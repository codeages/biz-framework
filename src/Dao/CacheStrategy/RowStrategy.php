<?php

namespace Codeages\Biz\Framework\Dao\CacheStrategy;

use Codeages\Biz\Framework\Dao\Annotation\MetadataReader;
use Codeages\Biz\Framework\Dao\CacheStrategy;
use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

/**
 * 行级别缓存策略
 */
class RowStrategy implements CacheStrategy
{
    /**
     * @var \Redis
     */
    private $redis;

    /**
     * @var MetadataReader
     */
    private $metadataReader;

    const LIFE_TIME = 3600;

    public function __construct($redis)
    {
        $this->redis = $redis;
        $this->metadataReader = new MetadataReader();
    }

    public function beforeQuery(GeneralDaoInterface $dao, $method, $arguments)
    {
        $metadata = $this->metadataReader->read($dao);

        $key = $this->key($dao, $metadata, $method, $arguments);
        if (!$key) {
            return false;
        }

        $primaryKey = $this->redis->get($key);
        if ($primaryKey === false) {
            return false;
        }

        return $this->redis->get($primaryKey);
    }

    public function afterQuery(GeneralDaoInterface $dao, $method, $arguments, $data)
    {
        $metadata = $this->metadataReader->read($dao);

        $key = $this->key($dao, $metadata, $method, $arguments);
        if (!$key) {
            return ;
        }

        $primaryKey = $this->saveRowCache($dao, $metadata, $data);

        $this->redis->set($key, $primaryKey, self::LIFE_TIME);
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

    protected function key(GeneralDaoInterface $dao, $metadata, $method, $arguments)
    {
        $argumentsForKey = array();

        if (empty($metadata['cache_key_of_arg_index'][$method])) {
            return false;
        }

        foreach ($metadata['cache_key_of_arg_index'][$method] as $index) {
            $argumentsForKey[] = $arguments[$index];
        }

        $key = "dao:{$dao->table()}:{$method}:";

        return $key.implode(',', $argumentsForKey);
    }

    protected function saveRowCache(GeneralDaoInterface $dao, $metadata, $data)
    {
        $method = $metadata['primary_query_method'];

        $args = array();
        foreach ($metadata['cache_key_of_field_name'][$method] as $field) {
            $args[] = $data[$field];
        }

        $key = $this->key($dao, $metadata, $method, $args);

        $this->redis->set($key, $data, self::LIFE_TIME);

        return $key;
    }

    protected function getPrimaryCacheKey()
    {

    }
}
