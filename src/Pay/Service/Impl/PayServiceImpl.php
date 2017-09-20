<?php

namespace Codeages\Biz\Framework\Pay\Service\Impl;

use Codeages\Biz\Framework\Pay\Status\PayingStatus;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Framework\Pay\Service\PayService;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Targetlog\Service\TargetlogService;

class PayServiceImpl extends BaseService implements PayService
{
    public function createTrade($data)
    {
        $data = ArrayToolkit::parts($data, array(
            'goods_title',
            'goods_detail',
            'attach',
            'order_sn',
            'amount',
            'coin_amount',
            'notify_url',
            'return_url',
            'show_url',
            'create_ip',
            'platform_type',
            'platform',
            'open_id',
            'device_info',
            'seller_id',
            'user_id',
            'type',
            'rate'
        ));

        if ('recharge' == $data['type']) {
            return $this->createRechargeTrade($data);
        } else if ('purchase' == $data['type']) {
            return $this->createPurchaseTrade($data);
        } else {
            throw new InvalidArgumentException("can't create the type of {$data['type']} trade");
        }
    }

    protected function createPurchaseTrade($data)
    {
        $lock = $this->biz['lock'];

        try {
            $lock->get("trade_create_{$data['order_sn']}");
            $this->beginTransaction();

            $trade = $this->createPaymentTrade($data);

            if ($trade['cash_amount'] != 0) {
                $trade = $this->createPaymentPlatformTrade($data, $trade);
            } else {
                $mockNotify = array(
                    'status' => 'paid',
                    'paid_time' => time(),
                    'cash_flow' => '',
                    'cash_type' => '',
                    'trade_sn' => $trade['trade_sn'],
                    'pay_amount' => '0',
                );

                $trade = $this->updateTradeToPaid($mockNotify);
            }

            $this->commit();

            $lock->release("trade_create_{$data['order_sn']}");
        } catch (\Exception $e) {
            $this->rollback();
            $lock->release("trade_create_{$data['order_sn']}");
            throw $e;
        }

        return $trade;
    }

    protected function createRechargeTrade($data)
    {
        $lock = $this->biz['lock'];

        try {
            $lockName = 'trade_create_recharge_trade_'.$this->biz['user']['id'];

            $lock->get($lockName);
            $this->beginTransaction();
            $trade = $this->createPaymentTrade($data);

            $trade = $this->createPaymentPlatformTrade($data, $trade);

            $this->commit();
            $lock->release($lockName);
        } catch (\Exception $e) {
            $this->rollback();
            $lock->release($lockName);
            throw $e;
        }
        return $trade;
    }

    public function getTradeByTradeSn($tradeSn)
    {
        return $this->getPaymentTradeDao()->getByTradeSn($tradeSn);
    }

    public function queryTradeFromPlatform($tradeSn)
    {
        $trade = $this->getPaymentTradeDao()->getByTradeSn($tradeSn);
        return $this->getPayment($trade['platform'])->queryTrade($trade);
    }

    public function findTradesByOrderSns($orderSns)
    {
        return $this->getPaymentTradeDao()->findByOrderSns($orderSns);
    }

    public function closeTradesByOrderSn($orderSn, $excludeTradeSns = array())
    {
        $trades = $this->getPaymentTradeDao()->findByOrderSn($orderSn);
        if (empty($trades)) {
            return;
        }

        foreach ($trades as $trade) {
            if (in_array($trade['trade_sn'], $excludeTradeSns)) {
                continue;
            }

            $trade = $this->getTradeContext($trade['id'])->closing();

            if($this->isCloseByPayment()){
                $this->closeByPayment($trade);
            } else {
                $data = array(
                    'sn' => $trade['trade_sn'],
                );
                $this->notifyClosed($data);
            }
        }
    }

    public function notifyPaid($payment, $data)
    {
        list($data, $result) = $this->getPayment($payment)->converterNotify($data);
        $this->getTargetlogService()->log(TargetlogService::INFO, 'trade.paid_notify', $data['trade_sn'], "收到第三方支付平台{$payment}的通知，交易号{$data['trade_sn']}，支付状态{$data['status']}", $data);

        $trade = $this->updateTradeToPaid($data);
        return $result;
    }

