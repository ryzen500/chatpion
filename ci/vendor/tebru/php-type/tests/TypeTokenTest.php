<?php

namespace Tebru\PhpType\Test;

use ArrayAccess;
use Countable;
use DateTime;
use IteratorAggregate;
use stdClass;
use Tebru\PhpType\Exception\MalformedTypeException;
use Tebru\PhpType\Test\Mock\PhpTypeClassParent;
use Tebru\PhpType\Test\Mock\PhpTypeClassParentParent;
use Tebru\PhpType\Test\Mock\PhpTypeClassWithInterface;
use Tebru\PhpType\Test\Mock\PhpTypeInterface;
use Tebru\PhpType\TypeToken;
use Traversable;
use PHPUnit\Framework\TestCase;

/**
 * Class TypeTokenTest
 *
 * @author Nate Brunette <nate.brunette@wheniwork.com>
 */
class TypeTokenTest extends TestCase
{
    public function testConstructWithSpaces()
    {
        $phpType = new TypeToken(' string ');

        self::assertSame('string', (string)$phpType);
    }
    public function testString()
    {
        $phpType = new TypeToken('string');

        self::assertTrue($phpType->isString());
    }

    public function testInteger()
    {
        $phpType = new TypeToken('integer');

        self::assertTrue($phpType->isInteger());
    }

    public function testInt()
    {
        $phpType = new TypeToken('int');

        self::assertTrue($phpType->isInteger());
    }

    public function testFloat()
    {
        $phpType = new TypeToken('float');

        self::assertTrue($phpType->isFloat());
    }

    public function testDouble()
    {
        $phpType = new TypeToken('double');

        self::assertTrue($phpType->isFloat());
    }

    public function testArray()
    {
        $phpType = new TypeToken('array');

        self::assertTrue($phpType->isArray());
    }

    public function testBoolean()
    {
        $phpType = new TypeToken('boolean');

        self::assertTrue($phpType->isBoolean());
    }

    public function testBool()
    {
        $phpType = new TypeToken('bool');

        self::assertTrue($phpType->isBoolean());
    }

    public function testNull()
    {
        $phpType = new TypeToken('null');

        self::assertTrue($phpType->isNull());
    }

    public function testNullCaps()
    {
        $phpType = new TypeToken('NULL');

        self::assertTrue($phpType->isNull());
    }

    public function testResource()
    {
        $phpType = new TypeToken('resource');

        self::assertTrue($phpType->isResource());
    }

    public function testWildcard()
    {
        $phpType = new TypeToken('?');

        self::assertTrue($phpType->isWildcard());
    }

    public function testScalarInt()
    {
        $phpType = new TypeToken('int');

        self::assertTrue($phpType->isScalar());
    }

    public function testScalarFloat()
    {
        $phpType = new TypeToken('float');

        self::assertTrue($phpType->isScalar());
    }

    public function testScalarString()
    {
        $phpType = new TypeToken('string');

        self::assertTrue($phpType->isScalar());
    }

    public function testScalarBool()
    {
        $phpType = new TypeToken('boolean');

        self::assertTrue($phpType->isScalar());
    }

    public function testNotScalarObject()
    {
        $phpType = new TypeToken('object');

        self::assertFalse($phpType->isScalar());
    }

    public function testNotScalarArray()
    {
        $phpType = new TypeToken('array');

        self::assertFalse($phpType->isScalar());
    }

    public function testObject()
    {
        $phpType = new TypeToken('object');

        self::assertTrue($phpType->isObject());
        self::assertSame(stdClass::class, $phpType->getRawType());
    }

    public function testStdClass()
    {
        $phpType = new TypeToken(stdClass::class);

        self::assertTrue($phpType->isObject());
        self::assertSame(stdClass::class, $phpType->getRawType());
    }

    public function testCustomClass()
    {
        $phpType = new TypeToken(stdClass::class);

        self::assertTrue($phpType->isObject());
        self::assertSame(stdClass::class, $phpType->getRawType());
    }

    public function testGetPhpTypeString()
    {
        $phpType = new TypeToken('string');

        self::assertSame('string', $phpType->getPhpType());
    }

    public function testGetPhpTypeObject()
    {
        $phpType = new TypeToken(stdClass::class);

        self::assertSame('object', $phpType->getPhpType());
    }

    public function testOneGeneric()
    {
        $phpType = new TypeToken('array<int>');

        self::assertTrue($phpType->isArray());
        self::assertCount(1, $phpType->getGenerics());
        self::assertSame('integer', (string)$phpType->getGenerics()[0]);
    }

    public function testTwoGeneric()
    {
        $phpType = new TypeToken('array<string, int>');

        self::assertTrue($phpType->isArray());
        self::assertCount(2, $phpType->getGenerics());
        self::assertSame('string', (string) $phpType->getGenerics()[0]);
        self::assertSame('integer', (string) $phpType->getGenerics()[1]);
    }

