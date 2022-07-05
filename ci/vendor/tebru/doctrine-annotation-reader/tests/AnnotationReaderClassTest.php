<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\AnnotationReader\Test;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Tebru\AnnotationReader\AnnotationCollection;
use Tebru\AnnotationReader\AnnotationReaderAdapter;
use Tebru\AnnotationReader\Test\Mock\AbstractParentClass;
use Tebru\AnnotationReader\Test\Mock\Annotation\AbstractParentClassAnnotation;
use Tebru\AnnotationReader\Test\Mock\Annotation\BaseClassAnnotation;
use Tebru\AnnotationReader\Test\Mock\Annotation\OverriddenClassAnnotation;
use Tebru\AnnotationReader\Test\Mock\Annotation\ParentClassAnnotation;
use Tebru\AnnotationReader\Test\Mock\BaseClass;
use Tebru\AnnotationReader\Test\Mock\ParentClass;

class AnnotationReaderClassTest extends TestCase
{
    /**
     * @var AnnotationReaderAdapter
     */
    private $reader;

    public function setUp()
    {
        $this->reader = new AnnotationReaderAdapter(new AnnotationReader(), new Psr16Cache(new NullAdapter()));
    }


    public function testReadBaseClass()
    {
        $annotation = $this->readClass(BaseClass::class, false)->get(BaseClassAnnotation::class);

        self::assertSame('foo', $annotation->getValue());
    }

    public function testReadParentClass()
    {
        $annotation = $this->readClass(ParentClass::class, false)->get(ParentClassAnnotation::class);

        self::assertSame('foo', $annotation->getValue());
    }

    public function testReadAbstractParentClass()
    {
        $annotation = $this->readClass(AbstractParentClass::class, false)->get(AbstractParentClassAnnotation::class);

        self::assertSame('foo', $annotation->getValue());
    }

    public function testReadBaseClassInherit()
    {
        $collection = $this->readClass(BaseClass::class);

        self::assertCount(4, $collection);
        self::assertSame('foo', $collection->get(BaseClassAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(ParentClassAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(AbstractParentClassAnnotation::class)->getValue());
        self::assertSame('bar', $collection->get(OverriddenClassAnnotation::class)->getValue());
    }

    public function testReadParentClassInherit()
    {
        $collection = $this->readClass(ParentClass::class);

        self::assertCount(3, $collection);
        self::assertSame('foo', $collection->get(ParentClassAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(AbstractParentClassAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(OverriddenClassAnnotation::class)->getValue());
    }

    private function readClass($name, $useParent = true): AnnotationCollection
    {
        return $this->reader->readClass($name, $useParent);
    }
}
