<?php

namespace Codeages\Biz\Framework\Pay\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface PayTradeDao extends GeneralDaoInterface
{
    public function getByOrderSnAndPlatform($orderSn, $platform);

    public function getByTradeSn($sn);

    public function findByTradeSns($sns);
    
    public function findByOrderSns($orderSns);

    public function findByOrderSn($orderSn);

    public function getByPlatformSn($platformSn);
}