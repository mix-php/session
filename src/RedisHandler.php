<?php

namespace Mix\Session;

use Mix\Bean\BeanInjector;
use Mix\Pool\ConnectionPoolInterface;
use Mix\Redis\RedisConnectionInterface;

/**
 * Class RedisHandler
 * @package Mix\Session
 * @author liu,jian <coder.keda@gmail.com>
 */
class RedisHandler implements SessionHandlerInterface
{

    /**
     * 连接池
     * @var ConnectionPoolInterface
     */
    public $pool;

    /**
     * 连接
     * @var RedisConnectionInterface
     */
    public $connection;

    /**
     * Key前缀
     * @var string
     */
    public $keyPrefix = 'SESSION:';

    /**
     * session_id
     * @var string
     */
    protected $sessionId = '';

    /**
     * Authorization constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
    }

    /**
     * 获取连接
     * @return RedisConnectionInterface
     */
    protected function getConnection()
    {
        return $this->pool ? $this->pool->getConnection() : $conn;
    }

    /**
     * 释放连接
     * @param $connection
     * @return bool
     */
    protected function release($connection)
    {
        if (!method_exists($conn, 'release')) {
            return false;
        }
        return call_user_func([$conn, 'release']);
    }

    /**
     * 设置session_id
     * @param string $sessionId
     * @return static
     */
    public function withSessionId(string $sessionId)
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    /**
     * 获取session_id
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * 获取保存的key
     * @param string $sessionId
     * @return string
     */
    public function getSaveKey(string $sessionId)
    {
        return $this->keyPrefix . $sessionId;
    }

    /**
     * 是否存在session_id
     * @param string $sessionId
     * @return bool
     */
    public function exists(string $sessionId)
    {
        $key     = $this->getSaveKey($sessionId);
        $conn    = $this->getConnection();
        $success = $conn->exists($key);
        $this->release($conn);
        return $success ? true : false;
    }

    /**
     * 赋值
     * @param string $name
     * @param $value
     * @param int $maxLifetime
     * @return bool
     */
    public function set(string $name, $value, int $maxLifetime)
    {
        $key     = $this->getSaveKey($this->getSessionId());
        $conn    = $this->getConnection();
        $success = $conn->hMset($key, [$name => serialize($value)]);
        $conn->expire($key, $maxLifetime);
        $this->release($conn);
        return $success ? true : false;
    }

    /**
     * 取值
     * @param string $name
     * @return mixed|null
     */
    public function get(string $name)
    {
        $key   = $this->getSaveKey($this->getSessionId());
        $conn  = $this->getConnection();
        $value = $conn->hGet($key, $name);
        $this->release($conn);
        return $value === false ? null : unserialize($value);
    }

    /**
     * 取所有值
     * @return array
     */
    public function getAttributes()
    {
        $key    = $this->getSaveKey($this->getSessionId());
        $conn   = $this->getConnection();
        $result = $conn->hGetAll($key);
        $this->release($conn);
        foreach ($result as $name => $item) {
            $result[$name] = unserialize($item);
        }
        return $result ?: [];
    }

    /**
     * 删除
     * @param string $name
     * @return bool
     */
    public function delete(string $name)
    {
        $key     = $this->getSaveKey($this->getSessionId());
        $conn    = $this->getConnection();
        $success = $conn->hDel($key, $name);
        $this->release($conn);
        return $success ? true : false;
    }

    /**
     * 清除session
     * @return bool
     */
    public function clear()
    {
        $key     = $this->getSaveKey($this->getSessionId());
        $conn    = $this->getConnection();
        $success = $conn->del($key);
        $this->release($conn);
        return $success ? true : false;
    }

    /**
     * 判断是否存在
     * @param string $name
     * @return bool
     */
    public function has(string $name)
    {
        $key   = $this->getSaveKey($this->getSessionId());
        $conn  = $this->getConnection();
        $exist = $conn->hExists($key, $name);
        $this->release($conn);
        return $exist ? true : false;
    }

}
