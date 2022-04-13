<?php
/**
 * @copyright Copyright (c) 2018 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larvacent.com/
 * @license http://www.larvacent.com/license/
 */

namespace Larva\Supports;

use GuzzleHttp\Psr7\Response;
use Larva\Supports\Exception\InvalidCallException;
use Larva\Supports\Exception\UnknownMethodException;
use Larva\Supports\Exception\UnknownPropertyException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class HttpResponse
 * @property-read array $data
 * @mixin Response
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class HttpResponse
{
    /**
     * @var ResponseInterface
     */
    protected $rawResponse;

    /**
     * @var string|null raw content
     */
    private $_content;

    /**
     * @var mixed
     */
    private $_data = null;

    /**
     * Response constructor.
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->rawResponse = $response;
        $this->_content = (string)$this->rawResponse->getBody()->getContents();
    }

    /**
     * @return ResponseInterface
     */
    public function getRawResponse()
    {
        return $this->rawResponse;
    }

    /**
     * 获取服务器类型
     * @return string
     */
    public function getServer()
    {
        if ($this->hasHeader('Server')) {
            return $this->getHeaderLine('Server');
        }
        return 'Unknown';
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->getHeaderLine('Content-Type');
    }

    /**
     * Returns the data fields, parsed from raw content.
     * @return array content data fields.
     */
    public function getData()
    {
        if (!$this->_data) {
            $contentType = $this->getContentType();
            $format = $this->detectFormatByContentType($contentType);
            if ($format === null) {
                $format = $this->detectFormatByContent($this->getContent());
            }
            switch ($format) {
                case 'json':
                    $this->_data = Json::decode($this->getContent());
                    break;
                case 'urlencoded':
                    $data = [];
                    parse_str($this->getContent(), $data);
                    $this->_data = $data;
                    break;
                case 'xml':
                    if (preg_match('/charset=(.*)/i', $contentType, $matches)) {
                        $encoding = $matches[1];
                    } else {
                        $encoding = 'UTF-8';
                    }
                    $dom = new \DOMDocument('1.0', $encoding);
                    $dom->loadXML($this->getContent(), LIBXML_NOCDATA);
                    $this->_data = $this->convertXmlToArray(simplexml_import_dom($dom->documentElement));
                    break;
            }
        }
        return $this->_data;
    }

    /**
     * Returns HTTP message raw content.
     * @return string raw body.
     */
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @link http://php.net/manual/en/function.stream-get-meta-data.php
     * @param string $key Specific metadata to retrieve.
     * @return array|mixed|null Returns an associative array if no key is
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     */
    public function getMetadata($key = null)
    {
        return $this->rawResponse->getBody()->getMetadata($key);
    }

    /**
     * Checks if response status code is OK (status code = 20x)
     * @return bool whether response is OK.
     */
    public function isOk()
    {
        return strncmp('20', $this->getStatusCode(), 2) === 0;
    }

    /**
     * 是否是有效的响应码
     *
     * @return boolean
     */
    public function isInvalid()
    {
        return $this->getStatusCode() < 100 || $this->getStatusCode() >= 600;
    }

    /**
     * 是否是重定向响应
     *
     * @return boolean
     */
    public function isRedirection()
    {
        return $this->getStatusCode() >= 300 && $this->getStatusCode() < 400;
    }

    /**
     * 是否请求客户端错误
     *
     * @return boolean
     */
    public function isClientError()
    {
        return $this->getStatusCode() >= 400 && $this->getStatusCode() < 500;
    }

    /**
     * 服务端是否发生错误
     *
     * @return boolean
     */
    public function isServerError()
    {
        return $this->getStatusCode() >= 500 && $this->getStatusCode() < 600;
    }

    /**
     * 是否是403
     *
     * @return boolean
     */
    public function isForbidden()
    {
        return $this->getStatusCode() == 403;
    }

    /**
     * 是否是404
     *
     * @return boolean
     */
    public function isNotFound()
    {
        return $this->getStatusCode() == 404;
    }

    /**
     * 是否是空响应
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return in_array($this->getStatusCode(), [201, 204, 304]);
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->getContent();
    }

    /**
     * @param string $name
     * @param array $params
     * @return mixed
     * @throws UnknownMethodException
     */
    public function __call($name, $params)
    {
        if (method_exists($this->rawResponse, $name)) {
            return call_user_func_array([$this->rawResponse, $name], $params);
        }
        throw new UnknownMethodException('Calling unknown method: ' . get_class($this) . "::$name()");
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Returns the value of an object property.
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `$value = $object->property;`.
     * @param string $name the property name
     * @return mixed the property value
     * @throws UnknownPropertyException if the property is not defined
     * @throws InvalidCallException if the property is write-only
     * @see __set()
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } elseif (method_exists($this, 'set' . $name)) {
            throw new InvalidCallException('Getting write-only property: ' . get_class($this) . '::' . $name);
        }

        throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
    }

    /**
     * Sets value of an object property.
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `$object->property = $value;`.
     * @param string $name the property name or the event name
     * @param mixed $value the property value
     * @throws UnknownPropertyException if the property is not defined
     * @throws InvalidCallException if the property is read-only
     * @see __get()
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new InvalidCallException('Setting read-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * Converts XML document to array.
     * @param string|\SimpleXMLElement $xml xml to process.
     * @return array XML array representation.
     */
    protected function convertXmlToArray($xml)
    {
        if (is_string($xml)) {
            $xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
        $result = (array)$xml;
        foreach ($result as $key => $value) {
            if (!is_scalar($value)) {
                $result[$key] = $this->convertXmlToArray($value);
            }
        }
        return $result;
    }

    /**
     * Detects format from headers.
     * @param string $contentType source content-type.
     * @return null|string format name, 'null' - if detection failed.
     */
    protected function detectFormatByContentType($contentType)
    {
        if (!empty($contentType)) {
            if (stripos($contentType, 'json') !== false) {
                return 'json';
            }
            if (stripos($contentType, 'urlencoded') !== false) {
                return 'urlencoded';
            }
            if (stripos($contentType, 'xml') !== false) {
                return 'xml';
            }
        }
        return null;
    }

    /**
     * Detects response format from raw content.
     * @param string $content raw response content.
     * @return null|string format name, 'null' - if detection failed.
     */
    protected function detectFormatByContent($content)
    {
        if (preg_match('/^\\{.*\\}$/is', $content)) {
            return 'json';
        }
        if (preg_match('/^([^=&])+=[^=&]+(&[^=&]+=[^=&]+)*$/', $content)) {
            return 'urlencoded';
        }
        if (preg_match('/^<.*>$/s', $content)) {
            return 'xml';
        }
        return null;
    }
}