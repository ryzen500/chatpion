<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * UsernameSuggestionsResponse.
 *
 * @method mixed getMessage()
 * @method string getStatus()
 * @method mixed getSuggestionsWithMetadata()
 * @method Model\_Message[] get_Messages()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool isSuggestionsWithMetadata()
 * @method bool is_Messages()
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this setSuggestionsWithMetadata(mixed $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unsetSuggestionsWithMetadata()
 * @method $this unset_Messages()
 */
class UsernameSuggestionsResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'suggestions_with_metadata'             => '',
    ];
}
