<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * LoginActivityResponse.
 *
 * @method mixed getMessage()
 * @method Model\Session[] getSessions()
 * @method string getStatus()
 * @method Model\SuspiciousLogin[] getSuspiciousLogins()
 * @method Model\_Message[] get_Messages()
 * @method bool isMessage()
 * @method bool isSessions()
 * @method bool isStatus()
 * @method bool isSuspiciousLogins()
 * @method bool is_Messages()
 * @method $this setMessage(mixed $value)
 * @method $this setSessions(Model\Session[] $value)
 * @method $this setStatus(string $value)
 * @method $this setSuspiciousLogins(Model\SuspiciousLogin[] $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetMessage()
 * @method $this unsetSessions()
 * @method $this unsetStatus()
 * @method $this unsetSuspiciousLogins()
 * @method $this unset_Messages()
 */
class LoginActivityResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'suspicious_logins'             => 'Model\SuspiciousLogin[]',
        'sessions'                      => 'Model\Session[]',
    ];
}
