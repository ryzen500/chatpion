<?php

namespace InstagramAPI\Response\Model\Bloks;

use InstagramAPI\AutoPropertyMapper;

/**
 * TextThemedColor.
 *
 * @method ThemedColor getBkTypesThemedColor()
 * @method bool isBkTypesThemedColor()
 * @method $this setBkTypesThemedColor(ThemedColor $value)
 * @method $this unsetBkTypesThemedColor()
 */
class TextThemedColor extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'bk.types.ThemedColor' => 'ThemedColor',
    ];
}
