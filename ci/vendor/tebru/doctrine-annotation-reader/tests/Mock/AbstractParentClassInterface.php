<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\AnnotationReader\Test\Mock;

use Tebru\AnnotationReader\Test\Mock\Annotation as Test;

/**
 * Class DoubleParentClass
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @Test\AbstractParentClassAnnotation("foo")
 * @Test\OverriddenClassAnnotation("foo")
 */
interface AbstractParentClassInterface
{
    /**
     * @Test\OverriddenMethodAnnotation("foo")
     */
    public function overriddenAbstractParentMethod();

    /**
     * @Test\OverriddenMethodAnnotation("bar")
     * @Test\OverriddenMethodOnlyParentAnnotation("foo")
     */
    public function overriddenAbstractParentMethodWithDifferentAnnotationSets();

    /**
     * @Test\MultipleAllowedAnnotation("foo")
     */
    public function overriddenAbstractParentWithMultipleAllowedAnnotations();

    /**
     * @Test\AbstractParentMethodAnnotation("foo")
     */
    public function abstractParentMethod();

    /**
     * @Test\AbstractParentMethodAnnotation("foo")
     */
    public function inheritMethodAndClassAnnotations();
}
