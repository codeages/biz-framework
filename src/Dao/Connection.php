<?php

namespace Codeages\Biz\Framework\Dao;

use Doctrine\DBAL\Connection as DoctrineConnection;

class Connection extends DoctrineConnection
{
    public function update($tableExpression, array $data, array $identifier, array $types = array())
    {
        $this->checkFieldNames(array_keys($data));

        return parent::update($tableExpression, $data, $identifier, $types);
    }

    public function insert($tableExpression, array $data, array $types = array())
    {
        $this->checkFieldNames(array_keys($data));

        return parent::insert($tableExpression, $data, $types);
    }

    public function checkFieldNames($names)
    {
        foreach ($names as $name) {
            if (!ctype_alnum(str_replace('_', '', $name))) {
                throw new \InvalidArgumentException('Field name is invalid.');
            }
        }

        return true;
    }

    public function fetchLockAssoc($statement, array $params = [], array $types = [])
    {
        return parent::fetchAssoc($statement, $params, $types);
    }

    public function getLock($statement, array $params = [], array $types = [])
    {
        $result = parent::fetchAssoc($statement, $params, $types);

        return $result['getLock'];
    }

    public function releaseLock($statement, array $params = array(), array $types = array())
    {
        $result = parent::fetchAssoc($statement, $params, $types);
        return $result['releaseLock'];
    }

    public function transactional(\Closure $func, \Closure $exceptionFunc = null)
    {
        $this->beginTransaction();
        try {
            $result = $func($this);
            $this->commit();

            return $result;
        } catch (\Exception $e) {
            $this->rollBack();
            !is_null($exceptionFunc) && $exceptionFunc($this);
            throw $e;
        }
    }
}