    public function rechargeByIap($data)
    {
        list($data, $result) = $this->getPayment('iap')->converterNotify($data);
        $trade = array(
            'goods_title' => '虚拟币充值',
            'order_sn' => '',
            'platform' => 'iap',
            'platform_type' => '',
            'amount' => $data['pay_amount'],
            'user_id' => $data['attach']['user_id'],
            'type' => 'recharge'
        );
        $trade = $this->createPaymentTrade($trade);

        $data = array(
            'paid_time' => $data['paid_time'],
            'cash_flow' => $data['cash_flow'],
            'cash_type' => 'CNY',
            'trade_sn' => $trade['trade_sn'],
            'status' => 'paid',
        );
        $this->updateTradeToPaid($data);
        return $this->getPaymentTradeDao()->get($trade['id']);
    }

    protected function isCloseByPayment()
    {
        return empty($this->biz['payment.options']['closed_notify']) ? false : $this->biz['payment.options']['closed_notify'];
    }

    protected function closeByPayment($trade)
    {
        // todo
    }

    protected function updateTradeToPaid($data)
    {
        if ($data['status'] == 'paid') {
            $lock = $this->biz['lock'];
            try {
                $lock->get("pay_notify_{$data['trade_sn']}");

                $trade = $this->getPaymentTradeDao()->getByTradeSn($data['trade_sn']);
                if (empty($trade)) {
                    $this->getTargetlogService()->log(TargetlogService::INFO, 'trade.not_found', $data['trade_sn'], "交易号{$data['trade_sn']}不存在", $data);
                    $lock->release("pay_notify_{$data['trade_sn']}");
                    return;
                }

                if (PayingStatus::NAME != $trade['status']) {
                    $this->getTargetlogService()->log(TargetlogService::INFO, 'trade.is_not_paying', $data['trade_sn'], "交易号{$data['trade_sn']}状态不正确，状态为：{$trade['status']}", $data);
                    $lock->release("pay_notify_{$data['trade_sn']}");
                    return;
                }

                $trade = $this->createFlowsAndUpdateTradeStatus($trade, $data);

                $lock->release("pay_notify_{$data['trade_sn']}");
            } catch (\Exception $e) {
                $lock->release("pay_notify_{$data['trade_sn']}");
                $this->getTargetlogService()->log(TargetlogService::INFO, 'pay.error', $data['trade_sn'], "交易号{$data['trade_sn']}处理失败, {$e->getMessage()}", $data);
                throw $e;
            }

            $this->dispatch('payment_trade.paid', $trade, $data);
            return $trade;
        }
    }

    public function searchTrades($conditions, $orderBy, $start, $limit)
    {
        return $this->getPaymentTradeDao()->search($conditions, $orderBy, $start, $limit);
    }

