Php Type
========

[![Build Status](https://travis-ci.org/tebru/php-type.svg?branch=master)](https://travis-ci.org/tebru/php-type)
[![Code Coverage](https://scrutinizer-ci.com/g/tebru/php-type/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/tebru/php-type/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/tebru/php-type/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/tebru/php-type/?branch=master)

This library wraps a type string and provides an API around getting
information about the type. It also supports generic type syntax.

General Usage
-------------

The simplest way is to instantiate a new TypeToken and pass in the type.

```php
new TypeToken('string');
```

The class also normalizes the type:

```php
$typeShort = new TypeToken('int');
$typeLong = new TypeToken('integer');

$typeShort->getRawType(); // 'integer'
$typeLong->getRawType(); // 'integer'

$typeShort->getPhpType(); // 'integer'
$typeLong->getPhpType(); // 'integer'

$typeShort->isInteger(); // true
$typeLong->isInteger(); // true
```

Any of the core php types are supported as well as `?` which represents
a wildcard type. This can be used if the type is unknown at the time
the type is instantiated. All of the possible types are represented
as constants on the class.

Classes also work the same

```php
$type = new TypeToken(\My\Foo::class);

$type->getRawType(); // 'My\Foo'
(string)$type; // 'My\Foo'
$type->getPhpType(); // 'object'
$type->isObject(); // true
$type->isA(\My\Foo::class); // true
```

`->isA()` checks the instantiated type's parent classes and interfaces
in addition to the passed in class name.

You can also use generic syntax with angle brackets.

```php
$type = new TypeToken('My\Foo<string, My\Foo2>');

$type->getRawType(); // 'My\Foo'
(string)$type; // 'My\Foo<string, My\Foo2>'
$type->getPhpType(); // 'object'
$type->isObject(); // true
$type->isA(\My\Foo::class); // true

$generics = $type->getGenerics();
(string)$generics[0]; // 'string'
(string)$generics[1]; // 'My\Foo2'
```

Calling `->getGenerics()` will return an array of TypeToken objects.

Nested generics work the same way

```php
new TypeToken('array<string, array<int>>');
```

This could represent an array with string keys and all values are an
array of integers.

If you have a variable, you can get the type using the static factory
method

```php
TypeToken::createFromVariable($variable);
```

This uses the singleton method `::create()` which will return the same instance on duplicate types.
