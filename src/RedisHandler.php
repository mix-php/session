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
    public $sessionId = '';

    /**
     * 是否抛出异常
     * @var bool
     */
    protected $throwException = false;

    /**
     * Authorization constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
    }

    /**
     * 初始化
     */
    public function init()
    {
        // 从连接池获取连接
        if (isset($this->pool)) {
            $this->connection = $this->pool->getConnection();
        }
    }

    /**
     * 析构
     */
    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        // 释放连接
        if (
            isset($this->pool) &&
            isset($this->connection) &&
            !$this->throwException &&
            method_exists($this->connection, 'release')
        ) {
            $this->connection->release();
            $this->connection = null;
        }
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
        try {

            $key     = $this->getSaveKey($sessionId);
            $success = $this->connection->exists($key);
            return $success ? true : false;

        } catch (\Throwable $e) {
            $this->throwException = true;
            throw  $e;
        }
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
        try {

            $key     = $this->getSaveKey($this->getSessionId());
            $success = $this->connection->hMset($key, [$name => serialize($value)]);
            $this->connection->expire($key, $maxLifetime);
            return $success ? true : false;

        } catch (\Throwable $e) {
            $this->throwException = true;
            throw  $e;
        }
    }

    /**
     * 取值
     * @param string $name
     * @return mixed|null
     */
    public function get(string $name)
    {
        try {

            $key   = $this->getSaveKey($this->getSessionId());
            $value = $this->connection->hGet($key, $name);
            return $value === false ? null : unserialize($value);

        } catch (\Throwable $e) {
            $this->throwException = true;
            throw  $e;
        }
    }

    /**
     * 取所有值
     * @return array
     */
    public function getAttributes()
    {
        try {

            $key    = $this->getSaveKey($this->getSessionId());
            $result = $this->connection->hGetAll($key);
            foreach ($result as $name => $item) {
                $result[$name] = unserialize($item);
            }
            return $result ?: [];

        } catch (\Throwable $e) {
            $this->throwException = true;
            throw  $e;
        }
    }

    /**
     * 删除
     * @param string $name
     * @return bool
     */
    public function delete(string $name)
    {
        try {

            $key     = $this->getSaveKey($this->getSessionId());
            $success = $this->connection->hDel($key, $name);
            return $success ? true : false;

        } catch (\Throwable $e) {
            $this->throwException = true;
            throw  $e;
        }
    }

    /**
     * 清除session
     * @return bool
     */
    public function clear()
    {
        try {

            $key     = $this->getSaveKey($this->getSessionId());
            $success = $this->connection->del($key);
            return $success ? true : false;

        } catch (\Throwable $e) {
            $this->throwException = true;
            throw  $e;
        }
    }

    /**
     * 判断是否存在
     * @param string $name
     * @return bool
     */
    public function has(string $name)
    {
        try {

            $key   = $this->getSaveKey($this->getSessionId());
            $exist = $this->connection->hExists($key, $name);
            return $exist ? true : false;

        } catch (\Throwable $e) {
            $this->throwException = true;
            throw  $e;
        }
    }

}
