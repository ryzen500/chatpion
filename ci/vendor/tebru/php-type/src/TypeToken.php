<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\PhpType;

use stdClass;
use Tebru\PhpType\Exception\MalformedTypeException;

/**
 * Class TypeToken
 *
 * Wrapper around core php types and custom types.  It can be used as simply as
 *
 *     new TypeToken('string');
 *
 * To create a string type.
 *
 * This class also allows us to fake generic types.  The syntax to
 * represent generics uses angle brackets <>.
 *
 * For example:
 *
 *     array<int>
 *
 * Would represent an array of ints.
 *
 *     array<string, int>
 *
 * Would represent an array using string keys and int values.
 *
 * They can be combined, like so
 *
 *     array<string, array<int>>
 *
 * To represent a array with string keys and an array of ints as values.
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class TypeToken
{
    public const STRING = 'string';
    public const INTEGER = 'integer';
    public const FLOAT = 'float';
    public const BOOLEAN = 'boolean';
    public const HASH = 'array';
    public const OBJECT = 'object';
    public const NULL = 'null';
    public const RESOURCE = 'resource';
    public const WILDCARD = '?';

    /**
     * The full initial type
     *
     * @var string
     */
    public $fullTypeString;

    /**
     * The core php type (string, int, etc) or class if object
     *
     * @var string
     */
    public $rawType;

    /**
     * The core php type (string, int, object, etc)
     *
     * @var string
     */
    public $phpType;

    /**
     * An array of parent classes and interfaces that a class implements
     *
     * @var array
     */
    public $parents = [];

    /**
     * Generic types, if they exist
     *
     * @var array
     */
    public $genericTypes = [];

    /**
     * An array of cached types
     *
     * @var TypeToken[]
     */
    public static $types = [];

    /**
     * Constructor
     *
     * @param string $type
     */
    public function __construct(string $type)
    {
        $type = \trim($type);
        $this->fullTypeString = $type;
        $this->parseType($type);
    }

    /**
     * Singleton factory for creating types
     *
     * @param string $type
     * @return TypeToken
     */
    public static function create(string $type): TypeToken
    {
        if (!isset(self::$types[$type])) {
            self::$types[$type] = new static($type);
        }

        return self::$types[$type];
    }

    /**
     * Create a new instance from a variable
     *
     * @param mixed $variable
     * @return TypeToken
     */
    public static function createFromVariable($variable): TypeToken
    {
        $type = \is_object($variable) ? \get_class($variable) : \gettype($variable);

        return self::create($type);
    }

    /**
     * Returns the class if an object, or the type as a string
     *
     * @return string
     */
    public function getRawType(): string
    {
        return $this->rawType;
    }

    /**
     * Returns the core php type
     *
     * @return string
     */
    public function getPhpType(): string
    {
        return $this->phpType;
    }

    /**
     * Returns an array of generic types
     *
     * @return TypeToken[]
     */
    public function getGenerics(): array
    {
        return $this->genericTypes;
    }

    /**
     * Returns true if the type matches the class, parent, full type, or one of the interfaces
     *
     * @param string $type
     * @return bool
     */
    public function isA(string $type): bool
    {
        if ($this->rawType === $type) {
            return true;
        }

        if ($this->fullTypeString === $type) {
            return true;
        }

        if (\in_array($type, $this->parents, true)) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if this is a string
     *
     * @return bool
     */
    public function isString(): bool
    {
        return $this->phpType === self::STRING;
    }

    /**
     * Returns true if this is an integer
     *
     * @return bool
     */
    public function isInteger(): bool
    {
        return $this->phpType === self::INTEGER;
    }

    /**
     * Returns true if this is a float
     *
     * @return bool
     */
    public function isFloat(): bool
    {
        return $this->phpType === self::FLOAT;
    }

    /**
     * Returns true if this is a boolean
     *
     * @return bool
     */
    public function isBoolean(): bool
    {
        return $this->phpType === self::BOOLEAN;
    }

    /**
     * Returns true if the type is a scalar type
     *
     * @return bool
     */
    public function isScalar(): bool
    {
        return $this->phpType === self::INTEGER
            || $this->phpType === self::FLOAT
            || $this->phpType === self::STRING
            || $this->phpType === self::BOOLEAN
            || $this->phpType === self::NULL;
    }

    /**
     * Returns true if this is an array
     *
     * @return bool
     */
    public function isArray(): bool
    {
        return $this->phpType === self::HASH;
    }

    /**
     * Returns true if this is an object
     *
     * @return bool
     */
    public function isObject(): bool
    {
        return $this->phpType === self::OBJECT;
    }

    /**
     * Returns true if this is null
     *
     * @return bool
     */
    public function isNull(): bool
    {
        return $this->phpType === self::NULL;
    }

    /**
     * Returns true if this is a resource
     *
     * @return bool
     */
    public function isResource(): bool
    {
        return $this->phpType === self::RESOURCE;
    }

    /**
     * Returns true if the type could be anything
     *
     * @return bool
     */
    public function isWildcard(): bool
    {
        return $this->phpType === self::WILDCARD;
    }

    /**
     * Return the initial type including generics
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->fullTypeString;
    }

    /**
     * Recursively parse type.  If generics are found, this will create
     * new PhpTypes.
     *
     * @param string $type
     * @return void
     * @throws \Tebru\PhpType\Exception\MalformedTypeException If the type cannot be processed
     */
    private function parseType(string $type): void
    {
        $start = \strpos($type, '<');
        if ($start === false) {
            $this->setTypes($type);
            return;
        }

        // get start and end positions of generic
        $end = \strrpos($type, '>');
        if ($end === false) {
            throw new MalformedTypeException('Could not find ending ">" for generic type');
        }

        $originalType = $type;

        // get generic types
        $generics = \substr($type, $start + 1, $end - $start - 1);

        // iterate over subtype to determine if format is <type> or <key, type>
        $depth = 0;
        $type = '';
        foreach (\str_split($generics) as $char) {
            // stepping into another generic type
            if ($char === '<') {
                $depth++;
            }

            // stepping out of generic type
            if ($char === '>') {
                $depth--;
            }

            // if the character is not a comma, or we're not on the first level
            // write the character to the buffer and continue loop
            if ($char !== ',' || $depth !== 0) {
                $type .= $char;
                continue;
            }

            // add new type to list
            $this->genericTypes[] = static::create($type);

            // reset type buffer
            $type = '';
        }

        // add final type
        $this->genericTypes[] = static::create($type);

        // set the main type
        $this->setTypes(\substr($originalType, 0, $start));
    }

    /**
     * Create a type enum and set the class if necessary
     *
     * @param string $rawType
     * @return void
     */
    private function setTypes(string $rawType): void
    {
        $this->phpType = $this->getNormalizedType($rawType);

        // if we're not working with an object, set the raw type to
        // the core php type so we can make sure it's normalized
        if (!$this->isObject()) {
            $this->rawType = $this->phpType;

            // if there aren't any generics, overwrite full type as well
            if ($this->getGenerics() === []) {
                $this->fullTypeString = $this->rawType;
            }
            return;
        }

        // use \stdClass as the class name for generic objects
        $this->rawType = self::OBJECT === $rawType ? stdClass::class : $rawType;

        // if we're dealing with a real class, get parents and interfaces so
        // it's easy to check if the type is an instance of another
        if (\class_exists($rawType)) {
            $this->parents = \array_merge(\class_parents($this->rawType), \class_implements($this->rawType));
        }
    }

    /**
     * Get a normalized core php type
     *
     * @param string $type
     * @return string
     */
    private function getNormalizedType(string $type): string
    {
        switch ($type) {
            case 'string':
                return self::STRING;
            case 'int':
            case 'integer':
                return self::INTEGER;
            case 'double':
            case 'float':
                return self::FLOAT;
            case 'bool':
            case 'boolean':
                return self::BOOLEAN;
            case 'array':
                return self::HASH;
            case 'null':
            case 'NULL':
                return self::NULL;
            case 'resource':
                return self::RESOURCE;
            case '?':
                return self::WILDCARD;
            default:
                return self::OBJECT;
        }
    }
}
