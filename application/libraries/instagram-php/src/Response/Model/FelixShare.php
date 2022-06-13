<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * FelixShare.
 *
 * @method string getText()
 * @method mixed getVideo()
 * @method bool isText()
 * @method bool isVideo()
 * @method $this setText(string $value)
 * @method $this setVideo(mixed $value)
 * @method $this unsetText()
 * @method $this unsetVideo()
 */
class FelixShare extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'video' => '', // TODO
        'text'  => 'string',
    ];
}
