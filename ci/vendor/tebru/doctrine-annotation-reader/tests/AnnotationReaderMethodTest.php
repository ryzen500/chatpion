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
use Tebru\AnnotationReader\Test\Mock\Annotation\AbstractParentMethodAnnotation;
use Tebru\AnnotationReader\Test\Mock\Annotation\BaseClassAnnotation;
use Tebru\AnnotationReader\Test\Mock\Annotation\MultipleAllowedAnnotation;
use Tebru\AnnotationReader\Test\Mock\Annotation\OverriddenClassAnnotation;
use Tebru\AnnotationReader\Test\Mock\Annotation\OverriddenMethodAnnotation;
use Tebru\AnnotationReader\Test\Mock\Annotation\OverriddenMethodOnlyBaseAnnotation;
use Tebru\AnnotationReader\Test\Mock\Annotation\OverriddenMethodOnlyParentAnnotation;
use Tebru\AnnotationReader\Test\Mock\Annotation\ParentClassAnnotation;
use Tebru\AnnotationReader\Test\Mock\Annotation\ParentMethodAnnotation;
use Tebru\AnnotationReader\Test\Mock\BaseClass;
use Tebru\AnnotationReader\Test\Mock\BaseClassInterface;

class AnnotationReaderMethodTest extends TestCase
{
    /**
     * @var AnnotationReaderAdapter
     */
    private $reader;

    public function setUp()
    {
        $this->reader = new AnnotationReaderAdapter(new AnnotationReader(), new Psr16Cache(new NullAdapter()));
    }

    /**
     * @dataProvider getClasses
     */
    public function testReadMultipleAnnotationOfSingleType(string $className)
    {
        $annotations = $this->readMethod('multipleAnnotationOfSingleType', $className, false, false)->getAll(MultipleAllowedAnnotation::class);

        self::assertCount(2, $annotations);
        self::assertSame('foo', $annotations[0]->getValue());
        self::assertSame('bar', $annotations[1]->getValue());
    }

    /**
     * @dataProvider getClasses
     */
    public function testOverriddenParentMethod(string $className)
    {
        $annotation = $this->readMethod('overriddenParentMethod', $className)->get(OverriddenMethodAnnotation::class);

        self::assertSame('bar', $annotation->getValue());
    }

    /**
     * @dataProvider getClasses
     */
    public function testOverriddenAbstractParentMethod(string $className)
    {
        $annotation = $this->readMethod('overriddenAbstractParentMethod', $className)->get(OverriddenMethodAnnotation::class);

        self::assertSame('bar', $annotation->getValue());
    }

    /**
     * @dataProvider getClasses
     */
    public function testOverriddenParentMethodWithDifferentAnnotationSets(string $className)
    {
        $collection = $this->readMethod('overriddenParentMethodWithDifferentAnnotationSets', $className);

        self::assertSame('bar', $collection->get(OverriddenMethodAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(OverriddenMethodOnlyBaseAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(OverriddenMethodOnlyParentAnnotation::class)->getValue());
    }

    /**
     * @dataProvider getClasses
     */
    public function testOverriddenAbstractParentMethodWithDifferentAnnotationSets(string $className)
    {
        $collection = $this->readMethod('overriddenAbstractParentMethodWithDifferentAnnotationSets', $className);

        self::assertSame('bar', $collection->get(OverriddenMethodAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(OverriddenMethodOnlyBaseAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(OverriddenMethodOnlyParentAnnotation::class)->getValue());
    }

    /**
     * @dataProvider getClasses
     */
    public function testOverriddenParentWithMultipleAllowedAnnotations(string $className)
    {
        $annotations = $this->readMethod('overriddenParentWithMultipleAllowedAnnotations', $className)->getAll(MultipleAllowedAnnotation::class);

        self::assertCount(2, $annotations);
        self::assertSame('bar', $annotations[0]->getValue());
        self::assertSame('foo', $annotations[1]->getValue());
    }

    /**
     * @dataProvider getClasses
     */
    public function testOverriddenAbstractParentWithMultipleAllowedAnnotations(string $className)
    {
        $annotations = $this->readMethod('overriddenAbstractParentWithMultipleAllowedAnnotations', $className)->getAll(MultipleAllowedAnnotation::class);

        self::assertCount(2, $annotations);
        self::assertSame('bar', $annotations[0]->getValue());
        self::assertSame('foo', $annotations[1]->getValue());
    }

    /**
     * @dataProvider getClasses
     */
    public function testParentMethod(string $className)
    {
        $annotation = $this->readMethod('parentMethod', $className)->get(ParentMethodAnnotation::class);

        self::assertSame('foo', $annotation->getValue());
    }

    /**
     * @dataProvider getClasses
     */
    public function testAbstractParentMethod(string $className)
    {
        $annotation = $this->readMethod('abstractParentMethod', $className)->get(AbstractParentMethodAnnotation::class);

        self::assertSame('foo', $annotation->getValue());
    }

    /**
     * @dataProvider getClasses
     */
    public function testOverriddenDeclaringClass(string $className)
    {
        $collection = $this->readMethod('overriddenDeclaringClass', $className, true);

        self::assertSame('bar', $collection->get(BaseClassAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(ParentClassAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(AbstractParentClassAnnotation::class)->getValue());
    }

    /**
     * @dataProvider getClasses
     */
    public function testOverriddenParentClass(string $className)
    {
        $collection = $this->readMethod('overriddenParentClass', $className, true);

        self::assertSame('foo', $collection->get(BaseClassAnnotation::class)->getValue());
        self::assertSame('bar', $collection->get(ParentClassAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(AbstractParentClassAnnotation::class)->getValue());
    }

    /**
     * @dataProvider getClasses
     */
    public function testOverriddenAbstractParentClass(string $className)
    {
        $collection = $this->readMethod('overriddenAbstractParentClass', $className, true);

        self::assertSame('foo', $collection->get(BaseClassAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(ParentClassAnnotation::class)->getValue());
        self::assertSame('bar', $collection->get(AbstractParentClassAnnotation::class)->getValue());
    }

    /**
     * @dataProvider getClasses
     */
    public function testInheritMethodAndClassAnnotations(string $className)
    {
        $collection = $this->readMethod('inheritMethodAndClassAnnotations', $className, true);

        self::assertCount(6, $collection);
        self::assertSame('foo', $collection->get(BaseClassAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(ParentClassAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(AbstractParentClassAnnotation::class)->getValue());
        self::assertSame('bar', $collection->get(OverriddenClassAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(ParentMethodAnnotation::class)->getValue());
        self::assertSame('foo', $collection->get(AbstractParentMethodAnnotation::class)->getValue());
    }

    public function getClasses()
    {
        return [
            [BaseClass::class],
            [BaseClassInterface::class],
        ];
    }

    private function readMethod($name, $className, $useClass = false, $useParent = true): AnnotationCollection
    {
        return $this->reader->readMethod($name, $className, $useClass, $useParent);
    }
}
