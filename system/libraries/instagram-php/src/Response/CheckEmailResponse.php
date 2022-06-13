<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * CheckEmailResponse.
 *
 * @method bool getAllowSharedEmailRegistration()
 * @method bool getAvailable()
 * @method bool getConfirmed()
 * @method bool getGdprRequired()
 * @method mixed getMessage()
 * @method string getStatus()
 * @method string getTosVersion()
 * @method bool getValid()
 * @method Model\_Message[] get_Messages()
 * @method bool isAllowSharedEmailRegistration()
 * @method bool isAvailable()
 * @method bool isConfirmed()
 * @method bool isGdprRequired()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool isTosVersion()
 * @method bool isValid()
 * @method bool is_Messages()
 * @method $this setAllowSharedEmailRegistration(bool $value)
 * @method $this setAvailable(bool $value)
 * @method $this setConfirmed(bool $value)
 * @method $this setGdprRequired(bool $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this setTosVersion(string $value)
 * @method $this setValid(bool $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetAllowSharedEmailRegistration()
 * @method $this unsetAvailable()
 * @method $this unsetConfirmed()
 * @method $this unsetGdprRequired()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unsetTosVersion()
 * @method $this unsetValid()
 * @method $this unset_Messages()
 */
class CheckEmailResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'valid'                           => 'bool',
        'available'                       => 'bool',
        'confirmed'                       => 'bool',
        'allow_shared_email_registration' => 'bool',
        'tos_version'                     => 'string',
        'gdpr_required'                   => 'bool',
    ];
}
