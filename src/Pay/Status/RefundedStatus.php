<?php

namespace Codeages\Biz\Framework\Pay\Status;

class RefundedStatus extends AbstractStatus
{
    const NAME = 'refunded';

    public function getName()
    {
        return self::NAME;
    }

    public function process($data = array())
    {
        $trade = $this->getPayTradeDao()->update($this->PayTrade['id'], array(
            'status' => RefundedStatus::NAME,
            'refund_success_time' => time()
        ));

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
            'buyer_id' => $trade['user_id'],
            'action' => 'refund'
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
                'parent_sn' => $flow['sn'],
                'buyer_id' => $trade['user_id'],
                'action' => 'refund'
            );
            $this->getAccountService()->transferCoin($fields);
        }

        return $trade;
    }

    protected function getAccountService()
    {
        return $this->biz->service('Pay:AccountService');
    }
}