<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * SuspiciousLogin.
 *
 * @method string getDevice()
 * @method string getId()
 * @method float getLatitude()
 * @method string getLocation()
 * @method float getLongitude()
 * @method string getTimestamp()
 * @method bool isDevice()
 * @method bool isId()
 * @method bool isLatitude()
 * @method bool isLocation()
 * @method bool isLongitude()
 * @method bool isTimestamp()
 * @method $this setDevice(string $value)
 * @method $this setId(string $value)
 * @method $this setLatitude(float $value)
 * @method $this setLocation(string $value)
 * @method $this setLongitude(float $value)
 * @method $this setTimestamp(string $value)
 * @method $this unsetDevice()
 * @method $this unsetId()
 * @method $this unsetLatitude()
 * @method $this unsetLocation()
 * @method $this unsetLongitude()
 * @method $this unsetTimestamp()
 */
class SuspiciousLogin extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'id'        => 'string',
        'location'  => 'string',
        'latitude'  => 'float',
        'longitude' => 'float',
        'device'    => 'string',
        'timestamp' => 'string',
    ];
}
