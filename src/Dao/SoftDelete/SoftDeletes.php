<?php

namespace Codeages\Biz\Framework\Dao\SoftDelete;

use Codeages\Biz\Framework\Dao\DaoException;

trait SoftDeletes
{
    /**
     * @var bool 是否启动软删除
     */
    private $isEnableSoftDelete = true;

    private $originalEnableSoftDeleteStatus = null;

    protected $softDeleteField = 'is_deleted';

    protected $softDeleteFieldType = SoftDeleteFieldConstant::FIELD_TYPE_INT;

    protected $softDeleteFieldFormat = null;

    protected $softDeletedAtField = 'deleted_time';

    protected $softDeletedAtFieldType = SoftDeleteFieldConstant::FIELD_TYPE_TIMESTAMP_MS;

    protected $softDeletedAtFieldFormat = null;

    /**
     * @return string
     */
    public function getSoftDeletedAtField()
    {
        return $this->softDeletedAtField;
    }

    /**
     * @param string $softDeletedAtField
     */
    protected function setSoftDeletedAtField($softDeletedAtField)
    {
        $this->softDeletedAtField = (string)$softDeletedAtField;
    }

    /**
     * @return string
     */
    public function getSoftDeletedAtFieldFormat()
    {
        return $this->softDeletedAtFieldFormat;
    }

    /**
     * @param string $softDeletedAtFieldFormat
     */
    protected function setSoftDeletedAtFieldFormat($softDeletedAtFieldFormat)
    {
        $this->softDeletedAtFieldFormat = (string)$softDeletedAtFieldFormat;
    }

    /**
     * @return string
     */
    public function getSoftDeletedAtFieldType()
    {
        return $this->softDeletedAtFieldType;
    }

    /**
     * @param string $softDeletedAtFieldType
     */
    public function setSoftDeletedAtFieldType($softDeletedAtFieldType)
    {
        $this->softDeletedAtFieldType = (string)$softDeletedAtFieldType;
    }

    private function startSetSoftDeleteStatus()
    {
        if (is_null($this->originalEnableSoftDeleteStatus)) {
            $this->originalEnableSoftDeleteStatus = $this->isEnableSoftDelete;
        }
    }

    private function recoverSoftDeleteStatus()
    {
        if (!is_null($this->originalEnableSoftDeleteStatus)) {
            $this->isEnableSoftDelete = $this->originalEnableSoftDeleteStatus;
        }
    }

    /**
     * @return null | string
     */
    public function getSoftDeleteFieldFormat()
    {
        return $this->softDeleteFieldFormat;
    }

    /**
     * @param null | string $softDeleteFieldFormat
     */
    protected function setSoftDeleteFieldFormat($softDeleteFieldFormat)
    {
        $this->softDeleteFieldFormat = (string)$softDeleteFieldFormat;
    }

    /**
     * @return string
     */
    public function getSoftDeleteFieldType()
    {
        return $this->softDeleteFieldType;
    }

    /**
     * @param string $softDeleteFieldType
     */
    protected function setSoftDeleteFieldType($softDeleteFieldType)
    {
        $this->softDeleteFieldType = (string)$softDeleteFieldType;
    }

    /**
     * @param bool $softDeleteStatus
     */
    private function setSoftDeleteStatus($softDeleteStatus)
    {
        $this->isEnableSoftDelete = (bool)$softDeleteStatus;
    }

    public function issetSoftDeleteField()
    {
        return !empty($this->softDeleteField);
    }

    /**
     * @return string
     */
    public function getSoftDeleteField()
    {
        return $this->softDeleteField;
    }

