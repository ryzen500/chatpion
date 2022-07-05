<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\PhpType\Test\Mock;

use Countable;

/**
 * Class PhpTypeClassWithInterface
 *
 * @author Nate Brunette <n@tebru.net>
 */
class PhpTypeClassWithInterface extends PhpTypeClassParent implements Countable
{
    public function count() {}
}
