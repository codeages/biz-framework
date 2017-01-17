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

        if ($this->isInCondition($where)) {
            return $this->addWhereIn($where);
        }

        $likeMatchs = $this->getLikeCondtion($where);
        if ($likeMatchs && in_array($likeMatchs[1], $likeMatchs)) {
            return $this->andWhereLike($where, $likeMatchs[1]);
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

    private function andWhereLike($where, $like)
    {
        $conditionName = $this->getConditionName($where);
        if (empty($this->conditions[$conditionName]) || !is_string($this->conditions[$conditionName])) {
            return $this;
        }

        //PRE_LIKE
        if ($like == 'PRE_LIKE') {
            $where = preg_replace('/PRE_LIKE/', "LIKE", $where);
            $this->conditions[$conditionName] = "%{$this->conditions[$conditionName]}";
        } else if ($like == 'SUF_LIKE') {
            $where = preg_replace('/SUF_LIKE/', "LIKE", $where);
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

    private function getLikeCondtion($where)
    {
        preg_match('/\s+((PRE_|SUF_)?LIKE)\s+/', $where, $matches);

        return $matches;
    }

    private function isInCondition($where)
    {
        $matched = preg_match('/\s+(IN)\s+/', $where, $matches);
        if (empty($matched)) {
            return false;
        } else {
            return true;
        }
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
