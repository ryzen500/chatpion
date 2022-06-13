<?php

namespace InstagramAPI\Response\Model\Bloks;

use InstagramAPI\AutoPropertyMapper;

/**
 * Text.
 *
 * @method string getText()
 * @method string getTextSize()
 * @method TextThemedColor getTextThemedColor()
 * @method bool isText()
 * @method bool isTextSize()
 * @method bool isTextThemedColor()
 * @method $this setText(string $value)
 * @method $this setTextSize(string $value)
 * @method $this setTextThemedColor(TextThemedColor $value)
 * @method $this unsetText()
 * @method $this unsetTextSize()
 * @method $this unsetTextThemedColor()
 */
class Text extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'text'              => 'string',
        'text_size'         => 'string',
        'text_themed_color' => 'TextThemedColor',
    ];
}
