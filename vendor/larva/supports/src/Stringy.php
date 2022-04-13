<?php
/**
 * @copyright Copyright (c) 2018 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larvacent.com/
 * @license http://www.larvacent.com/license/
 */

namespace Larva\Supports;

/**
 * Class Stringy
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Stringy extends \Stringy\Stringy
{
    /**
     * Call Stringy's `charsArray` for backwards compatibility.
     *
     * @return array
     */
    public function getAsciiCharMap(): array
    {
        return $this->charsArray();
    }
}