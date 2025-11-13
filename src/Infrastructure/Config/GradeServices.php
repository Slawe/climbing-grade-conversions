<?php

namespace Climb\Grades\Infrastructure\Config;

use Climb\Grades\Domain\Repository\GradeScaleDataRepository;
use Climb\Grades\Domain\Service\GradeConversionService;
use Climb\Grades\Infrastructure\Persistence\Csv\CsvGradeScaleDataRepository;

final class GradeServices
{
    public static function conversion(?GradeScaleDataRepository $repo = null): GradeConversionService
    {
        $repo ??= new CsvGradeScaleDataRepository(GradeConfig::csvPath());
        return new GradeConversionService(...GradeScaleProvider::all($repo));
    }
}