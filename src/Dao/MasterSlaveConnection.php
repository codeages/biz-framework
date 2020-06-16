<?php

namespace Codeages\Biz\Framework\Dao;

use Doctrine\DBAL\Connections\MasterSlaveConnection as DoctrineMasterSlaveConnection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Driver\PingableConnection;
use Throwable;

class MasterSlaveConnection extends DoctrineMasterSlaveConnection
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

    /**
     * {@inheritDoc}
     */
    public function query()
    {
        $this->connect('master');
        assert($this->_conn instanceof DriverConnection);

        $args = func_get_args();

        $logger = $this->getConfiguration()->getSQLLogger();
        if ($logger) {
            $logger->startQuery($args[0]);
        }

        try {
            $statement = call_user_func_array(array($this->_conn, 'query'), $args);
        } catch (Throwable $ex) {
            throw DBALException::driverExceptionDuringQuery($this->_driver, $ex, $args[0]);
        }

        $statement->setFetchMode($this->defaultFetchMode);

        if ($logger) {
            $logger->stopQuery();
        }

        return $statement;
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
