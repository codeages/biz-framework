<?php

namespace Codeages\Biz\Framework\Dao\CacheStrategy;

use Codeages\Biz\Framework\Dao\CacheStrategy;
use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

class DoubleCacheStrategy extends AbstractCacheStrategy implements CacheStrategy
{
    /**
     * @var CacheStrategy
     */
    private $first;

    /**
     * @var CacheStrategy
     */
    private $second;

    public function setStrategies(CacheStrategy $first, CacheStrategy $second)
    {
        $this->first = $first;
        $this->second = $second;
    }

    public function beforeQuery(GeneralDaoInterface $dao, $method, $arguments)
    {
        $cache = $this->first->beforeQuery($dao, $method, $arguments);
        if ($cache && $cache !== false) {
            return $cache;
        }

        return $this->second->beforeQuery($dao, $method, $arguments);
    }

    public function afterQuery(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
        $this->first->afterQuery($dao, $method, $arguments, $row);
        $this->second->afterQuery($dao, $method, $arguments, $row);
    }

    public function afterCreate(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
        $this->first->afterCreate($dao, $method, $arguments, $row);
        $this->second->afterCreate($dao, $method, $arguments, $row);
    }

    public function afterUpdate(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
        $this->first->afterUpdate($dao, $method, $arguments, $row);
        $this->second->afterUpdate($dao, $method, $arguments, $row);
    }

    public function afterWave(GeneralDaoInterface $dao, $method, $arguments, $affected)
    {
        $this->first->afterWave($dao, $method, $arguments, $affected);
        $this->second->afterWave($dao, $method, $arguments, $affected);
    }

    public function afterDelete(GeneralDaoInterface $dao, $method, $arguments)
    {
        $this->first->afterDelete($dao, $method, $arguments);
        $this->second->afterDelete($dao, $method, $arguments);
    }
}
