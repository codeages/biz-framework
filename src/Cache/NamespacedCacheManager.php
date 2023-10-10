<?php

namespace Codeages\Biz\Framework\Cache;

class NamespacedCacheManager
{
    /**
     * @var CacheManager
     */
    private $cacheManager;

    private $namespace;

    /**
     * @param CacheManager $cacheManager
     * @param string $namespace
     */
    public function __construct(CacheManager $cacheManager, string $namespace)
    {
        $this->cacheManager = $cacheManager;
        $this->namespace = $namespace;
    }

    public function get(string $key, callable $fallback, array $options = [])
    {
        return $this->cacheManager->get($this->namespace, $key, $fallback, $options);
    }

    public function getById($id, callable $fallback, array $options = [])
    {
        return $this->cacheManager->getById($this->namespace, $id, $fallback, $options);
    }

    public function getByRef(string $key, callable $fallback, array $options = [])
    {
        return $this->cacheManager->getByRef($this->namespace,$key, $fallback, $options);
    }

    public function del($key)
    {
        $this->cacheManager->del($this->namespace, $key);
    }
}