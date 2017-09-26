<?php

namespace Codeages\Biz\Framework\Session\Handler;

/**
 * RedisSessionHandler.
 */
class RedisSessionHandler implements \SessionHandlerInterface
{
    /**
     * @var \Redis driver
     */
    private $redis;

    /**
     * @var int Time to live in seconds
     */
    private $ttl;

    /**
     * @var string key prefix for shared environments
     */
    private $prefix;

    public function __construct($biz, array $options = array())
    {
        $this->biz = $biz;
        $this->redis = $biz['redis'];
        $this->ttl = isset($options['max_life_time']) ? (int) $options['max_life_time'] : 86400;
        $this->prefix = isset($options['prefix']) ? $options['prefix'] : 'biz_session';
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        return $this->redis->get($this->prefix.':'.$sessionId) ?: '';
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data)
    {
        $this->redis->setex($this->prefix.':'.$sessionId, $this->ttl, $data);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        $this->redis->delete($this->prefix.':'.$sessionId);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        return true;
    }
}
