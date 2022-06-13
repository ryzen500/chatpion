<?php

namespace InstagramAPI\Response\Model\Bloks;

use InstagramAPI\AutoPropertyMapper;

/**
 * Flex.
 *
 * @method string getHeight()
 * @method string getMarginRight()
 * @method string getPaddingBottom()
 * @method string getPaddingLeft()
 * @method string getPaddingRight()
 * @method string getPaddingTop()
 * @method int getShrink()
 * @method string getWidth()
 * @method bool isHeight()
 * @method bool isMarginRight()
 * @method bool isPaddingBottom()
 * @method bool isPaddingLeft()
 * @method bool isPaddingRight()
 * @method bool isPaddingTop()
 * @method bool isShrink()
 * @method bool isWidth()
 * @method $this setHeight(string $value)
 * @method $this setMarginRight(string $value)
 * @method $this setPaddingBottom(string $value)
 * @method $this setPaddingLeft(string $value)
 * @method $this setPaddingRight(string $value)
 * @method $this setPaddingTop(string $value)
 * @method $this setShrink(int $value)
 * @method $this setWidth(string $value)
 * @method $this unsetHeight()
 * @method $this unsetMarginRight()
 * @method $this unsetPaddingBottom()
 * @method $this unsetPaddingLeft()
 * @method $this unsetPaddingRight()
 * @method $this unsetPaddingTop()
 * @method $this unsetShrink()
 * @method $this unsetWidth()
 */
class Flex extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'width'          => 'string',
        'height'         => 'string',
        'padding_top'    => 'string',
        'padding_left'   => 'string',
        'padding_right'  => 'string',
        'padding_bottom' => 'string',
        'margin_right'   => 'string',
        'shrink'         => 'int',
    ];
}
