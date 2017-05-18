<?php

namespace Codeages\Biz\Framework\Dao\CacheStrategy;

use Codeages\Biz\Framework\Dao\CacheStrategy;
use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

/**
 * 表级别缓存策略.
 */
class PrimaryKeyStrategy implements CacheStrategy
{
    /**
     * @var \Redis
     */
    private $redis;

    const LIFE_TIME = 3600;

    public function __construct($redis)
    {
        $this->redis = $redis;
    }

    public function beforeQuery(GeneralDaoInterface $dao, $method, $arguments)
    {
        $key = $this->getCacheKey($dao, $method, $arguments);
        if (!$key) {
            return false;
        }

        return $this->redis->get($key);
    }

    public function afterQuery(GeneralDaoInterface $dao, $method, $arguments, $data)
    {
        $key = $this->getCacheKey($dao, $method, $arguments);
        if (!$key) {
            return ;
        }

        $this->redis->set($key, $data, self::LIFE_TIME);
    }

    public function afterCreate(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
    }

    public function afterUpdate(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
    }

    public function afterDelete(GeneralDaoInterface $dao, $method, $arguments)
    {

    }

    public function afterWave(GeneralDaoInterface $dao, $method, $arguments, $affected)
    {

    }

    protected function getCacheKey(GeneralDaoInterface $dao, $method, $arguments)
    {

    }
}
