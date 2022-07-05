<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\AnnotationReader\Test;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tebru\AnnotationReader\AnnotationCollection;
use Tebru\AnnotationReader\Test\Mock\Annotation\BaseClassAnnotation;
use Tebru\AnnotationReader\Test\Mock\Annotation\MultipleAllowedAnnotation;

class AnnotationCollectionTest extends TestCase
{
    /**
     * @var AnnotationCollection
     */
    private $collection;

    public function setUp()
    {
        $this->collection = new AnnotationCollection();
    }

    public function testExists()
    {
        $this->collection->add(new BaseClassAnnotation(['value' => 'foo']));

        self::assertTrue($this->collection->exists(BaseClassAnnotation::class));
        self::assertFalse($this->collection->exists('foo'));
    }

    public function testAddSingle()
    {
        $added = $this->collection->add(new BaseClassAnnotation(['value' => 'foo']));

        self::assertTrue($added);
        self::assertSame('foo', $this->collection->get(BaseClassAnnotation::class)->getValue());
    }

    public function testAddMultipleNotAllowed()
    {
        $this->collection->add(new BaseClassAnnotation(['value' => 'foo']));
        $added = $this->collection->add(new BaseClassAnnotation(['value' => 'bar']));

        self::assertFalse($added);
        self::assertSame('foo', $this->collection->get(BaseClassAnnotation::class)->getValue());
    }

    public function testAddMultiple()
    {
        $this->collection->add(new MultipleAllowedAnnotation(['value' => 'foo']));
        $added = $this->collection->add(new MultipleAllowedAnnotation(['value' => 'bar']));
        $annotations = $this->collection->getAll(MultipleAllowedAnnotation::class);

        self::assertTrue($added);
        self::assertSame('foo', $annotations[0]->getValue());
        self::assertSame('bar', $annotations[1]->getValue());
    }

    public function testCreateSingleFromArray()
    {
        $this->collection = AnnotationCollection::createFromArray([new BaseClassAnnotation(['value' => 'foo'])]);

        self::assertSame('foo', $this->collection->get(BaseClassAnnotation::class)->getValue());
    }

    public function testCreateMultipleFromArray()
    {
        $this->collection = AnnotationCollection::createFromArray([
            new MultipleAllowedAnnotation(['value' => 'foo']),
            new MultipleAllowedAnnotation(['value' => 'bar']),
        ]);

        $annotations = $this->collection->getAll(MultipleAllowedAnnotation::class);

        self::assertSame('foo', $annotations[0]->getValue());
        self::assertSame('bar', $annotations[1]->getValue());
    }

    public function testCreateSingleFromCollection()
    {
        $this->collection = AnnotationCollection::createFromCollection(
            AnnotationCollection::createFromArray([new BaseClassAnnotation(['value' => 'foo'])])
        );

        self::assertSame('foo', $this->collection->get(BaseClassAnnotation::class)->getValue());
    }

    public function testCreateMultipleFromCollectionArray()
    {
        $this->collection->addArray([
            new MultipleAllowedAnnotation(['value' => 'foo']),
            new MultipleAllowedAnnotation(['value' => 'bar'])
        ]);
        $collection = AnnotationCollection::createFromArray($this->collection->toArray());
        $annotations = $collection->getAll(MultipleAllowedAnnotation::class);

        self::assertSame('foo', $annotations[0]->getValue());
        self::assertSame('bar', $annotations[1]->getValue());
    }

    public function testCreateMultipleFromCollection()
    {
        $this->collection = AnnotationCollection::createFromCollection(
            AnnotationCollection::createFromArray([
                new MultipleAllowedAnnotation(['value' => 'foo']),
                new MultipleAllowedAnnotation(['value' => 'bar']),
            ])
        );

        $annotations = $this->collection->getAll(MultipleAllowedAnnotation::class);

        self::assertSame('foo', $annotations[0]->getValue());
        self::assertSame('bar', $annotations[1]->getValue());
    }

