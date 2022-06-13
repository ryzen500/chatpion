<?php

namespace InstagramAPI\Response\Model\Bloks;

use InstagramAPI\AutoPropertyMapper;

/**
 * Collection.
 *
 * @method Children[] getChildren()
 * @method string getDirection()
 * @method string getId()
 * @method Style get_Style()
 * @method bool isChildren()
 * @method bool isDirection()
 * @method bool isId()
 * @method bool is_Style()
 * @method $this setChildren(Children[] $value)
 * @method $this setDirection(string $value)
 * @method $this setId(string $value)
 * @method $this set_Style(Style $value)
 * @method $this unsetChildren()
 * @method $this unsetDirection()
 * @method $this unsetId()
 * @method $this unset_Style()
 */
class Collection extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'direction' => 'string',
        'children'  => 'Children[]',
        'id'        => 'string',
        '_style'    => 'Style',
    ];
}
