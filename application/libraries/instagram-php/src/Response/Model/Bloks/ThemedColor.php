<?php

namespace InstagramAPI\Response\Model\Bloks;

use InstagramAPI\AutoPropertyMapper;

/**
 * ThemedColor.
 *
 * @method string getDarkColor()
 * @method string getLightColor()
 * @method bool isDarkColor()
 * @method bool isLightColor()
 * @method $this setDarkColor(string $value)
 * @method $this setLightColor(string $value)
 * @method $this unsetDarkColor()
 * @method $this unsetLightColor()
 */
class ThemedColor extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'light_color' => 'string',
        'dark_color'  => 'string',
    ];
}
