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
use Tebru\AnnotationReader\Test\Mock\Annotation\AbstractParentClassAnnotation;
use Tebru\AnnotationReader\Test\Mock\Annotation\AbstractParentPropertyAnnotation;
use Tebru\AnnotationReader\Test\Mock\Annotation\BaseClassAnnotation;
use Tebru\AnnotationReader\Test\Mock\Annotation\MultipleAllowedAnnotation;
use Tebru\AnnotationReader\Test\Mock\Annotation\OverriddenClassAnnotation;
use Tebru\AnnotationReader\Test\Mock\Annotation\OverriddenPropertyAnnotation;
use Tebru\AnnotationReader\Test\Mock\Annotation\OverriddenPropertyOnlyBaseAnnotation;
use Tebru\AnnotationReader\Test\Mock\Annotation\OverriddenPropertyOnlyParentAnnotation;
use Tebru\AnnotationReader\Test\Mock\Annotation\ParentClassAnnotation;
use Tebru\AnnotationReader\Test\Mock\Annotation\ParentPropertyAnnotation;
use Tebru\AnnotationReader\Test\Mock\BaseClass;

class AnnotationReaderPropertyTest extends TestCase
{
    /**
     * @var AnnotationReaderAdapter
     */
    private $reader;

    public function setUp()
    {
        $this->reader = new AnnotationReaderAdapter(new AnnotationReader(), new Psr16Cache(new NullAdapter()));
    }

    public function testReadMultipleAnnotationOfSingleType()
    {
        $annotations = $this->readProperty('multipleAnnotationOfSingleType', false, false)->getAll(MultipleAllowedAnnotation::class);

        self::assertCount(2, $annotations);
        self::assertSame('foo', $annotations[0]->getValue());
        self::assertSame('bar', $annotations[1]->getValue());
    }

    public function testOverriddenParentProperty()
    {
        $annotation = $this->readProperty('overriddenParentProperty')->get(OverriddenPropertyAnnotation::class);

        self::assertSame('bar', $annotation->getValue());
    }

    public function testOverriddenAbstractParentProperty()
    {
        $annotation = $this->readProperty('overriddenAbstractParentProperty')->get(OverriddenPropertyAnnotation::class);

        self::assertSame('bar', $annotation->getValue());
    }

    public function testOverriddenParentPropertyWithDifferentAnnotationSets()
    {
        $collection = $this->readProperty('overriddenParentPropertyWithDifferentAnnotationSets');

        self::assertSame('bar', $collection->get(OverriddenPropertyAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(OverriddenPropertyOnlyBaseAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(OverriddenPropertyOnlyParentAnnotation::class)->getValue());
    }

    public function testOverriddenAbstractParentPropertyWithDifferentAnnotationSets()
    {
        $collection = $this->readProperty('overriddenAbstractParentPropertyWithDifferentAnnotationSets');

        self::assertSame('bar', $collection->get(OverriddenPropertyAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(OverriddenPropertyOnlyBaseAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(OverriddenPropertyOnlyParentAnnotation::class)->getValue());
    }

    public function testOverriddenParentWithMultipleAllowedAnnotations()
    {
        $annotations = $this->readProperty('overriddenParentWithMultipleAllowedAnnotations')->getAll(MultipleAllowedAnnotation::class);

        self::assertCount(2, $annotations);
        self::assertSame('bar', $annotations[0]->getValue());
        self::assertSame('foo', $annotations[1]->getValue());
    }

    public function testOverriddenAbstractParentWithMultipleAllowedAnnotations()
    {
        $annotations = $this->readProperty('overriddenAbstractParentWithMultipleAllowedAnnotations')->getAll(MultipleAllowedAnnotation::class);

        self::assertCount(2, $annotations);
        self::assertSame('bar', $annotations[0]->getValue());
        self::assertSame('foo', $annotations[1]->getValue());
    }

    public function testParentProperty()
    {
        $annotation = $this->readProperty('parentProperty')->get(ParentPropertyAnnotation::class);

        self::assertSame('foo', $annotation->getValue());
    }

    public function testAbstractParentProperty()
    {
        $annotation = $this->readProperty('abstractParentProperty')->get(AbstractParentPropertyAnnotation::class);

        self::assertSame('foo', $annotation->getValue());
    }

    public function testOverriddenDeclaringClass()
    {
        $collection = $this->readProperty('overriddenDeclaringClass', true);

        self::assertSame('bar', $collection->get(BaseClassAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(ParentClassAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(AbstractParentClassAnnotation::class)->getValue());
    }

    public function testOverriddenParentClass()
    {
        $collection = $this->readProperty('overriddenParentClass', true);

        self::assertSame('foo', $collection->get(BaseClassAnnotation::class)->getValue());
        self::assertSame('bar', $collection->get(ParentClassAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(AbstractParentClassAnnotation::class)->getValue());
    }

    public function testOverriddenAbstractParentClass()
    {
        $collection = $this->readProperty('overriddenAbstractParentClass', true);

        self::assertSame('foo', $collection->get(BaseClassAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(ParentClassAnnotation::class)->getValue());
        self::assertSame('bar', $collection->get(AbstractParentClassAnnotation::class)->getValue());
    }

    public function testInheritPropertyAndClassAnnotations()
    {
        $collection = $this->readProperty('inheritPropertyAndClassAnnotations', true);

        self::assertCount(6, $collection);
        self::assertSame('foo', $collection->get(BaseClassAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(ParentClassAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(AbstractParentClassAnnotation::class)->getValue());
        self::assertSame('bar', $collection->get(OverriddenClassAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(ParentPropertyAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(AbstractParentPropertyAnnotation::class)->getValue());
    }

    private function readProperty($name, $useClass = false, $useParent = true): AnnotationCollection
    {
        return $this->reader->readProperty($name, BaseClass::class, $useClass, $useParent);
    }
}
