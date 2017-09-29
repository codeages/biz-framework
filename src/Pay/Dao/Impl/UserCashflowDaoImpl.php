<?php

namespace Codeages\Biz\Framework\Pay\Dao\Impl;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Framework\Dao\DaoException;
use Codeages\Biz\Framework\Pay\Dao\UserCashflowDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UserCashflowDaoImpl extends GeneralDaoImpl implements UserCashflowDao
{
    protected $table = 'biz_user_cashflow';

    public function findByTradeSn($sn)
    {
        return $this->findByFields(array('trade_sn' => $sn));
    }

    /**
     * @param $column
     * @param $conditions
     * @return bool|string
     * @throws DaoException
     */
    public function sumColumnByConditions($column, $conditions)
    {
        if (!$this->isSumColumnAllow($column)) {
            throw new DaoException('column is not allowed');
        }
        $builder = $this->createQueryBuilder($conditions)
            ->select("sum({$column})");
        return $builder->execute()->fetchColumn(0);
    }

    /**
     * @param $column
     * @param $conditions
     * @param $sort
     * @param $start
     * @param $limit
     * @return array
     * @throws DaoException
     * 使用时需要标明type&amount_type
     */
    public function searchUserIdsGroupByUserIdOrderBySumColumn($column, $conditions, $sort, $start, $limit)
    {
        if (!$this->isSumColumnAllow($column)) {
            throw new DaoException('column is not allowed');
        }

        $builder = $this->createQueryBuilder($conditions)
            ->select("user_id")
            ->groupBy('user_id')
            ->addOrderBy("sum({$column})", $sort)
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return ArrayToolkit::column($builder->execute()->fetchAll() ? : array(), 'user_id');

    }

    /**
     * @param $conditions
     * @param $sort
     * @param $start
     * @param $limit
     * @return array
     * @throws DaoException
     * 使用时不区分type,区分amount_type
     */
    public function searchUserIdsGroupByUserIdOrderByBalance($conditions, $sort, $start, $limit)
    {
        if (!isset($conditions['amount_type']) || !in_array($conditions['amount_type'], array('coin', 'money'))) {
            throw new DaoException('amount_type value is not allowed');
        }

        $orderByType = $conditions['amount_type'] == 'coin' ? 'amount' : 'cash_amount';

        $builder = $this->createQueryBuilder($conditions)
            ->select("{$this->table}.user_id")
            ->leftJoin($this->table, 'biz_user_balance', 'b', "{$this->table}.user_id=b.user_id")
            ->groupBy("{$this->table}.user_id")
            ->addOrderBy("b.{$orderByType}", $sort)
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return ArrayToolkit::column($builder->execute()->fetchAll() ? : array(), 'user_id');

    }

    public function countUsersByConditions($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select("count(distinct user_id)");
        return $builder->execute()->fetchColumn(0);
    }

    private function sumColumnWhiteList()
    {
        return array('amount');
    }

    protected function isSumColumnAllow($column)
    {
        $whiteList = $this->sumColumnWhiteList();

        if (in_array($column, $whiteList)) {
            return true;
        }
        return false;
    }

    protected function createQueryBuilder($conditions)
    {
        $conditions = array_filter(
            $conditions,
            function ($value) {
                if ($value === '' || $value === null) {
                    return false;
                }

                if (is_array($value) && empty($value)) {
                    return false;
                }

                return true;
            }
        );

        $builder = $this->getQueryBuilder($conditions);
        $builder->from($this->table(), $this->table());

        $declares = $this->declares();
        $declares['conditions'] = isset($declares['conditions']) ? $declares['conditions'] : array();

        foreach ($declares['conditions'] as $condition) {
            $builder->andWhere($this->table.'.'.$condition);
        }

        return $builder;
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time'),
            'orderbys' => array(
                'id',
                'created_time',
            ),
            'conditions' => array(
                'id = :id',
                'sn = :sn',
                'user_id != :except_user_id',
                'user_id = :user_id',
                'buyer_id = :buyer_id',
                'type = :type',
                'amount > :amount_GT',
                'amount >= :amount_GTE',
                'amount < :amount_LT',
                'amount <= :amount_LTE',
                'currency = :currency',
                'order_sn = :order_sn',
                'trade_sn = :trade_sn',
                'platform = :platform',
                'amount_type = :amount_type',
                'created_time > :created_time_GT',
                'created_time >= :created_time_GTE',
                'created_time < :created_time_LT',
                'created_time <= :created_time_LTE',
            ),
        );
    }
}