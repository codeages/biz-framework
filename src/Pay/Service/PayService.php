<?php

namespace Codeages\Biz\Framework\Pay\Service;

interface PayService
{
    public function findEnabledPayments();

    public function createTrade($data, $createPlatformTrade = true);

    public function closeTradesByOrderSn($orderSn, $excludeTradeSns = array());

    public function findTradesByOrderSns($orderSns);

    public function applyRefundByTradeSn($tradeSn);

    public function notifyPaid($payment, $data);

    public function notifyRefunded($payment, $data);

    public function notifyClosed($data);

    public function queryTradeFromPlatform($tradeSn);

    public function getTradeByTradeSn($tradeSn);

    public function findTradesByTradeSn($tradeSns);

    public function searchTrades($conditions, $orderBy, $start, $limit);

    public function getCreateTradeResultByTradeSnFromPlatform($tradeSn);

    public function rechargeByIap($data);

    public function getTradeByPlatformSn($platformSn);
}