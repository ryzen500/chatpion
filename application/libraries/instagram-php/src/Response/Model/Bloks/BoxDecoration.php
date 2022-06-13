<?php

namespace InstagramAPI\Response\Model\Bloks;

use InstagramAPI\AutoPropertyMapper;

/**
 * BoxDecoration.
 *
 * @method bool getClipping()
 * @method string getCornerRadius()
 * @method bool isClipping()
 * @method bool isCornerRadius()
 * @method $this setClipping(bool $value)
 * @method $this setCornerRadius(string $value)
 * @method $this unsetClipping()
 * @method $this unsetCornerRadius()
 */
class BoxDecoration extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'corner_radius' => 'string',
        'clipping'      => 'bool',
    ];
}
