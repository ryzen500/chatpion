<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * @method mixed getAppData()
 * @method mixed getChecksum()
 * @method mixed getConfig()
 * @method mixed getError()
 * @method bool isAppData()
 * @method bool isChecksum()
 * @method bool isConfig()
 * @method bool isError()
 * @method $this setAppData(mixed $value)
 * @method $this setChecksum(mixed $value)
 * @method $this setConfig(mixed $value)
 * @method $this setError(mixed $value)
 * @method $this unsetAppData()
 * @method $this unsetChecksum()
 * @method $this unsetConfig()
 * @method $this unsetError()
 * 
 */
class ClientEventLogsResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'checksum' => '',
        'config'   => '',
        'app_data' => '',
        'error' => '',
    ];
}
