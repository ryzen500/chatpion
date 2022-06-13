<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Session.
 *
 * @method string getDevice()
 * @method string getId()
 * @method bool getIsCurrent()
 * @method float getLatitude()
 * @method string getLocation()
 * @method string getLoginId()
 * @method string getLoginTimestamp()
 * @method float getLongitude()
 * @method string getTimestamp()
 * @method bool isDevice()
 * @method bool isId()
 * @method bool isIsCurrent()
 * @method bool isLatitude()
 * @method bool isLocation()
 * @method bool isLoginId()
 * @method bool isLoginTimestamp()
 * @method bool isLongitude()
 * @method bool isTimestamp()
 * @method $this setDevice(string $value)
 * @method $this setId(string $value)
 * @method $this setIsCurrent(bool $value)
 * @method $this setLatitude(float $value)
 * @method $this setLocation(string $value)
 * @method $this setLoginId(string $value)
 * @method $this setLoginTimestamp(string $value)
 * @method $this setLongitude(float $value)
 * @method $this setTimestamp(string $value)
 * @method $this unsetDevice()
 * @method $this unsetId()
 * @method $this unsetIsCurrent()
 * @method $this unsetLatitude()
 * @method $this unsetLocation()
 * @method $this unsetLoginId()
 * @method $this unsetLoginTimestamp()
 * @method $this unsetLongitude()
 * @method $this unsetTimestamp()
 */
class Session extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'id'              => 'string',
        'location'        => 'string',
        'latitude'        => 'float',
        'longitude'       => 'float',
        'device'          => 'string',
        'timestamp'       => 'string',
        'login_timestamp' => 'string',
        'is_current'      => 'bool',
        'login_id'        => 'string',
    ];
}
