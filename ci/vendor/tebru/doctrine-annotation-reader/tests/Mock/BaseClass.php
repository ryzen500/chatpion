<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\AnnotationReader\Test\Mock;

use Tebru\AnnotationReader\Test\Mock\Annotation as Test;

/**
 * Class BaseClass
 *
 * @author Nate Brunette <n@tebru.net>
 * 
 * @Test\BaseClassAnnotation("foo")
 * @Test\OverriddenClassAnnotation("bar")
 */
class BaseClass extends ParentClass
{
    /**
     * @Test\MultipleAllowedAnnotation("foo")
     * @Test\MultipleAllowedAnnotation("bar")
     */
    private $multipleAnnotationOfSingleType;

    /**
     * @Test\OverriddenPropertyAnnotation("bar")
     */
    private $overriddenParentProperty;

    /**
     * @Test\OverriddenPropertyAnnotation("bar")
     */
    private $overriddenAbstractParentProperty;

    /**
     * @Test\OverriddenPropertyAnnotation("bar")
     * @Test\OverriddenPropertyOnlyBaseAnnotation("foo")
     */
    private $overriddenParentPropertyWithDifferentAnnotationSets;

    /**
     * @Test\OverriddenPropertyAnnotation("bar")
     * @Test\OverriddenPropertyOnlyBaseAnnotation("foo")
     */
    private $overriddenAbstractParentPropertyWithDifferentAnnotationSets;

    /**
     * @Test\MultipleAllowedAnnotation("bar")
     */
    private $overriddenParentWithMultipleAllowedAnnotations;

    /**
     * @Test\MultipleAllowedAnnotation("bar")
     */
    private $overriddenAbstractParentWithMultipleAllowedAnnotations;

    /**
     * @Test\BaseClassAnnotation("bar")
     */
    private $overriddenDeclaringClass;

    /**
     * @Test\ParentClassAnnotation("bar")
     */
    private $overriddenParentClass;

    /**
     * @Test\AbstractParentClassAnnotation("bar")
     */
    private $overriddenAbstractParentClass;

    private $inheritPropertyAndClassAnnotations;
    
    /**
     * @Test\MultipleAllowedAnnotation("foo")
     * @Test\MultipleAllowedAnnotation("bar")
     */
    public function multipleAnnotationOfSingleType() {}

    /**
     * @Test\OverriddenMethodAnnotation("bar")
     */
    public function overriddenParentMethod() {}

    /**
     * @Test\OverriddenMethodAnnotation("bar")
     */
    public function overriddenAbstractParentMethod() {}
    
    /**
     * @Test\OverriddenMethodAnnotation("bar")
     * @Test\OverriddenMethodOnlyBaseAnnotation("foo")
     */
    public function overriddenParentMethodWithDifferentAnnotationSets() {}
    
    /**
     * @Test\OverriddenMethodAnnotation("bar")
     * @Test\OverriddenMethodOnlyBaseAnnotation("foo")
     */
    public function overriddenAbstractParentMethodWithDifferentAnnotationSets() {}

    /**
     * @Test\MultipleAllowedAnnotation("bar")
     */
    public function overriddenParentWithMultipleAllowedAnnotations() {}
    
    /**
     * @Test\MultipleAllowedAnnotation("bar")
     */
    public function overriddenAbstractParentWithMultipleAllowedAnnotations() {}

    /**
     * @Test\BaseClassAnnotation("bar")
     */
    public function overriddenDeclaringClass() {}

    /**
     * @Test\ParentClassAnnotation("bar")
     */
    public function overriddenParentClass() {}

    /**
     * @Test\AbstractParentClassAnnotation("bar")
     */
    public function overriddenAbstractParentClass() {}

    public function inheritMethodAndClassAnnotations() {}
}
