<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\ExclusionStrategies;

use Tebru\Gson\ClassMetadata;
use Tebru\Gson\Exclusion\ClassDeserializationExclusionStrategy;
use Tebru\Gson\Exclusion\ClassSerializationExclusionStrategy;
use Tebru\Gson\Test\Mock\UserMock;

/**
 * Class UserMockExclusionStrategy
 *
 * @author Nate Brunette <n@tebru.net>
 */
class UserMockExclusionStrategy implements ClassSerializationExclusionStrategy, ClassDeserializationExclusionStrategy
{
    /**
     * Returns true if the class should be skipped during deserialization
     *
     * @param ClassMetadata $class
     * @return bool
     */
    public function skipDeserializingClass(ClassMetadata $class): bool
    {
        return UserMock::class === $class->getName();
    }

    /**
     * Returns true if the class should be skipped during serialization
     *
     * @param ClassMetadata $class
     * @return bool
     */
    public function skipSerializingClass(ClassMetadata $class): bool
    {
        return UserMock::class === $class->getName();
    }

    /**
     * Return true if the result of the strategy should be cached
     *
     * @return bool
     */
    public function shouldCache(): bool
    {
        return false;
    }
}