    protected function createFlowsAndUpdateTradeStatus($trade, $data)
    {
        try {
            $this->beginTransaction();
            $trade = $this->getPaymentTradeDao()->update($trade['id'], array(
                'status' => $data['status'],
                'pay_time' => $data['paid_time'],
                'platform_sn' => $data['cash_flow'],
                'notify_data' => $data,
                'currency' => $data['cash_type'],
            ));
            $this->transfer($trade);
            $this->closeTradesByOrderSn($trade['order_sn'], array($trade['trade_sn']));
            $this->getTargetlogService()->log(TargetlogService::INFO, 'trade.paid', $data['trade_sn'], "交易号{$data['trade_sn']}，账目流水处理成功", $data);
            $this->commit();
            return $trade;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function findEnabledPayments()
    {
        return $this->biz['payment.platforms'];
    }

    public function notifyClosed($data)
    {
        $trade = $this->getPaymentTradeDao()->getByTradeSn($data['sn']);
        return $this->getTradeContext($trade['id'])->closed();
    }

    public function applyRefundByTradeSn($tradeSn)
    {
        $trade = $this->getPaymentTradeDao()->getByTradeSn($tradeSn);
        if (in_array($trade['status'], array('refunding', 'refunded'))) {
            return $trade;
        }

        if ($trade['status'] != 'paid') {
            throw new AccessDeniedException('can not refund, becourse the trade is not paid');
        }

        if ((time() - $trade['pay_time']) > 86400) {
            throw new AccessDeniedException('can not refund, becourse the paid trade is expired.');
        }

        if($this->isRefundByPayment()){
            return $this->refundByPayment($trade);
        }

        return $this->markRefunded($trade);
    }

    protected function isRefundByPayment()
    {
        return empty($this->biz['payment.options']['refunded_notify']) ? false : $this->biz['payment.options']['refunded_notify'];
    }

    protected function refundByPayment($trade)
    {
        $paymentGetWay = $this->getPayment($trade['platform']);
        $response = $paymentGetWay->applyRefund($trade);

        if (!$response->isSuccessful()) {
            return $trade;
        }

        $trade = $this->getPaymentTradeDao()->update($trade['id'], array(
            'status' => 'refunding',
            'apply_refund_time' => time()
        ));
        $this->dispatch('payment_trade.refunding', $trade);

        return $trade;
    }

    protected function markRefunded($trade)
    {
        $fields = array(
            'title' => $trade['title'],
            'from_user_id' => $trade['seller_id'],
            'to_user_id' => $trade['user_id'],
            'amount' => $trade['cash_amount'],
            'trade_sn' => $trade['trade_sn'],
            'order_sn' => $trade['order_sn'],
            'platform' => $trade['platform'],
            'parent_sn' => '',
            'currency' => $trade['currency'],
        );
        $flow = $this->getAccountService()->transferCash($fields);

        if (!empty($trade['coin_amount'])) {
            $fields = array(
                'title' => $trade['title'],
                'from_user_id' => $trade['seller_id'],
                'to_user_id' => $trade['user_id'],
                'amount' => $trade['coin_amount'],
                'trade_sn' => $trade['trade_sn'],
                'order_sn' => $trade['order_sn'],
                'platform' => $trade['platform'],
                'parent_sn' => $flow['sn']
            );
            $this->getAccountService()->transferCoin($fields);
        }

        return $this->getTradeContext($trade['id'])->refunded();
    }

    public function notifyRefunded($payment, $data)
    {
        $paymentGetWay = $this->getPayment($payment);
        $response = $paymentGetWay->converterRefundNotify($data);
        $tradeSn = $response[0]['notify_data']['trade_sn'];

        $trade = $this->getPaymentTradeDao()->getByTradeSn($tradeSn);

        return $this->markRefunded($trade);
    }

    protected function validateLogin()
    {
        if (empty($this->biz['user']['id'])) {
            throw new AccessDeniedException('user is not login.');
        }
    }

    protected function createPaymentTrade($data)
    {
        $rate = $this->getDefaultCoinRate();

        $trade = array(
            'title' => $data['goods_title'],
            'trade_sn' => $this->generateSn(),
            'order_sn' => $data['order_sn'],
            'platform' => $data['platform'],
            'platform_type' => $data['platform_type'],
            'price_type' => $this->getCurrencyType(),
            'amount' => $data['amount'],
            'rate' => $this->getDefaultCoinRate(),
            'seller_id' => empty($data['seller_id']) ? 0 : $data['seller_id'],
            'user_id' => $this->biz['user']['id'],
            'status' => 'paying',
        );

        if (!empty($data['type'])) {
            $trade['type'] = $data['type'];
        }

        if (empty($data['coin_amount'])) {
            $trade['coin_amount'] = 0;
        } else {
            $trade['coin_amount'] = $data['coin_amount'];
        }

        if ('money' == $trade['price_type']) {
            $trade['cash_amount'] = ceil(($trade['amount'] * $trade['rate'] - $trade['coin_amount']) / $trade['rate'] ); // 标价为人民币，可用虚拟币抵扣
        } else {
            $trade['cash_amount'] = ceil(($trade['amount'] - $trade['coin_amount']) / $rate); // 标价为虚拟币
        }

        $trade = $this->getPaymentTradeDao()->create($trade);
        if ('purchase' == $trade['type']) {
            $this->lockCoin($trade);
        }

        return $trade;
    }

    protected function lockCoin($trade)
    {
        if ($trade['coin_amount']>0) {
            $user = $this->biz['user'];
            $this->getAccountService()->lockCoin($user['id'], $trade['coin_amount']);
        }
    }

    protected function transfer($trade)
    {
        if (!empty($trade['cash_amount'])) {
            $fields = array(
                'from_user_id' => $trade['user_id'],
                'to_user_id' => $trade['seller_id'],
                'amount' => $trade['cash_amount'],
                'title' => $trade['title'],
                'trade_sn' => $trade['trade_sn'],
                'order_sn' => $trade['order_sn'],
                'platform' => $trade['platform'],
                'parent_sn' => '',
                'currency' => $trade['currency']
            );
            $flow = $this->getAccountService()->transferCash($fields);
        }

        if ('recharge' == $trade['type']) {
            if (!empty($trade['cash_amount'])) {
                $fields = array(
                    'from_user_id' => $trade['seller_id'],
                    'to_user_id' => $trade['user_id'],
                    'amount' => $trade['cash_amount'] * $this->getDefaultCoinRate(),
                    'title' => $trade['title'],
                    'trade_sn' => $trade['trade_sn'],
                    'order_sn' => $trade['order_sn'],
                    'platform' => $trade['platform'],
                    'parent_sn' => empty($flow['sn']) ? '' : $flow['sn'],
                );
                $this->getAccountService()->transferCoin($fields);
            }

        } elseif ('purchase' == $trade['type']) {
            if (!empty($trade['coin_amount'])) {
                $this->getAccountService()->decreaseLockedCoin($trade['user_id'], $trade['coin_amount']);

                $fields = array(
                    'from_user_id' => $trade['user_id'],
                    'to_user_id' => $trade['seller_id'],
                    'amount' => $trade['coin_amount'],
                    'title' => $trade['title'],
                    'trade_sn' => $trade['trade_sn'],
                    'order_sn' => $trade['order_sn'],
                    'platform' => $trade['platform'],
                    'parent_sn' => empty($flow['sn']) ? '' : $flow['sn'],
                );
                $this->getAccountService()->transferCoin($fields);
            }
        }
    }

    protected function generateSn($prefix = '')
    {
        return $prefix.date('YmdHis', time()).mt_rand(10000, 99999);
    }

    protected function getTargetlogService()
    {
        return $this->biz->service('Targetlog:TargetlogService');
    }

    protected function getPaymentTradeDao()
    {
        return $this->biz->dao('Pay:PaymentTradeDao');
    }

    protected function getAccountService()
    {
        return $this->biz->service('Pay:AccountService');
    }

    protected function getDefaultCoinRate()
    {
        $options = $this->biz['payment.options'];
        return empty($options['coin_rate']) ? 1: $options['coin_rate'];
    }

    protected function getCurrencyType()
    {
        return 'money';
    }

    protected function getPayment($payment)
    {
        return $this->biz['payment.'.$payment];
    }

    protected function createPaymentPlatformTrade($data, $trade)
    {
        $data['trade_sn'] = $trade['trade_sn'];
        unset($data['user_id']);
        unset($data['seller_id']);
        $data['amount'] = $trade['cash_amount'];
        $data['platform_type'] = $trade['platform_type'];
        $data['platform'] = $trade['platform'];

        $result = $this->getPayment($data['platform'])->createTrade($data);

        return $this->getPaymentTradeDao()->update($trade['id'], array(
            'platform_created_result' => $result,
            'platform_created_params' => $data
        ));
    }

    public function getCreateTradeResultByTradeSnFromPlatform($tradeSn)
    {
        $trade = $this->getPaymentTradeDao()->getByTradeSn($tradeSn);

        $result = $this->getPayment($trade['platform'])->createTrade($trade['platform_created_params']);

        $this->getPaymentTradeDao()->update($trade['id'], array(
            'platform_created_result' => $result
        ));

        return $result;
    }

    protected function getTradeContext($id)
    {
        $tradeContext = $this->biz['payment_trade_context'];

        $trade = $this->getPaymentTradeDao()->get($id);
        if (empty($trade)) {
            throw $this->createNotFoundException("trade #{$trade['id']} is not found");
        }

        $tradeContext->setPaymentTrade($trade);

        return $tradeContext;
    }
}