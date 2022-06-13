<?php

namespace InstagramAPI\Response\Model\Bloks;

use InstagramAPI\AutoPropertyMapper;

/**
 * Style.
 *
 * @method Flex getFlex()
 * @method bool isFlex()
 * @method $this setFlex(Flex $value)
 * @method $this unsetFlex()
 */
class Style extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'flex' => 'Flex',
    ];
}
