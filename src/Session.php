<?php

namespace Mix\Session;

use Mix\Bean\BeanInjector;
use Mix\Http\Message\Factory\CookieFactory;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Helper\RandomStringHelper;

/**
 * Class Session
 * @package Mix\Session
 * @author liu,jian <coder.keda@gmail.com>
 */
class Session
{

    /**
     * 处理者
     * @var RedisHandler
     */
    public $handler;

    /**
     * @var ServerRequest
     */
    public $request;

    /**
     * @var Response
     */
    public $response;

    /**
     * session名
     * @var string
     */
    public $name = 'session_id';

    /**
     * SessionID 长度
     * @var int
     */
    public $sessionIdLength = 26;

    /**
     * 生存时间
     * @var int
     */
    public $maxLifetime = 7200;

    /**
     * 过期时间
     * @var int
     */
    public $cookieExpires = 0;

    /**
     * 有效的服务器路径
     * @var string
     */
    public $cookiePath = '/';

    /**
     * 有效域名/子域名
     * @var string
     */
    public $cookieDomain = '';

    /**
     * 仅通过安全的 HTTPS 连接传给客户端
     * @var bool
     */
    public $cookieSecure = false;

    /**
     * 仅可通过 HTTP 协议访问
     * @var bool
     */
    public $cookieHttpOnly = false;

    /**
     * SessionID
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
     * 初始化
     * 加载或创建session_id
     */
    public function init()
    {
        $sessionId = $this->request->getAttribute($this->name);
        if (is_null($sessionId)) {
            $sessionId = $this->createId();
        }
        $this->sessionId = $sessionId;
    }

    /**
     * 创建session_id
     * @return string
     */
    public function createId()
    {
        do {
            $sessionId = RandomStringHelper::randomAlphanumeric($this->sessionIdLength);
        } while ($this->handler->exists($sessionId));
        $this->sessionId = $sessionId;
        return $sessionId;
    }

    /**
     * 赋值
     * @param string $name
     * @param $value
     * @return bool
     */
    public function set(string $name, $value)
    {
        $sessionId = $this->getId();
        // 赋值
        $this->handler->set($sessionId, $name, $value, $this->maxLifetime);
        // 更新cookie
        $factory = new CookieFactory();
        $cookie  = $factory->createCookie($this->name, $sessionId, $this->maxLifetime);
        $cookie->withDomain($this->cookieDomain)
            ->withPath($this->cookiePath)
            ->withSecure($this->cookieSecure)
            ->withHttpOnly($this->cookieHttpOnly);
        $this->response->withCookie($cookie);
        return true;
    }

    /**
     * 取值
     * @param  string $name
     * @return mixed
     */
    public function get(string $name)
    {
        return $this->handler->get($this->getId(), $name);
    }

    /**
     * 取所有值
     * @return array
     */
    public function getAttributes()
    {
        return $this->handler->getAttributes($this->getId());
    }

    /**
     * 删除
     * @param string $name
     * @return bool
     */
    public function delete(string $name)
    {
        return $this->handler->delete($this->getId(), $name);
    }

    /**
     * 清除session
     * @return bool
     */
    public function clear()
    {
        return $this->handler->clear($this->getId());
    }

    /**
     * 判断是否存在
     * @param string $name
     * @return bool
     */
    public function has(string $name)
    {
        return $this->handler->has($this->getId(), $name);
    }

    /**
     * 获取session_id
     * @return string
     */
    public function getId()
    {
        return $this->sessionId;
    }

}
