<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\AnnotationReader\Test\Mock;

use Tebru\AnnotationReader\Test\Mock\Annotation as Test;

/**
 * Interface BaseClassInterface
 *
 * @author Nate Brunette <n@tebru.net>
 * 
 * @Test\BaseClassAnnotation("foo")
 * @Test\OverriddenClassAnnotation("bar")
 */
interface BaseClassInterface extends ParentClassInterface, AbstractParentClassInterface
{
    /**
     * @Test\MultipleAllowedAnnotation("foo")
     * @Test\MultipleAllowedAnnotation("bar")
     */
    public function multipleAnnotationOfSingleType();

    /**
     * @Test\OverriddenMethodAnnotation("bar")
     */
    public function overriddenParentMethod();

    /**
     * @Test\OverriddenMethodAnnotation("bar")
     */
    public function overriddenAbstractParentMethod();
    
    /**
     * @Test\OverriddenMethodAnnotation("bar")
     * @Test\OverriddenMethodOnlyBaseAnnotation("foo")
     */
    public function overriddenParentMethodWithDifferentAnnotationSets();
    
    /**
     * @Test\OverriddenMethodAnnotation("bar")
     * @Test\OverriddenMethodOnlyBaseAnnotation("foo")
     */
    public function overriddenAbstractParentMethodWithDifferentAnnotationSets();

    /**
     * @Test\MultipleAllowedAnnotation("bar")
     */
    public function overriddenParentWithMultipleAllowedAnnotations();
    
    /**
     * @Test\MultipleAllowedAnnotation("bar")
     */
    public function overriddenAbstractParentWithMultipleAllowedAnnotations();

    /**
     * @Test\BaseClassAnnotation("bar")
     */
    public function overriddenDeclaringClass();

    /**
     * @Test\ParentClassAnnotation("bar")
     */
    public function overriddenParentClass();

    /**
     * @Test\AbstractParentClassAnnotation("bar")
     */
    public function overriddenAbstractParentClass();

    public function inheritMethodAndClassAnnotations();
}
