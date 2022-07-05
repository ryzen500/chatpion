<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\AnnotationReader;

use Doctrine\Common\Annotations\Reader;
use Psr\SimpleCache\CacheInterface;
use ReflectionClass;

/**
 * Class AnnotationReaderAdapter
 *
 * Provides methods for creating [@see AnnotationCollection]s based on class, method, or
 * property annotations and easily allows inheriting annotations.
 *
 * @author Nate Brunette <n@tebru.net>
 */
class AnnotationReaderAdapter
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * Constructor
     *
     * @param Reader $reader
     * @param CacheInterface $cache
     */
    public function __construct(Reader $reader, CacheInterface $cache)
    {
        $this->reader = $reader;
        $this->cache = $cache;
    }

    /**
     * Reads class annotations and returns a new AnnotationCollection
     *
     * Pass true to second parameter if parent classes an interfaces should
     * also be parsed.
     *
     * @param string $className
     * @param bool $useParent
     * @return AnnotationCollection
     */
    public function readClass(string $className, bool $useParent): AnnotationCollection
    {
        $key = 'annotationreader.'.\str_replace('\\', '', $className);
        $result = $this->cache->get($key);
        if ($result !== null) {
             return AnnotationCollection::createFromArray($result);
        }

        $reflectionClass = new ReflectionClass($className);
        $annotations = $this->reader->getClassAnnotations($reflectionClass);

        $collection = AnnotationCollection::createFromArray($annotations);

        if (!$useParent) {
            return $collection;
        }

        $parents = $this->resolveParents($reflectionClass);

        foreach ($parents as $parent) {
            $collection->addCollection($this->readClass($parent->getName(), true));
        }

        $this->cache->set($key, $collection->toArray());

        return $collection;
    }

    /**
     * Reads method annotations and returns a new AnnotationCollection
     *
     * Pass true to $useParent if parent class and interface methods should be parsed, and
     * true to $useClass if the declaring class annotations should be parsed.
     *
     * @param string $methodName
     * @param string $className
     * @param bool $useClass
     * @param bool $useParent
     * @return AnnotationCollection
     */
    public function readMethod(string $methodName, string $className, bool $useClass, bool $useParent): AnnotationCollection
    {
        $key = 'annotationreader.'.\str_replace('\\', '', $className).$methodName;
        $result = $this->cache->get($key);
        if ($result !== null) {
            return AnnotationCollection::createFromArray($result);
        }

        $collection = new AnnotationCollection();
        $parentReflectionClass = new ReflectionClass($className);
        if ($parentReflectionClass->hasMethod($methodName)) {
            $reflectionMethod = $parentReflectionClass->getMethod($methodName);
            if ($reflectionMethod->getDeclaringClass()->getName() === $parentReflectionClass->getName()) {
                $annotations = $this->reader->getMethodAnnotations($reflectionMethod);
                $collection->addArray($annotations);
            }
        }

        if (!$useParent && !$useClass) {
            return $collection;
        }

        // add method class annotations without inheritance
        if ($useClass) {
            $collection->addCollection($this->readClass($parentReflectionClass->getName(), false));
        }

        // add overridden method annotations and class annotations if requested
        if ($useParent) {
            $parents = $this->resolveParents($parentReflectionClass);

            foreach ($parents as $parent) {
                $collection->addCollection($this->readMethod($methodName, $parent->getName(), $useClass, $useParent));
            }
        }

        $this->cache->set($key, $collection->toArray());

        return $collection;
    }

    /**
     * Reads property annotations and returns a new AnnotationCollection
     *
     * Pass true to $useParent if parent class and interface methods should be parsed, and
     * true to $useClass if the property class annotations should be parsed.
     *
     * @param string $propertyName
     * @param string $className
     * @param bool $useClass
     * @param bool $useParent
     * @return AnnotationCollection
     */
    public function readProperty(string $propertyName, string $className, bool $useClass, bool $useParent): AnnotationCollection
    {
        $key = 'annotationreader.'.\str_replace('\\', '', $className).$propertyName;
        $result = $this->cache->get($key);
        if ($result !== null) {
            return AnnotationCollection::createFromArray($result);
        }

        $collection = new AnnotationCollection();
        $parentReflectionClass = new ReflectionClass($className);
        if ($parentReflectionClass->hasProperty($propertyName)) {
            $reflectionProperty = $parentReflectionClass->getProperty($propertyName);
            if ($reflectionProperty->getDeclaringClass()->getName() === $parentReflectionClass->getName()) {
                $annotations = $this->reader->getPropertyAnnotations($reflectionProperty);
                $collection->addArray($annotations);
            }
        }

        if (!$useClass && !$useParent) {
            return $collection;
        }

        // add method class annotations without inheritance
        if ($useClass) {
            $collection->addCollection($this->readClass($parentReflectionClass->getName(), false));
        }

        // add overridden method annotations and class annotations if requested
        if ($useParent) {
            $parents = $this->resolveParents($parentReflectionClass);

            foreach ($parents as $parent) {
                $collection->addCollection($this->readProperty($propertyName, $parent->getName(), $useClass, $useParent));
            }
        }

        $this->cache->set($key, $collection->toArray());

        return $collection;
    }

    /**
     * Get parent class and interfaces
     *
     * If $reflectionClass is an interface, return all interfaces, otherwise
     * return the parent class wrapped in an array
     *
     * @param ReflectionClass $reflectionClass
     * @return ReflectionClass[]
     */
    private function resolveParents(ReflectionClass $reflectionClass): array
    {
        if ($reflectionClass->isInterface()) {
            return $reflectionClass->getInterfaces();
        }

        $parentClass = $reflectionClass->getParentClass();

        if ($parentClass === false) {
            return [];
        }

        return [$parentClass->getName() => $parentClass];
    }
}
