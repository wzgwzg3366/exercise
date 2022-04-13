<?php
/**
 * @copyright Copyright (c) 2018 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larvacent.com/
 * @license http://www.larvacent.com/license/
 */

namespace Larva\Supports;


/**
 * Class HtmlHelper
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class HtmlHelper
{
    /**
     * Encodes special characters into HTML entities.
     * @param string $content the content to be encoded
     * @param bool $doubleEncode whether to encode HTML entities in `$content`. If false,
     * HTML entities in `$content` will not be further encoded.
     * @return string the encoded content
     * @see decode()
     * @see http://www.php.net/manual/en/function.htmlspecialchars.php
     */
    public static function encode($content, $doubleEncode = true)
    {
        return htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', $doubleEncode);
    }

    /**
     * Decodes special HTML entities back to the corresponding characters.
     * This is the opposite of [[encode()]].
     * @param string $content the content to be decoded
     * @return string the decoded content
     * @see encode()
     * @see http://www.php.net/manual/en/function.htmlspecialchars-decode.php
     */
    public static function decode($content)
    {
        return htmlspecialchars_decode($content, ENT_QUOTES);
    }

    /**
     * Will take an HTML string and an associative array of key=>value pairs, HTML encode the values and swap them back
     * into the original string using the keys as tokens.
     *
     * @param string $html The HTML string.
     * @param array $variables An associative array of key => value pairs to be applied to the HTML string using `strtr`.
     * @return string The HTML string with the encoded variable values swapped in.
     */
    public static function encodeParams(string $html, array $variables = []): string
    {
        // Normalize the param keys
        $normalizedVariables = [];
        if (is_array($variables)) {
            foreach ($variables as $key => $value) {
                $key = '{' . trim($key, '{}') . '}';
                $normalizedVariables[$key] = static::encode($value);
            }
            $html = strtr($html, $normalizedVariables);
        }
        return $html;
    }

    /**
     * 检测 Html 编码
     * @param string $content
     * @return string
     */
    public static function getCharSet($content)
    {
        if (preg_match("/<meta.+?charset=[^\\w]?([-\\w]+)/i", $content, $match)) {
            return strtoupper($match [1]);
        } else { // 检测中文常用编码
            return strtoupper(mb_detect_encoding($content, ['ASCII', 'CP936', 'GB2312', 'GBK', 'GB18030', 'UTF-8', 'BIG-5']));
        }
    }

    /**
     * 提取所有的 Head 标签返回一个数组
     * @param string $content
     * @return array
     */
    public static function getHeadTags($content)
    {
        $result = [];
        if (is_string($content) && !empty ($content)) {
            if (($chatSet = static::getCharSet($content)) != 'UTF-8') { // 转码
                $content = mb_convert_encoding($content, 'UTF-8', $chatSet);
            }
            // 解析title
            if (preg_match('#<title[^>]*>(.*?)</title>#si', $content, $match)) {
                $result ['title'] = trim(strip_tags($match [1]));
            }

            // 解析meta
            if (preg_match_all('/<[\s]*meta[\s]*name="?' . '([^>"]*)"?[\s]*' . 'content="?([^>"]*)"?[\s]*[\/]?[\s]*>/si', $content, $match)) {
                // name转小写
                $names = array_map('strtolower', $match [1]);
                $values = $match [2];
                $nameTotal = count($names);
                for ($i = 0; $i < $nameTotal; $i++) {
                    $result ['metaTags'] [$names [$i]] = $values [$i];
                }
            }
            if (isset ($result ['metaTags'] ['keywords'])) {//将关键词切成数组
                $keywords = str_replace(['，', '|', '、', ' '], ',', $result ['metaTags'] ['keywords']);
                $result ['keywords'] = explode(',', $keywords);
            }
        }
        return $result;
    }

    /**
     * 获取页面内的所有链接
     * @param string $url
     * @return array
     */
    public static function getOutLink($url)
    {
        $parse = parse_url($url);
        $hostname = $parse ['host'];
        $Client = new HttpProClient ();
        /** @var HttpResponse $response */
        $response = $Client->get($url);
        if ($response->isOk()) {
            return static::getHtmlOutLink($response->getContent(), $hostname);
        }
        return ['count' => 0, 'inlink' => 0, 'outlink' => 0, 'dataList' => []];
    }

    /**
     * 从内容获取外联
     * @param string $content
     * @param string $hostname
     * @return array
     */
    public static function getHtmlOutLink($content, $hostname)
    {
        if (preg_match_all('/<a(.*?)href="(.*?)"(.*?)>(.*?)<\/a>/i', $content, $document)) {
            $links = [];
            $outLinks = [];
            $inLink = 0;
            foreach ($document [2] as $key => $link) {
                $matches = parse_url($link);
                if (!isset ($matches ['host']) || $matches ['host'] == $hostname) { // 内联
                    $inLink++;
                    continue;
                }
                if (!in_array($matches ['host'], $outLinks) && (stripos($link, 'http:') !== false || stripos($link, 'https:') !== false)) {
                    $outLinks [] = $matches ['host'];
                    $links [] = ['title' => $document [4] [$key], 'nofollow' => !strpos($document [1] [$key], 'nofollow') ? 0 : 1, 'url' => $link, 'host' => $matches ['host']];
                } else {
                    continue;
                }
            }
            return ['count' => count($links) + $inLink, 'inlink' => $inLink, 'outlink' => count($links), 'dataList' => $links];
        }
        return ['count' => 0, 'inlink' => 0, 'outlink' => 0, 'dataList' => []];
    }

    /**
     * 获取简介
     * @param string $content
     * @param int $len
     * @return string
     */
    public static function getSummary($content, $len = 190)
    {
        $description = str_replace(["\r\n", "\t", '&ldquo;', '&rdquo;', '&nbsp;'], '', strip_tags($content));
        return mb_substr($description, 0, $len);
    }

    /**
     * 获取缩略图
     * @param $content
     * @return null|string
     */
    public static function getThumb($content)
    {
        //自动提取缩略图
        $matches = [];
        if (preg_match_all("/(src)=([\"|']?)([^ \"'>]+\.(gif|jpg|jpeg|bmp|png))\\2/i", $content, $matches)) {
            return $matches[3][0];
        }
        return null;
    }

    /**
     * 删除所有IMG标签
     * @param string $content
     * @return string|string[]|null
     */
    public static function cleanImg($content)
    {
        $content = preg_replace('/<img[^>]*src=[\'"]?([^>\'"\s]*)[\'"]?[^>]*>/ie', "", $content);
        return $content;
    }

    /**
     * 获取MIP
     * @param $content
     * @return string
     */
    public static function getMIPContent($content)
    {
        $content = preg_replace('/<img[^>]*src=[\'"]?([^>\'"\s]*)[\'"]?[^>]*>/ie', "<mip-img data-carousel=\"carousel\"  class=\"mip-element mip-img\"  src=\"$1\"></mip-img>", $content);
        return $content;
    }

    /**
     * 获取AMP
     * @param $content
     * @return string
     */
    public static function getAMPContent($content)
    {
        $content = preg_replace('/<img[^>]*src=[\'"]?([^>\'"\s]*)[\'"]?[^>]*>/ie', "<amp-img data-carousel=\"carousel\"  class=\"amp-element amp-img\"  src=\"$1\"></amp-img>", $content);
        return $content;
    }
}
