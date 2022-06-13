<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * AgeEligibilityResponse.
 *
 * @method bool getEligibleToRegister()
 * @method mixed getMessage()
 * @method bool getParentalConsentRequired()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isEligibleToRegister()
 * @method bool isMessage()
 * @method bool isParentalConsentRequired()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setEligibleToRegister(bool $value)
 * @method $this setMessage(mixed $value)
 * @method $this setParentalConsentRequired(bool $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetEligibleToRegister()
 * @method $this unsetMessage()
 * @method $this unsetParentalConsentRequired()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class AgeEligibilityResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'eligible_to_register'      => 'bool',
        'parental_consent_required' => 'bool',
    ];
}
