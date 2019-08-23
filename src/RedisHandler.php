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
class RedisHandler
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
     * 获取保存的key
     * @param $sessionId
     * @return string
     */
    protected function getKey($sessionId)
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

            $key     = $this->getKey($sessionId);
            $success = $this->connection->exists($key);
            return $success ? true : false;

        } catch (\Throwable $e) {
            $this->throwException = true;
            throw  $e;
        }
    }

    /**
     * 赋值
     * @param $name
     * @param $value
     * @return bool
     */
    public function set(string $sessionId, string $name, $value, int $maxLifetime)
    {
        try {

            $key     = $this->getKey($sessionId);
            $success = $this->connection->hmset($key, [$name => serialize($value)]);
            $this->connection->expire($key, $maxLifetime);
            return $success ? true : false;

        } catch (\Throwable $e) {
            $this->throwException = true;
            throw  $e;
        }
    }

    /**
     * 取值
     * @param null $name
     * @return mixed
     */
    public function get(string $sessionId, string $name)
    {
        try {

            $key   = $this->getKey($sessionId);
            $value = $this->connection->hget($key, $name);
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
    public function getAttributes(string $sessionId)
    {
        try {

            $key    = $this->getKey($sessionId);
            $result = $this->connection->hgetall($key);
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
     * @param $name
     * @return bool
     */
    public function delete(string $sessionId, string $name)
    {
        try {

            $key     = $this->getKey($sessionId);
            $success = $this->connection->hdel($key, $name);
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
    public function clear(string $sessionId)
    {
        try {

            $key     = $this->getKey($sessionId);
            $success = $this->connection->del($key);
            return $success ? true : false;

        } catch (\Throwable $e) {
            $this->throwException = true;
            throw  $e;
        }
    }

    /**
     * 判断是否存在
     * @param $name
     * @return bool
     */
    public function has(string $sessionId, string $name)
    {
        try {

            $key   = $this->getKey($sessionId);
            $exist = $this->connection->hexists($key, $name);
            return $exist ? true : false;

        } catch (\Throwable $e) {
            $this->throwException = true;
            throw  $e;
        }
    }

}
