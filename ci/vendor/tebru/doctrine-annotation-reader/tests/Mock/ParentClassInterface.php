<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\AnnotationReader\Test\Mock;

use Tebru\AnnotationReader\Test\Mock\Annotation as Test;

/**
 * Interface ParentClassInterface
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @Test\ParentClassAnnotation("foo")
 */
interface ParentClassInterface
{
    /**
     * @Test\OverriddenMethodAnnotation("foo")
     */
    public function overriddenParentMethod();

    /**
     * @Test\OverriddenMethodAnnotation("bar")
     * @Test\OverriddenMethodOnlyParentAnnotation("foo")
     */
    public function overriddenParentMethodWithDifferentAnnotationSets();

    /**
     * @Test\MultipleAllowedAnnotation("foo")
     */
    public function overriddenParentWithMultipleAllowedAnnotations();

    /**
     * @Test\ParentMethodAnnotation("foo")
     */
    public function parentMethod();

    /**
     * @Test\ParentMethodAnnotation("foo")
     */
    public function inheritMethodAndClassAnnotations();
}
