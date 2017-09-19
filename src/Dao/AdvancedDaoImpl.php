<?php

namespace Codeages\Biz\Framework\Dao;

abstract class AdvancedDaoImpl extends GeneralDaoImpl implements AdvancedDaoInterface
{
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

        $builder = $this->createQueryBuilder($conditions)
            ->delete($this->table);

        return $builder->execute();
    }

    public function batchCreate($rows)
    {
        if (empty($rows)) {
            return array();
        }

        $columns = array_keys(reset($rows));
        $this->db()->checkFieldNames($columns);
        $columnStr = implode(',', $columns);

        $count = count($rows);
        $pageSize = 1000;
        $pageCount = ceil($count / $pageSize);

        for ($i = 1; $i <= $pageCount; ++$i) {
            $start = ($i - 1) * $pageSize;
            $pageRows = array_slice($rows, $start, $pageSize);

            $params = array();
            $sql = "INSERT INTO {$this->table} ({$columnStr}) values ";
            foreach ($pageRows as $key => $row) {
                $marks = str_repeat('?,', count($row) - 1).'?';

                if ($key != 0) {
                    $sql .= ',';
                }
                $sql .= "({$marks})";

                $params = array_merge($params, array_values($row));
            }

            $this->db()->executeUpdate($sql, $params);
            unset($params);
        }

        return true;
    }

    public function batchUpdate($identifies, $updateColumnsList, $identifyColumn = 'id')
    {
        $updateColumns = array_keys(reset($updateColumnsList));

        $this->db()->checkFieldNames($updateColumns);
        $this->db()->checkFieldNames(array($identifyColumn));

        array_walk($identifies, 'intval');

        $count = count($identifies);
        $pageSize = 500;
        $pageCount = ceil($count / $pageSize);

        for ($i = 1; $i <= $pageCount; ++$i) {
            $start = ($i - 1) * $pageSize;
            $partIdentifies = array_slice($identifies, $start, $pageSize);
            $partUpdateColumnsList = array_slice($updateColumnsList, $start, $pageSize);
            $this->partUpdate($partIdentifies, $partUpdateColumnsList, $identifyColumn, $updateColumns);
        }
    }

    /**
     * @param $identifies
     * @param $updateColumnsList
     * @param $identifyColumn
     * @param $updateColumns
     *
     * @return int
     */
    private function partUpdate($identifies, $updateColumnsList, $identifyColumn, $updateColumns)
    {
        $sql = "UPDATE {$this->table} SET ";

        $updateSql = array();

        $params = array();
        foreach ($updateColumns as $updateColumn) {
            $caseWhenSql = "{$updateColumn} = CASE {$identifyColumn} ";

            foreach ($identifies as $identifyIndex => $identify) {
                $params[] = $updateColumnsList[$identifyIndex][$updateColumn];
                $caseWhenSql .= " WHEN {$identify} THEN ? ";
                if ($identifyIndex === count($identifies) - 1) {
                    $caseWhenSql .= " ELSE {$updateColumn} END";
                }
            }

            $updateSql[] = $caseWhenSql;
        }

        $sql .= implode(',', $updateSql);

        $identifiesStr = implode(',', $identifies);
        $sql .= " WHERE {$identifyColumn} IN ({$identifiesStr})";

        return $this->db()->executeUpdate($sql, $params);
    }
}