    protected function setSoftDeleteField($field)
    {
        $this->softDeleteField = (string)$field;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnableSoftDelete()
    {
        return $this->isEnableSoftDelete && $this->issetSoftDeleteField();
    }

    protected function createQueryBuilder($conditions)
    {
        $builder = parent::createQueryBuilder($conditions);
        if ($this->isEnableSoftDelete()) {
            $builder->andStaticWhere(SoftDeleteFieldConstant::getFieldIsFalseSql(
                $this->getSoftDeleteField(),
                $this->getSoftDeleteFieldType()
            ));

        }
        return $builder;
    }

    public function delete($id)
    {
        if ($this->isEnableSoftDelete()) {
            // 软删除
            return $this->updateById($id, $this->getSoftDeletedFields());
        } else {
            return parent::delete($id);
        }
    }

    public function batchDelete(array $conditions)
    {
        $declares = $this->declares();
        $declareConditions = isset($declares['conditions']) ? $declares['conditions'] : array();
        array_walk($conditions, function (&$condition, $key) use ($declareConditions) {
            $isInDeclareCondition = false;
            foreach ($declareConditions as $declareCondition) {
                if (preg_match('/:'.$key.'/', $declareCondition)) {
                    $isInDeclareCondition = true;
                }
            }

            if (!$isInDeclareCondition) {
                $condition = null;
            }
        });

        $conditions = array_filter($conditions);

        if (empty($conditions) || empty($declareConditions)) {
            throw new DaoException('Please make sure at least one restricted condition');
        }

        if ($this->isEnableSoftDelete()) {
            return $this->updateByConditions($conditions, $this->getSoftDeletedFields());
        } else {
            $builder = $this->createQueryBuilder($conditions)
                ->delete($this->table);

            return $builder->execute();
        }
    }

    protected function getSoftDeleteSqlFragment()
    {
        $softDeleteSql = "";
        if ($this->isEnableSoftDelete()) {
            $softDeleteSql = " AND " . SoftDeleteFieldConstant::getFieldIsFalseSql(
                    $this->getSoftDeleteField(),
                    $this->getSoftDeleteFieldType()
                );
        }

        return $softDeleteSql;
    }

    public function get($id, array $options = array())
    {
        $lock = isset($options['lock']) && true === $options['lock'];
        $sql = "SELECT * FROM {$this->table()} WHERE id = ? {$this->getSoftDeleteSqlFragment()} ".($lock ? ' FOR UPDATE' : '');

        return $this->db()->fetchAssoc($sql, array($id)) ?: null;
    }


    protected function findInField($field, $values)
    {
        if (empty($values)) {
            return array();
        }

        $marks = str_repeat('?,', count($values) - 1).'?';

        $sql = "SELECT * FROM {$this->table} WHERE {$field} IN ({$marks}) {$this->getSoftDeleteSqlFragment()}; ";

        return $this->db()->fetchAll($sql, array_values($values));
    }

    protected function getByFields($fields)
    {
        $placeholders = array_map(
            function ($name) {
                return "{$name} = ?";
            },
            array_keys($fields)
        );

        $sql = "SELECT * FROM {$this->table()} WHERE ".implode(' AND ', $placeholders) . $this->getSoftDeleteSqlFragment() .' LIMIT 1 ';

        return $this->db()->fetchAssoc($sql, array_values($fields)) ?: null;
    }

    protected function findByFields($fields)
    {
        $placeholders = array_map(
            function ($name) {
                return "{$name} = ?";
            },
            array_keys($fields)
        );

        $sql = "SELECT * FROM {$this->table()} WHERE ".implode(' AND ', $placeholders) . $this->getSoftDeleteSqlFragment();

        return $this->db()->fetchAll($sql, array_values($fields));
    }

    protected function getSoftDeletedFields()
    {
        return [
            $this->getSoftDeleteField() => SoftDeleteFieldConstant::getFieldTrueValue(
                $this->getSoftDeleteFieldType(),
                $this->getSoftDeleteFieldFormat()
            ),
            $this->getSoftDeletedAtField() => SoftDeleteFieldConstant::getFieldTrueValue(
                $this->getSoftDeletedAtFieldType(),
                $this->getSoftDeletedAtFieldFormat()
            )
        ];
    }
}