<?php

namespace Codeages\Biz\Framework\Pay\Service;

interface AccountService
{
    public function setPayPassword($userId, $password);

    public function validatePayPassword($userId, $password);

    public function setSecurityAnswers($userId, $answers);

    public function validateSecurityAnswer($userId, $questionKey, $answer);

    public function isPayPasswordSetted($userId);

    public function isSecurityAnswersSetted($userId);

    public function createUserBalance($userId);

    public function getUserBalanceByUserId($userId);

    public function lockCoin($userId, $coinAmount);

    public function releaseCoin($userId, $coinAmount);

    public function decreaseLockedCoin($userId, $amount);

    public function transferCoin($fields);

    public function transferCash($fields);

    public function countUserCashflows($conditions);

    public function searchUserCashflows($conditions, $orderBy, $start, $limit);

    public function sumColumnByConditions($column, $conditions);

    public function searchUserIdsGroupByUserIdOrderBySumColumn($column, $conditions, $sort, $start, $limit);

    public function searchUserIdsGroupByUserIdOrderByBalance($conditions, $sort, $start, $limit);

    public function countUsersByConditions($conditions);

}
