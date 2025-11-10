<?php

namespace Climb\Grades\Infrastructure\Config;

use Climb\Grades\Domain\Service\GradeConversionService;

final class GradeServices
{
    public static function conversion(): GradeConversionService
    {
        return new GradeConversionService(...GradeScaleRegistry::all());
    }
}