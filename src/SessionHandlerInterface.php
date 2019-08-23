<?php

namespace Mix\Session;

/**
 * Interface SessionHandlerInterface
 * @package Mix\Session
 * @author liu,jian <coder.keda@gmail.com>
 */
interface SessionHandlerInterface
{

    /**
     * 设置session_id
     * @param string $sessionId
     * @return static
     */
    public function withSessionId(string $sessionId);

    /**
     * 获取session_id
     * @return string
     */
    public function getSessionId();

    /**
     * 获取保存的key
     * @param string $sessionId
     * @return string
     */
    public function getSaveKey(string $sessionId);

    /**
     * 是否存在session_id
     * @param string $sessionId
     * @return bool
     */
    public function exists(string $sessionId);

    /**
     * 赋值
     * @param string $name
     * @param $value
     * @param int $maxLifetime
     * @return bool
     */
    public function set(string $name, $value, int $maxLifetime);

    /**
     * 取值
     * @param string $name
     * @return mixed|null
     */
    public function get(string $name);

    /**
     * 取所有值
     * @return array
     */
    public function getAttributes();

    /**
     * 删除
     * @param string $name
     * @return bool
     */
    public function delete(string $name);

    /**
     * 清除session
     * @return bool
     */
    public function clear();

    /**
     * 判断是否存在
     * @param string $name
     * @return bool
     */
    public function has(string $name);

}
