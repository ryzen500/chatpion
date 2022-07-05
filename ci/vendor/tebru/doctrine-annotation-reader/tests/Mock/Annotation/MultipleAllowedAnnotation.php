<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\AnnotationReader\Test\Mock\Annotation;

use Tebru\AnnotationReader\AbstractAnnotation;

/**
 * Class MultipleAllowedAnnotation
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @Annotation
 */
class MultipleAllowedAnnotation extends AbstractAnnotation
{
    /**
     * Returns true if multiple annotations of this type are allowed
     *
     * @return bool
     */
    public function allowMultiple(): bool
    {
        return true;
    }
}
