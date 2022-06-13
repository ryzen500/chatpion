<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * AboutThisAccountResponse.
 *
 * @method Model\Bloks\Layout getLayout()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isLayout()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setLayout(Model\Bloks\Layout $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetLayout()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class AboutThisAccountResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'layout'    => 'Model\Bloks\Layout',
    ];
}
