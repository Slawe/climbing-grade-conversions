<?php

namespace Climb\Grades\Infrastructure\Config;

use Climb\Grades\Domain\Service\AbstractGradeScale;
use Climb\Grades\Domain\Service\Scale\FrenchScale;
use Climb\Grades\Domain\Service\Scale\UiaaScale;
use Climb\Grades\Domain\Service\Scale\YdsScale;

class GradeScaleRegistry
{
    /**
     * All scales.
     *
     * @return AbstractGradeScale[]
     */
    public static function all(): array
    {
        return [
            new UiaaScale(),
            new FrenchScale(),
            new YdsScale(),
        ];
    }
}