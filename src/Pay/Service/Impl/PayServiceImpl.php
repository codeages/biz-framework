<?php

namespace Codeages\Biz\Framework\Pay\Service\Impl;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Framework\Pay\Service\PayService;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Targetlog\Service\TargetlogService;

class PayServiceImpl extends BaseService implements PayService
{
    /**
     * 创建交易信息有两种情况
     * 1、标价为虚拟币
     * 2、标价为货币
     *
     * @param $data
     * @return mixed
     */
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
            'create_ip',
            'pay_type',
            'platform',
            'open_id',
            'device_info',
            'seller_id',
            'user_id'
        ));

        $lock = $this->biz['lock'];

        try {
            $lock->get("trade_create_{$data['order_sn']}");
            $this->beginTransaction();
            $trade = $this->getPaymentTradeDao()->getByOrderSnAndPlatform($data['order_sn'], $data['platform']);
            if(empty($trade)) {
                $trade = $this->createPaymentTrade($data);
            }

            $result = $this->createPaymentPlatformTrade($data, $trade);

            $trade = $this->getPaymentTradeDao()->update($trade['id'], array(
                'platform_created_result' => $result
            ));
            $this->commit();
            $lock->release("trade_create_{$data['order_sn']}");
        } catch (\Exception $e) {
            $this->rollback();
            $lock->release("trade_create_{$data['order_sn']}");
            throw $e;
        }
        return $trade;
    }

    /**
     * TODO:
     * 1、虚拟币余额处理
     * 2、异常订单处理
     * 3、日志
     */
    public function notify($payment, $data)
    {
        list($data, $result) = $this->getPayment($payment)->converterNotify($data);
        $this->getTargetlogService()->log(TargetlogService::INFO, 'pay.notify_received', $data['trade_sn'], "收到第三方支付平台{$payment}的通知，交易号{$data['trade_sn']}，支付状态{$data['status']}", $data);

        if ($data['status'] == 'paid') {
            $lock = $this->biz['lock'];
            try {
                $lock->get("pay_notify_{$data['trade_sn']}");

                $trade = $this->getPaymentTradeDao()->getByTradeSn($data['trade_sn']);
                if (empty($trade)) {
                    $this->getTargetlogService()->log(TargetlogService::INFO, 'pay.trade_empty', $data['trade_sn'], "交易号{$data['trade_sn']}不存在", $data);
                    $lock->release("pay_notify_{$data['trade_sn']}");
                    return $result;
                }

                $cashFlows = $this->findUserCashflowsByTradeSn($trade['trade_sn']);
                if (!empty($cashFlows)) {
                    $this->getTargetlogService()->log(TargetlogService::INFO, 'pay.notify_exist', $data['trade_sn'], "交易号{$data['trade_sn']}，已存在流水，不处理此通知", $data);
                    $lock->release("pay_notify_{$data['trade_sn']}");
                    return $result;
                }

                $this->beginTransaction();
                $trade = $this->getPaymentTradeDao()->update($trade['id'], array(
                    'status' => $data['status'],
                    'pay_time' => $data['paid_time'],
                    'platform_sn' => $data['cash_flow'],
                    'notify_data' => $data,
                    'currency' => $data['cash_type'],
                ));
                $this->createCashFlow($trade, $data);
                $this->getTargetlogService()->log(TargetlogService::INFO, 'pay.success', $data['trade_sn'], "交易号{$data['trade_sn']}，账目流水处理成功", $data);
                $this->commit();

                $lock->release("pay_notify_{$data['trade_sn']}");

            } catch (\Exception $e) {
                $this->rollback();
                $lock->release("pay_notify_{$data['trade_sn']}");
                $this->getTargetlogService()->log(TargetlogService::INFO, 'pay.error', $data['trade_sn'], "交易号{$data['trade_sn']}处理失败, {$e->getMessage()}", $data);
                throw $e;
            }

            $this->dispatch('pay.success', $trade, $data);
        }
        return $result;
    }

    public function findEnabledPayments()
    {
        $payments = $this->biz['payment.platforms'];

        $enabledPayments = array();
        foreach ($payments as $key => $payment) {
            $setting = $this->getPaymentSetting($key);
            if (!empty($setting['enable']) && $setting['enable']) {
                $enabledPayments[$key] = $payment;
            }
        }
        return $enabledPayments;
    }

    protected function createPaymentTrade($data)
    {
        $rate = $this->getCoinRate();

        $trade = array(
            'title' => $data['goods_title'],
            'trade_sn' => $this->generateSn(),
            'order_sn' => $data['order_sn'],
            'platform' => $data['platform'],
            'price_type' => $this->getCurrencyType(),
            'amount' => $data['amount'],
            'rate' => $this->getCoinRate(),
            'seller_id' => empty($data['seller_id']) ? 0 : $data['seller_id'],
            'user_id' => $this->biz['user']['id'],
        );

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

        return $this->getPaymentTradeDao()->create($trade);
    }

    protected function findUserCashflowsByTradeSn($sn)
    {
        return $this->getUserCashflowDao()->findByTradeSn($sn);
    }

    protected function createCashFlow($trade, $notifyData)
    {
        $inflow = $this->createCashInflow($trade, $notifyData);
        $outflow = $this->createCashOutflow($trade, $inflow);
        $this->createSiteCashInflow($trade, $outflow);

        if ('recharge' == $trade['type']) {
            $outflow = $this->createSiteCoinOutflow($trade, $outflow);
            $this->createUserCoinInflow($trade, $outflow);
        } elseif ('purchase' == $trade['type']) {
            if (!empty($trade['coin_amount'])) {
                $outflow = $this->createUserCoinOutfow($trade, $outflow);
                $this->createSiteCoinInflow($trade, $outflow);
            }
        } elseif ('refund' == $trade['type']) {
            $this->createRefundCashflow($trade);
        }
    }

    protected function createRefundCashflow($trade)
    {
        // TODO:
    }

    protected function createSiteflow($trade, $parentFlow = array())
    {
        
    }

    protected function createSiteCashInflow($trade, $flow)
    {
        $siteIncome = array(
            'sn' => $this->generateSn(),
            'title' => $trade['title'],
            'trade_sn' => $trade['trade_sn'],
            'order_sn' => $trade['order_sn'],
            'platform_sn' => $trade['platform_sn'],
            'platform' => $trade['platform'],
            'price_type' => $trade['price_type'],
            'currency' => $trade['currency'],
            'amount' => $trade['cash_amount'],
            'pay_time' => $trade['pay_time'],
            'user_cashflow' => $flow['sn'],
        );
        return $this->getSiteCashFlowDao()->create($siteIncome);
    }

    protected function createSiteCoinOutflow($trade, $flow)
    {
        $coinOutflow = array(
            'sn' => $this->generateSn(),
            'title' => $trade['title'],
            'trade_sn' => $trade['trade_sn'],
            'order_sn' => $trade['order_sn'],
            'platform_sn' => $trade['platform_sn'],
            'platform' => $trade['platform'],
            'price_type' => $trade['price_type'],
            'currency' => 'coin',
            'amount' => $flow['amount'] * $this->getCoinRate(),
            'pay_time' => $trade['pay_time'],
            'user_cashflow' => $flow['sn'],
        );
        return $this->getSiteCashFlowDao()->create($coinOutflow);
    }

    protected function createSiteCoinInflow($trade, $flow)
    {
        $coinOutflow = array(
            'sn' => $this->generateSn(),
            'title' => $trade['title'],
            'trade_sn' => $trade['trade_sn'],
            'order_sn' => $trade['order_sn'],
            'platform_sn' => $trade['platform_sn'],
            'platform' => $trade['platform'],
            'price_type' => $trade['price_type'],
            'currency' => 'coin',
            'amount' => $flow['amount'],
            'pay_time' => $trade['pay_time'],
            'user_cashflow' => $flow['sn'],
        );
        return $this->getSiteCashFlowDao()->create($coinOutflow);
    }

    protected function getSiteCashFlowDao()
    {
        return $this->biz->dao('Pay:SiteIncomeDao');
    }

    protected function generateSn($prefix = '')
    {
        return $prefix.date('YmdHis', time()).mt_rand(10000, 99999);
    }

    protected function getUserCashflowDao()
    {
        return $this->biz->dao('Pay:UserCashflowDao');
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

    protected function getCoinRate()
    {
        return 1;
    }

    protected function getCurrencyType()
    {
        return 'money';
    }

    protected function getPaymentSetting($paymentType)
    {
        return array('enable' => 1);
    }

    protected function getPayment($payment)
    {
        return $this->biz["payment.{$payment}"];
    }

    protected function createCashInflow($trade, $notifyData)
    {
        $inflow = array(
            'sn' => $this->generateSn(),
            'type' => 'inflow',
            'amount' => $notifyData['pay_amount'],
            'currency' => $trade['currency'],
            'user_id' => $notifyData['attach']['user_id'],
            'trade_sn' => $notifyData['trade_sn'],
            'order_sn' => $trade['order_sn'],
            'platform' => $trade['platform']
        );

        $inflow = $this->getUserCashflowDao()->create($inflow);
        $this->getAccountService()->waveCashAmount($notifyData['attach']['user_id'], $inflow['amount']);
        return $inflow;
    }

    protected function createCashOutflow($trade, $inflow)
    {
        $outflow = array(
            'sn' => $this->generateSn(),
            'type' => 'outflow',
            'parent_sn' => $inflow['sn'],
            'amount' => $inflow['amount'],
            'currency' => $trade['currency'],
            'user_id' => $inflow['user_id'],
            'trade_sn' => $inflow['trade_sn'],
            'order_sn' => $trade['order_sn'],
            'platform' => $trade['platform']
        );

        $outflow = $this->getUserCashflowDao()->create($outflow);
        $this->getAccountService()->waveCashAmount($inflow['user_id'], $outflow['amount']);
        return $outflow;
    }

    protected function createUserCoinInflow($trade, $flow)
    {
        $inflow = array(
            'sn' => $this->generateSn(),
            'type' => 'inflow',
            'parent_sn' => $flow['sn'],
            'amount' => $flow['amount']* $this->getCoinRate(),
            'currency' => 'coin',
            'user_id' => $flow['user_id'],
            'trade_sn' => $flow['trade_sn'],
            'order_sn' => $trade['order_sn'],
            'platform' => $trade['platform']
        );

        $inflow = $this->getUserCashflowDao()->create($inflow);
        $this->getAccountService()->waveAmount($flow['user_id'], $inflow['amount']);
    }

    protected function createUserCoinOutfow($trade, $flow)
    {
        $outflow = array(
            'sn' => $this->generateSn(),
            'type' => 'outflow',
            'parent_sn' => $flow['sn'],
            'amount' => $trade['coin_amount'],
            'currency' => 'coin',
            'user_id' => $flow['user_id'],
            'trade_sn' => $flow['trade_sn'],
            'order_sn' => $trade['order_sn'],
            'platform' => $trade['platform']
        );

        $outflow = $this->getUserCashflowDao()->create($outflow);
        $this->getAccountService()->waveAmount($flow['user_id'], $outflow['amount']);
        return $outflow;
    }

    protected function createPaymentPlatformTrade($data, $trade)
    {
        $data['trade_sn'] = $trade['trade_sn'];
        unset($data['user_id']);
        unset($data['seller_id']);
        return $this->getPayment($data['platform'])->createTrade($data);
    }
}