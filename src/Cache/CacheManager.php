<?php

namespace Codeages\Biz\Framework\Cache;

use Redis;
use UnexpectedValueException;

class CacheManager
{
    /**
     * @var Redis | null
     */
    private $redis;

    /**
     * 是否启用缓存
     *
     * @var bool
     */
    private $enabled;

    /**
     * @var int 缓存的生命周期(单位：秒)
     */
    private $ttl = 3600;

    public function __construct($redis, array $options = [])
    {
        $this->redis = $redis;
        $this->enabled = $options['enabled'] ?? true;

        if (!empty($options['enabled'])) {
            $this->enabled = !! $options['enabled'];
        }

        if (!empty($options['ttl'])) {
            $this->ttl = (int) $options['ttl'];
        }
    }

    public function get(string $namespace, string $key, callable $fallback, array $options = [])
    {
        if (!$this->enabled) {
            return $fallback();
        }

        $key = $this->_key($namespace, $key);

        $obj = $this->redis->get($key);

        if ($obj === false) {
            $obj = $fallback();
            $this->redis->set($key, $obj, $this->_ttl($options));
            return $obj;
        } else {
            return $obj;
        }
    }

    public function getById(string $namespace, $id, callable $fallback, array $options = [])
    {
        return $this->get($namespace, "id_${id}", $fallback, $options);
    }

    public function getByRef($namespace, $key, $fallback, $options = [])
    {
        if (!$this->enabled) {
            return $fallback();
        }

        $refKey = $this->_key($namespace, $key);
        $refId = $this->redis->get($refKey);

        if ($refId !== false) {
            if ($refId === null) {
                return null;
            }
            $obj = $this->redis->get($this->_key($namespace, "id_${refId}"));
        } else {
            $obj = false;
        }

        if ($obj !== false) {
            return $obj;
        }

        $obj = $fallback();
        if ($obj !== false) {
            if ($obj === null) {
                $this->redis->set($refKey, null, $this->_ttl($options));
            } else {
                if (empty($obj['id'])) {
                    throw new UnexpectedValueException("Object no id field, cache getByRef failed (namespace: ${namespace}, key: ${key})");
                }
                $this->redis->set($refKey, $obj['id'], $this->ttl);
                $this->redis->set($this->_key($namespace, "id_${obj['id']}"), $obj, $this->_ttl($options));
            }
        } else {
            $this->redis->set($refKey, null, $this->_ttl($options));
        }

        return $obj;
    }

    public function del(string $namespace, $key)
    {
        $keys = [];
        if (is_string($key)) {
            $keys[] = $key;
        } else if (is_array($key) && (array_key_exists('id', $key) || array_key_exists('key', $key))) {
            if (count($key) > 2) {
                $keyStr = json_encode($key);
                throw new \InvalidArgumentException("Key is invalid, delete cache failed (key: ${keyStr}");
            }

            if (array_key_exists('id', $key)) {
                if (is_array($key['id'])) {
                    foreach ($key['id'] as $id) {
                        $keys[] = "id_${id}";
                    }
                } else {
                    $keys[] = "id_${key['id']}";
                }
            }

            if (array_key_exists('key', $key)) {
                if (is_array($key['key'])) {
                    foreach ($key['key'] as $k) {
                        $keys[] = (string) $k;
                    }
                } else {
                    $keys[] = (string) $key['key'];
                }
            }
        } else if (is_array($key)) {
            foreach ($key as $k) {
                $keys[] = (string) $k;
            }
        } else {
            $keyStr = json_encode($key);
            throw new \InvalidArgumentException("Key is invalid, delete cache failed (key: ${keyStr}");
        }

        array_walk($keys, function (&$key) use ($namespace) {
            $key = $this->_key($namespace, $key);
        });

        $this->redis->del($keys);
    }

    private function _ttl(array $options)
    {
        return isset($options['ttl']) ? (int) $options['ttl'] : $this->ttl;
    }

    private function _key(string $namespace, string $key): string
    {
        return "{$namespace}::{$key}";
    }
}