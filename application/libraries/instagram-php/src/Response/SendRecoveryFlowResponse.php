<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * SendRecoveryFlowResponse.
 *
 * @method string getBody()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method string getTitle()
 * @method Model\_Message[] get_Messages()
 * @method bool isBody()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool isTitle()
 * @method bool is_Messages()
 * @method $this setBody(string $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this setTitle(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetBody()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unsetTitle()
 * @method $this unset_Messages()
 */
class SendRecoveryFlowResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'title' => 'string',
        'body'  => 'string',
    ];
}
