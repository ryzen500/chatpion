<?php

namespace InstagramAPI\Response\Model\Bloks;

use InstagramAPI\AutoPropertyMapper;

/**
 * Image.
 *
 * @method string getScaleType()
 * @method string getUrl()
 * @method bool isScaleType()
 * @method bool isUrl()
 * @method $this setScaleType(string $value)
 * @method $this setUrl(string $value)
 * @method $this unsetScaleType()
 * @method $this unsetUrl()
 */
class Image extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'url'        => 'string',
        'scale_type' => 'string',
    ];
}
