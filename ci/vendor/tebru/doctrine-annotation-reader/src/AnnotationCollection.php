<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\AnnotationReader;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use RuntimeException;
use Traversable;

/**
 * Class AnnotationCollection
 *
 * Stores [@see Annotation]s in a way that gracefully handles rules for
 * multiple annotations.
 *
 * @author Nate Brunette <n@tebru.net>
 */
class AnnotationCollection implements IteratorAggregate, Countable
{
    /**
     * All [@se Annotation] objects
     *
     * @var AbstractAnnotation[]|AbstractAnnotation[][]
     */
    public $annotations = [];

    /**
     * Create new collection from array
     *
     * @param array $annotations
     * @return AnnotationCollection
     */
    public static function createFromArray(array $annotations): AnnotationCollection
    {
        $collection = new static();
        $collection->addArray($annotations);

        return $collection;
    }

    /**
     * Create new collection from collection
     *
     * @param AnnotationCollection $annotations
     * @return AnnotationCollection
     */
    public static function createFromCollection(AnnotationCollection $annotations): AnnotationCollection
    {
        $collection = new static();
        $collection->addCollection($annotations);

        return $collection;
    }

    /**
     * Returns true if the annotation is in the collection
     *
     * @param string $name
     * @return bool
     */
    public function exists(string $name): bool
    {
        return isset($this->annotations[$name]);
    }

    /**
     * Get a single annotation by name
     *
     * @param string $name
     * @return AbstractAnnotation|null
     */
    public function get(string $name): ?AbstractAnnotation
    {
        if (!isset($this->annotations[$name])) {
            return null;
        }

        if (!$this->annotations[$name] instanceof AbstractAnnotation) {
            throw new RuntimeException(\sprintf('Multiple values available for "%s". Use getAll() instead.', $name));
        }

        return $this->annotations[$name];
    }

    /**
     * Get all annotations
     *
     * @param string $name
     * @return AbstractAnnotation[]|null
     */
    public function getAll(string $name): ?array
    {
        if (!isset($this->annotations[$name])) {
            return null;
        }

        if (!\is_array($this->annotations[$name])) {
            throw new RuntimeException(\sprintf('Only one annotation available for "%s". Use get() instead.', $name));
        }

        return $this->annotations[$name];
    }

    /**
     * Add an annotation by name
     *
     * If multiple annotations of this type are allowed, store in array
     *
     * Returns true if the annotation was added
     *
     * @param AbstractAnnotation $annotation
     * @return bool
     */
    public function add(AbstractAnnotation $annotation): bool
    {
        $allowMultiple = $annotation->allowMultiple();
        $name = $annotation->getName();
        $exists = isset($this->annotations[$name]);

        if (!$allowMultiple && $exists) {
            return false;
        }

        if (!$allowMultiple) {
            $this->annotations[$name] = $annotation;
            return true;
        }

        if (!$exists) {
            $this->annotations[$name] = [];
        }

        $this->annotations[$name][] = $annotation;

        return true;
    }

    /**
     * Remove an annotation by name
     *
     * Returns the annotation removed or null
     *
     * @param string $name
     * @return null|AbstractAnnotation
     */
    public function remove(string $name): ?AbstractAnnotation
    {
        if (!isset($this->annotations[$name])) {
            return null;
        }

        try {
            $annotation = $this->get($name);
        } catch (RuntimeException $exception) {
            throw new RuntimeException(\sprintf('Multiple values available for "%s". Use removeAll() instead.', $name));
        }

        unset($this->annotations[$name]);

        return $annotation;
    }

    /**
     * Remove all annotations by name
     *
     * Returns the array of annotations removed or null
     *
     * @param string $name
     * @return null|array
     */
    public function removeAll(string $name): ?array
    {
        if (!isset($this->annotations[$name])) {
            return null;
        }

        try {
            $annotations = $this->getAll($name);
        } catch (RuntimeException $exception) {
            throw new RuntimeException(\sprintf('Only one annotation available for "%s". Use remove() instead.', $name));
        }

        unset($this->annotations[$name]);

        return $annotations;
    }

    /**
     * Add all annotations from array
     *
     * Any duplicate annotations from the provided array that are
     * not allowed will be ignored
     *
     * @param AbstractAnnotation[] $annotations
     * @return int Number added
     */
    public function addArray(array $annotations): int
    {
        $added = 0;
        foreach ($annotations as $annotation) {
            $added += $this->doAdd($annotation);
        }

        return $added;
    }

    /**
     * Add a collection to current collection
     *
     * Any duplicate annotations from the provided collection that are
     * not allowed will be ignored
     *
     * @param AnnotationCollection $collection
     * @return int Number added
     */
    public function addCollection(AnnotationCollection $collection): int
    {
        $added = 0;
        foreach ($collection as $element) {
            if (\is_array($element)) {
                $added += $this->addArray($element);

                continue;
            }

            $added += $this->doAdd($element);
        }

        return $added;
    }

    /**
     * Return annotations as array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->annotations;
    }

    /**
     * Retrieve an external iterator
     *
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->annotations);
    }

    /**
     * Count elements of an object
     *
     * @return int
     */
    public function count(): int
    {
        return \count($this->annotations);
    }

    /**
     * Internal method to add an annotation
     *
     * Ignore the annotation if not the correct type
     *
     * @param mixed $annotation
     * @return int
     */
    private function doAdd($annotation): int
    {
        if (\is_array($annotation)) {
            return $this->addArray($annotation);
        }

        if (!$annotation instanceof AbstractAnnotation) {
            return 0;
        }

        return (int)$this->add($annotation);
    }
}
