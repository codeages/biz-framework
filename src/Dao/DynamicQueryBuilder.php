<?php

namespace Codeages\Biz\Framework\Dao;

use Doctrine\DBAL\Query\QueryBuilder;

class DynamicQueryBuilder extends QueryBuilder
{
    protected $conditions;

    public function __construct(Connection $connection, $conditions)
    {
        parent::__construct($connection);
        $this->conditions = $conditions;
    }

    public function where($where)
    {
        if (!$this->isWhereInConditions($where)) {
            return $this;
        }

        return parent::where($where);
    }

    public function andWhere($where)
    {
        if (!$this->isWhereInConditions($where)) {
            return $this;
        }

        if ($this->matchInCondition($where)) {
            return $this->addWhereIn($where);
        }

        if ($likeType = $this->matchLikeCondition($where)) {
            return $this->andWhereLike($where, $likeType);
        }

        return parent::andWhere($where);
    }

    public function andStaticWhere($where)
    {
        return parent::andWhere($where);
    }

    private function addWhereIn($where)
    {
        $conditionName = $this->getConditionName($where);
        if (empty($this->conditions[$conditionName]) or !is_array($this->conditions[$conditionName])) {
            return $this;
        }

        $marks = array();
        foreach (array_values($this->conditions[$conditionName]) as $index => $value) {
            $marks[] = ":{$conditionName}_{$index}";
            $this->conditions["{$conditionName}_{$index}"] = $value;
        }

        $where = str_replace(":{$conditionName}", implode(',', $marks), $where);

        return parent::andWhere($where);
    }

    private function andWhereLike($where, $likeType)
    {
        $conditionName = $this->getConditionName($where);
        if (empty($this->conditions[$conditionName]) || !is_string($this->conditions[$conditionName])) {
            return $this;
        }

        //PRE_LIKE
        if ($likeType == 'PRE_LIKE') {
            $where = str_replace('PRE_LIKE', "LIKE", $where);
            $this->conditions[$conditionName] = "%{$this->conditions[$conditionName]}";
        } else if ($likeType == 'SUF_LIKE') {
            $where = str_replace('SUF_LIKE', "LIKE", $where);
            $this->conditions[$conditionName] = "{$this->conditions[$conditionName]}%";
        } else {
            $this->conditions[$conditionName] = "%{$this->conditions[$conditionName]}%";
        }

        return parent::andWhere($where);
    }

    public function execute()
    {
        foreach ($this->conditions as $field => $value) {
            $this->setParameter(":{$field}", $value);
        }

        return parent::execute();
    }

    private function matchLikeCondition($where)
    {
        preg_match('/\s+((PRE_|SUF_)?LIKE)\s+/', $where, $matches);

        return $matches ? $matches[1] : false;
    }

    private function matchInCondition($where)
    {
        return preg_match('/\s+(IN)\s+/', $where);
    }

    private function getConditionName($where)
    {
        $matched = preg_match('/:([a-zA-z0-9_]+)/', $where, $matches);
        if (empty($matched)) {
            return false;
        }

        return $matches[1];
    }

    private function isWhereInConditions($where)
    {
        $conditionName = $this->getConditionName($where);
        if (!$conditionName) {
            return false;
        }

        return array_key_exists($conditionName, $this->conditions) && !is_null($this->conditions[$conditionName]);
    }
}
