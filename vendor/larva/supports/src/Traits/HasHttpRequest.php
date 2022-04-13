<?php
/**
 * @copyright Copyright (c) 2018 Larva Information Technology Co., Ltd.
 * @link http://www.larvacent.com/
 * @license http://www.larvacent.com/license/
 */

namespace Larva\Supports\Traits;

use DOMDocument;
use DOMElement;
use DOMText;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Larva\Supports\HttpResponse;
use Larva\Supports\Json;
use Larva\Supports\StringHelper;
use Psr\Http\Message\ResponseInterface;
use SimpleXMLElement;

/**
 * Trait HasHttpRequest
 *
 * @method  string getBaseUri()
 * @method HandlerStack getHandlerStack()
 * @property string $timeout
 * @property string $connectTimeout
 * @property boolean $httpErrors
 */
trait HasHttpRequest
{
    /**
     * Http client.
     *
     * @var null|Client
     */
    protected $httpClient = null;

    /**
     * Http client options.
     *
     * @var array
     */
    public $httpOptions = [];

    /**
     * Make a get request.
     *
     * @param string $endpoint
     * @param array $query
     * @param array $headers
     * @return array
     */
    protected function get($endpoint, $query = [], $headers = [])
    {
        return $this->request('get', $endpoint, [
            'headers' => $headers,
            'query' => $query,
        ]);
    }

    /**
     * 获取JSON
     * @param string $endpoint
     * @param array $query
     * @param array $headers
     * @return array
     */
    protected function getJSON($endpoint, $query = [], $headers = [])
    {
        $headers['Accept'] = 'application/json';
        return $this->get($endpoint, $query, $headers);
    }

    /**
     * Make a post request.
     *
     * @param string $endpoint
     * @param string|array $params
     * @param array $headers
     * @return array
     */
    protected function post($endpoint, $params, $headers = [])
    {
        $options = ['headers' => $headers];
        if (!is_array($params)) {
            $options['body'] = $params;
        } else {
            $options['form_params'] = $params;
        }
        return $this->request('post', $endpoint, $options);
    }

    /**
     * make a post xml request
     * @param string $endpoint
     * @param mixed $data
     * @param array $headers
     * @return mixed
     */
    protected function postXML($endpoint, $data, $headers = [])
    {
        if ($data instanceof DOMDocument) {
            $xml = $data->saveXML();
        } elseif ($data instanceof SimpleXMLElement) {
            $xml = $data->saveXML();
        } else {
            $xml = $this->convertArrayToXml($data);
        }
        $header['Content-Type'] = 'application/xml; charset=UTF-8';
        return $this->post($endpoint, $xml, $headers);
    }

    /**
     * Make a post request.
     *
     * @param string $endpoint
     * @param array $params
     * @param array $headers
     * @return array
     */
    protected function postJSON($endpoint, $params = [], $headers = [])
    {
        $headers['Accept'] = 'application/json';
        $headers['Content-Type'] = 'application/json';
        return $this->post($endpoint, Json::encode($params), $headers);
    }

    /**
     * Make a http request.
     *
     * @param string $method
     * @param string $endpoint
     * @param array $options http://docs.guzzlephp.org/en/latest/request-options.html
     * @return mixed
     */
    protected function request($method, $endpoint, $options = [])
    {
        return $this->unwrapResponse($this->getHttpClient()->{$method}($endpoint, $options));
    }

    /**
     * @param Client $client
     * @return $this
     */
    public function setHttpClient(Client $client)
    {
        $this->httpClient = $client;
        return $this;
    }

    /**
     * Return http client.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient()
    {
        if (is_null($this->httpClient)) {
            $this->httpClient = $this->getDefaultHttpClient();
        }
        return $this->httpClient;
    }

    /**
     * Get default http client.
     *
     * @return Client
     * @author yansongda <me@yansongda.cn>
     *
     */
    protected function getDefaultHttpClient()
    {
        return new Client($this->getOptions());
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
     * Return Guzzle options.
     *
     * @return array
     */
    protected function getOptions()
    {
        $options = array_merge([
            'base_uri' => method_exists($this, 'getBaseUri') ? $this->getBaseUri() : '',
            'timeout' => property_exists($this, 'timeout') ? $this->timeout : 5.0,
            'connect_timeout' => property_exists($this, 'connectTimeout') ? $this->connectTimeout : 5.0,
            'http_errors' => property_exists($this, 'httpErrors') ? $this->httpErrors : true,
            'force_ip_resolve'=>property_exists($this, 'forceIpResolve') ? $this->forceIpResolve : 'v4',
        ], $this->httpOptions);
        if (method_exists($this, 'getHandlerStack')) {
            $options['handler'] = $this->getHandlerStack();
        }
        return $options;
    }

    /**
     * Convert response contents to json.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return array|mixed
     */
    protected function unwrapResponse(ResponseInterface $response)
    {
        $response = new HttpResponse($response);
        if (($data = $response->getData()) != null) {
            return $data;
        }
        return $response->getContent();
    }

    /**
     * Converts array to XML document.
     * @param $arr
     * @return string
     */
    protected function convertArrayToXml($arr)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $root = new DOMElement('xml');
        $dom->appendChild($root);
        $this->buildXml($root, $arr);
        return $dom->saveXML();
    }

    /**
     * Build xml
     * @param DOMElement $element
     * @param mixed $data
     */
    protected function buildXml($element, $data)
    {
        if (is_array($data)) {
            foreach ($data as $name => $value) {
                if (is_int($name) && is_object($value)) {
                    $this->buildXml($element, $value);
                } elseif (is_array($value) || is_object($value)) {
                    $child = new DOMElement(is_int($name) ? 'item' : $name);
                    $element->appendChild($child);
                    $this->buildXml($child, $value);
                } else {
                    $child = new DOMElement(is_int($name) ? 'item' : $name);
                    $element->appendChild($child);
                    $child->appendChild(new DOMText((string)$value));
                }
            }
        } elseif (is_object($data)) {
            $child = new DOMElement(StringHelper::basename(get_class($data)));
            $element->appendChild($child);
            $array = [];
            foreach ($data as $name => $value) {
                $array[$name] = $value;
            }
            $this->buildXml($child, $array);
        } else {
            $element->appendChild(new DOMText((string)$data));
        }
    }
}
