<?php

namespace InstagramAPI\Response\Model\Bloks;

use InstagramAPI\AutoPropertyMapper;

/**
 * Children.
 *
 * @method Flexbox getBkComponentsFlexbox()
 * @method Image getBkComponentsImage()
 * @method Text getBkComponentsText()
 * @method bool isBkComponentsFlexbox()
 * @method bool isBkComponentsImage()
 * @method bool isBkComponentsText()
 * @method $this setBkComponentsFlexbox(Flexbox $value)
 * @method $this setBkComponentsImage(Image $value)
 * @method $this setBkComponentsText(Text $value)
 * @method $this unsetBkComponentsFlexbox()
 * @method $this unsetBkComponentsImage()
 * @method $this unsetBkComponentsText()
 */
class Children extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'bk.components.Flexbox' => 'Flexbox',
        'bk.components.Text'    => 'Text',
        'bk.components.Image'   => 'Image',
    ];
}
