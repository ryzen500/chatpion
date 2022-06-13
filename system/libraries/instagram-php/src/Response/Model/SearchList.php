<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * SearchList.
 *
 * @method Hashtag getHashtag()
 * @method LocationItem getPlace()
 * @method int getPosition()
 * @method User getUser()
 * @method bool isHashtag()
 * @method bool isPlace()
 * @method bool isPosition()
 * @method bool isUser()
 * @method $this setHashtag(Hashtag $value)
 * @method $this setPlace(LocationItem $value)
 * @method $this setPosition(int $value)
 * @method $this setUser(User $value)
 * @method $this unsetHashtag()
 * @method $this unsetPlace()
 * @method $this unsetPosition()
 * @method $this unsetUser()
 */
class SearchList extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'position' => 'int',
        'user'     => 'User',
        'hashtag'  => 'Hashtag',
        'place'    => 'LocationItem',
    ];
}
