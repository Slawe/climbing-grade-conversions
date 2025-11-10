<?php

namespace Climb\Grades\Domain\Service;

use Climb\Grades\Domain\Value\Grade;
use Climb\Grades\Domain\Value\GradeSystem;
use Climb\Grades\Infrastructure\Config\GradeServices;

final class GradeConversion
{
    private GradeConversionService $service;
    private Grade $grade;

    public function __construct(GradeConversionService $service, Grade $grade)
    {
        $this->service = $service;
        $this->grade = $grade;
    }

    public static function from(string $value, string $system): self
    {
        $service = GradeServices::conversion();
        $grade = new Grade($value, $system);

        return new self($service, $grade);
    }

    public function to(GradeSystem $target): Grade
    {
        return $this->service->convert($this->grade, $target);
    }

    public function toAll(bool $includeSource = false): array
    {
        return $this->service->convertToAll($this->grade, $includeSource);
    }
}