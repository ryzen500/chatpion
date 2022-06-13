<?php

namespace InstagramAPI\Response\Model\Bloks;

use InstagramAPI\AutoPropertyMapper;

/**
 * Layout.
 *
 * @method Collection getBkComponentsCollection()
 * @method bool isBkComponentsCollection()
 * @method $this setBkComponentsCollection(Collection $value)
 * @method $this unsetBkComponentsCollection()
 */
class Layout extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'bk.components.Collection' => 'Collection',
    ];
}