    public function testAddNonAbstractAnnotation()
    {
        $added = $this->collection->addArray([new BaseClassAnnotation(['value' => 'foo']), new MultipleAllowedAnnotation(['value' => 'foo']), new \stdClass()]);

        self::assertSame(2, $added);
        self::assertCount(2, $this->collection);
        self::assertSame('foo', $this->collection->get(BaseClassAnnotation::class)->getValue());
    }

    public function testGetNotExists()
    {
        $this->collection->add(new BaseClassAnnotation(['value' => 'foo']));

        self::assertNull($this->collection->get('foo'));
    }

    public function testGetArrayThrowsException()
    {
        $this->collection->add(new MultipleAllowedAnnotation(['value' => 'foo']));

        try {
            $this->collection->get(MultipleAllowedAnnotation::class);
        } catch (RuntimeException $exception) {
            self::assertSame('Multiple values available for "Tebru\AnnotationReader\Test\Mock\Annotation\MultipleAllowedAnnotation". Use getAll() instead.', $exception->getMessage());
            return;
        }

        self::assertTrue(false);
    }

    public function testGetAllNotExists()
    {
        $this->collection->add(new MultipleAllowedAnnotation(['value' => 'foo']));

        self::assertNull($this->collection->getAll('foo'));
    }

    public function testGetAnnotationThrowsException()
    {
        $this->collection->add(new BaseClassAnnotation(['value' => 'foo']));

        try {
            $this->collection->getAll(BaseClassAnnotation::class);
        } catch (RuntimeException $exception) {
            self::assertSame('Only one annotation available for "Tebru\AnnotationReader\Test\Mock\Annotation\BaseClassAnnotation". Use get() instead.', $exception->getMessage());
            return;
        }

        self::assertTrue(false);
    }

    public function testRemoveAnnotation()
    {
        $annotation = new BaseClassAnnotation(['value' => 'foo']);
        $this->collection->add($annotation);
        $removed = $this->collection->remove($annotation->getName());

        self::assertSame($annotation, $removed);
    }

    public function testRemoveAllAnnotations()
    {
        $annotations = [new MultipleAllowedAnnotation(['value' => 'foo']), new MultipleAllowedAnnotation(['value' => 'foo'])];
        $this->collection->addArray($annotations);
        $removed = $this->collection->removeAll(MultipleAllowedAnnotation::class);

        self::assertSame($annotations, $removed);
    }

    public function testRemoveAnnotationThrowsException()
    {
        $this->collection->add(new BaseClassAnnotation(['value' => 'foo']));

        try {
            $this->collection->removeAll(BaseClassAnnotation::class);
        } catch (RuntimeException $exception) {
            self::assertSame('Only one annotation available for "Tebru\AnnotationReader\Test\Mock\Annotation\BaseClassAnnotation". Use remove() instead.', $exception->getMessage());
            return;
        }

        self::assertTrue(false);
    }

    public function testRemoveArrayThrowsException()
    {
        $this->collection->add(new MultipleAllowedAnnotation(['value' => 'foo']));

        try {
            $this->collection->remove(MultipleAllowedAnnotation::class);
        } catch (RuntimeException $exception) {
            self::assertSame('Multiple values available for "Tebru\AnnotationReader\Test\Mock\Annotation\MultipleAllowedAnnotation". Use removeAll() instead.', $exception->getMessage());
            return;
        }

        self::assertTrue(false);
    }

    public function testRemoveAnnotationNotExists()
    {
        $annotation = new BaseClassAnnotation(['value' => 'foo']);
        $this->collection->add($annotation);
        $removed = $this->collection->remove('foo');

        self::assertNull($removed);
    }

    public function testRemoveAllAnnotationsNotExists()
    {
        $annotations = [new MultipleAllowedAnnotation(['value' => 'foo']), new MultipleAllowedAnnotation(['value' => 'foo'])];
        $this->collection->addArray($annotations);
        $removed = $this->collection->removeAll('foo');

        self::assertNull($removed);
    }

    public function testCount()
    {
        $this->collection->add(new BaseClassAnnotation(['value' => 'foo']));

        self::assertCount(1, $this->collection);
    }
}
