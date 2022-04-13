<?php
/**
 * @copyright Copyright (c) 2018 Larva Information Technology Co., Ltd.
 * @link http://www.larvacent.com/
 * @license http://www.larvacent.com/license/
 */

namespace Larva\Supports;

use Larva\Supports\Traits\HasHttpRequest;

/**
 * Http 客户端
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class HttpClient extends BaseObject
{
    use HasHttpRequest {
        post as public;
        get as public;
        postJSON as public;
        postXML as public;
        request as public;
    }

    /**
     * @var float
     */
    public $timeout = 5.0;

    /**
     * @var float
     */
    public $connectTimeout = 5.0;

    /**
     * @var bool Http错误是否抛出异常
     */
    public $httpErrors = false;

    /**
     * @var string
     */
    protected $baseUri = '';

    /**
     * 获取基础路径
     * @return string
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * 设置基础路径
     * @param string $baseUri
     * @return $this
     */
    public function setBaseUri($baseUri)
    {
        $this->baseUri = $baseUri;
        return $this;
    }

    /**
     * 设置参数
     * @param array $httpOptions
     * @return string
     */
    public function setHttpOptions($httpOptions)
    {
        $this->httpOptions = array_merge($this->httpOptions, $httpOptions);
        return $this;
    }

    /**
     * 获取 响应的 Header
     * @param string $url 目标Url
     * @param array $headers Headers
     * @param int $timeout 超时时间
     * @return array|false
     */
    public static function getHeaders($url, $headers = [], $timeout = 5)
    {
        $http = new static([
            'timeout' => $timeout,//请求超时的秒数。使用 0 无限期的等待(默认行为)。
            'connectTimeout' => $timeout,//表示等待服务器响应超时的最大值，使用 0 将无限等待 (默认行为).
            'httpOptions' => [
                'verify' => false,
                'http_errors' => false,
                'headers' => $headers
            ]
        ]);
        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = $http->getHttpClient()->get($url);
        return $response->getHeaders();
    }

    /**
     * 检查 CORS 跨域
     * @param string $url 检查的Url
     * @param string $origin 来源
     * @param int $timeout 超时时间
     * @return bool
     */
    public static function checkCors($url, $origin, $timeout = 50)
    {
        $headers = static::getHeaders($url, ['Referer' => $origin, 'Origin' => $origin], $timeout);
        if (isset($headers['Access-Control-Allow-Origin']) && in_array($headers['Access-Control-Allow-Origin'][0], [$origin, '*'])) {
            return true;
        }
        return false;
    }
}