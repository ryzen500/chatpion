<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\AnnotationReader\Test\Mock;

use Tebru\AnnotationReader\Test\Mock\Annotation as Test;

/**
 * Class ParentClass
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @Test\ParentClassAnnotation("foo")
 */
class ParentClass extends AbstractParentClass
{
    /**
     * @Test\OverriddenPropertyAnnotation("foo")
     */
    private $overriddenParentProperty;

    /**
     * @Test\OverriddenPropertyAnnotation("bar")
     * @Test\OverriddenPropertyOnlyParentAnnotation("foo")
     */
    private $overriddenParentPropertyWithDifferentAnnotationSets;

    /**
     * @Test\MultipleAllowedAnnotation("foo")
     */
    private $overriddenParentWithMultipleAllowedAnnotations;

    /**
     * @Test\ParentPropertyAnnotation("foo")
     */
    private $parentProperty;

    /**
     * @Test\ParentPropertyAnnotation("foo")
     */
    private $inheritPropertyAndClassAnnotations;
    
    /**
     * @Test\OverriddenMethodAnnotation("foo")
     */
    public function overriddenParentMethod() {}

    /**
     * @Test\OverriddenMethodAnnotation("bar")
     * @Test\OverriddenMethodOnlyParentAnnotation("foo")
     */
    public function overriddenParentMethodWithDifferentAnnotationSets() {}

    /**
     * @Test\MultipleAllowedAnnotation("foo")
     */
    public function overriddenParentWithMultipleAllowedAnnotations() {}

    /**
     * @Test\ParentMethodAnnotation("foo")
     */
    public function parentMethod() {}

    /**
     * @Test\ParentMethodAnnotation("foo")
     */
    public function inheritMethodAndClassAnnotations() {}
}
