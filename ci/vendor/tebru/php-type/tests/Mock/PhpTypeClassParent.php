<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\PhpType\Test\Mock;

/**
 * Class PhpTypeClassParent
 *
 * @author Nate Brunette <n@tebru.net>
 */
abstract class PhpTypeClassParent extends PhpTypeClassParentParent implements PhpTypeInterface
{
    public function getIterator() {}
    public function offsetExists($offset) {}
    public function offsetGet($offset) {}
    public function offsetSet($offset, $value) {}
    public function offsetUnset($offset) {}
}
