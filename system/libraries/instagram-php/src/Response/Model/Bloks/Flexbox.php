<?php

namespace InstagramAPI\Response\Model\Bloks;

use InstagramAPI\AutoPropertyMapper;

/**
 * Flexbox.
 *
 * @method string getAlignItems()
 * @method Children[] getChildren()
 * @method Decoration getDecoration()
 * @method string getFlexDirection()
 * @method string getOnClick()
 * @method Style get_Style()
 * @method bool isAlignItems()
 * @method bool isChildren()
 * @method bool isDecoration()
 * @method bool isFlexDirection()
 * @method bool isOnClick()
 * @method bool is_Style()
 * @method $this setAlignItems(string $value)
 * @method $this setChildren(Children[] $value)
 * @method $this setDecoration(Decoration $value)
 * @method $this setFlexDirection(string $value)
 * @method $this setOnClick(string $value)
 * @method $this set_Style(Style $value)
 * @method $this unsetAlignItems()
 * @method $this unsetChildren()
 * @method $this unsetDecoration()
 * @method $this unsetFlexDirection()
 * @method $this unsetOnClick()
 * @method $this unset_Style()
 */
class Flexbox extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'on_click'       => 'string',
        'children'       => 'Children[]',
        '_style'         => 'Style',
        'decoration'     => 'Decoration',
        'flex_direction' => 'string',
        'align_items'    => 'string',
    ];
}
