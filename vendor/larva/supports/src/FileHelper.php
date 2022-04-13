<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Supports;

/**
 * 文件助手
 * @author Tongle Xu <xutongle@gmail.com>
 */
class FileHelper
{
    /**
     * 取得文件后缀
     *
     * @param string $fileName 文件名称
     * @return string
     */
    public static function getSuffix($fileName)
    {
        if (false === ($pos = strrpos($fileName, '.'))) return '';
        return substr($fileName, $pos + 1);
    }
}