    public function testThreeGeneric()
    {
        $phpType = new TypeToken('stdClass<string, int, stdClass>');

        self::assertTrue($phpType->isObject());
        self::assertSame(stdClass::class, $phpType->getRawType());
        self::assertCount(3, $phpType->getGenerics());
        self::assertSame('string', (string) $phpType->getGenerics()[0]);
        self::assertSame('integer', (string) $phpType->getGenerics()[1]);
        self::assertSame(stdClass::class, (string) $phpType->getGenerics()[2]->getRawType());
    }

    public function testNestedGeneric()
    {
        $phpType = new TypeToken('array<array<string, stdClass<string, bool>>>');

        self::assertTrue($phpType->isArray());
        self::assertCount(1, $phpType->getGenerics());
        self::assertTrue($phpType->getGenerics()[0]->isArray());
        self::assertCount(2, $phpType->getGenerics()[0]->getGenerics());
        self::assertSame('string', (string) $phpType->getGenerics()[0]->getGenerics()[0]);
        self::assertSame(stdClass::class, (string) $phpType->getGenerics()[0]->getGenerics()[1]->getRawType());
        self::assertCount(2, $phpType->getGenerics()[0]->getGenerics()[1]->getGenerics());
        self::assertSame('string', (string) $phpType->getGenerics()[0]->getGenerics()[1]->getGenerics()[0]);
        self::assertSame('boolean', (string) $phpType->getGenerics()[0]->getGenerics()[1]->getGenerics()[1]);
    }

    public function testGenericNoEndingBracket()
    {
        try {
            new TypeToken('array<string');
        } catch (MalformedTypeException $exception) {
            self::assertSame('Could not find ending ">" for generic type', $exception->getMessage());
        }
    }

    public function testGetTypeClass()
    {
        $type = new TypeToken(DateTime::class);

        self::assertSame(DateTime::class, $type->getRawType());
    }

    public function testGetTypeArray()
    {
        $type = new TypeToken('array');

        self::assertSame('array', $type->getRawType());
    }

    public function testGetTypeArrayWithGenerics()
    {
        $type = new TypeToken('array<int>');

        self::assertSame('array', $type->getRawType());
    }

    public function testIsAClass()
    {
        $type = new TypeToken(PhpTypeClassWithInterface::class);

        self::assertTrue($type->isA(PhpTypeClassWithInterface::class));
        self::assertTrue($type->isA(PhpTypeClassParent::class));
        self::assertTrue($type->isA(PhpTypeClassParentParent::class));
        self::assertTrue($type->isA(PhpTypeInterface::class));
        self::assertTrue($type->isA(ArrayAccess::class));
        self::assertTrue($type->isA(IteratorAggregate::class));
        self::assertTrue($type->isA(Countable::class));
        self::assertTrue($type->isA(Traversable::class));
        self::assertFalse($type->isA(stdClass::class));
    }

    public function testIsAArray()
    {
        $type = new TypeToken('array');

        self::assertTrue($type->isA('array'));
    }

    public function testIsAArrayGenerics()
    {
        $type = new TypeToken('array<int>');

        self::assertTrue($type->isA('array'));
        self::assertTrue($type->isA('array<int>'));
    }

    public function testIsACanonical()
    {
        $type = new TypeToken('int');

        self::assertTrue($type->isA('integer'));
    }

    public function testToString()
    {
        $phpType = new TypeToken('array<array<string, stdClass<string, bool>>>');

        self::assertSame('array<array<string, stdClass<string, bool>>>', (string) $phpType);
    }

    public function testToStringReturnsCanonicalType()
    {
        $phpType = new TypeToken('int');

        self::assertSame('integer', (string) $phpType);
    }

    public function testCreateFromVariableObject()
    {
        self::assertSame(stdClass::class, (string) TypeToken::createFromVariable(new stdClass()));
    }

    public function testCreateFromVariableInteger()
    {
        self::assertSame('integer', (string) TypeToken::createFromVariable(1));
    }

    public function testCreateFromVariableFloat()
    {
        self::assertSame('float', (string) TypeToken::createFromVariable(1.1));
    }

    public function testCreateFromVariableString()
    {
        self::assertSame('string', (string) TypeToken::createFromVariable('foo'));
    }

    public function testCreateFromVariableBooleanTrue()
    {
        self::assertSame('boolean', (string) TypeToken::createFromVariable(true));
    }

    public function testCreateFromVariableBooleanFalse()
    {
        self::assertSame('boolean', (string) TypeToken::createFromVariable(false));
    }

    public function testCreateFromVariableArray()
    {
        self::assertSame('array', (string) TypeToken::createFromVariable([]));
    }

    public function testCreateFromVariableNull()
    {
        self::assertSame('null', (string) TypeToken::createFromVariable(null));
    }

    public function testCreateSingletonReusesType()
    {
        self::assertSame(TypeToken::createFromVariable(false), TypeToken::createFromVariable(true));
    }
}
