<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * SendSignupSmsCodeResponse.
 *
 * @method bool getGdprRequired()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method string getTosVersion()
 * @method Model\_Message[] get_Messages()
 * @method bool isGdprRequired()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool isTosVersion()
 * @method bool is_Messages()
 * @method $this setGdprRequired(bool $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this setTosVersion(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetGdprRequired()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unsetTosVersion()
 * @method $this unset_Messages()
 */
class SendSignupSmsCodeResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'tos_version'             => 'string',
        'gdpr_required'           => 'bool',
    ];
}
