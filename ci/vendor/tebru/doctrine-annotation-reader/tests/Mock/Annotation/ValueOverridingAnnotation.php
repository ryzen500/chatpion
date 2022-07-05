<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\AnnotationReader\Test\Mock\Annotation;

use Tebru\AnnotationReader\AbstractAnnotation;

/**
 * Class ValueOverriddingAnnotation
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @Annotation
 */
class ValueOverridingAnnotation extends AbstractAnnotation
{
    public function init(): void
    {
        $this->assertKey('foo');

        $this->value = $this->data['foo'];
    }
}
