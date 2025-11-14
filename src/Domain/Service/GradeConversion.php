<?php

namespace Climb\Grades\Domain\Service;

use Climb\Grades\Domain\Value\Grade;
use Climb\Grades\Domain\Value\GradeSystem;
use Climb\Grades\Infrastructure\Config\GradeServices;

final class GradeConversion
{
    /**
     * @var GradeConversionService
     */
    private GradeConversionService $service;

    /**
     * @var Grade
     */
    private Grade $grade;

    /**
     * GradeConversion constructor.
     *
     * @param GradeConversionService $service
     * @param Grade $grade
     */
    public function __construct(GradeConversionService $service, Grade $grade)
    {
        $this->service = $service;
        $this->grade = $grade;
    }

    /**
     * Original Grade which will be converted.
     *
     * @param string $value
     * @param string $system
     * @return static
     */
    public static function from(string $value, string $system): self
    {
        // use default wiring; if you want DI, you can add overload with $service
        $service = GradeServices::conversion();
        $grade = new Grade($value, $system);

        return new self($service, $grade);
    }

    /**
     * Return ALL variants (list/range) for the target system..
     *
     * @param GradeSystem $target
     * @return Grade[]
     */
    public function to(GradeSystem $target): array
    {
        return $this->service->convert($this->grade, $target);
    }

    /**
     * Return a ConversionChain, so you can call ->all() or ->single(policy).
     *
     * @param GradeSystem $target
     * @return ConversionChain
     */
    public function towards(GradeSystem $target): ConversionChain
    {
        return new ConversionChain($this->service, $this->grade, $target);
    }

    /**
     * Convert to all registered systems.
     *
     * @param bool $includeSource
     * @return array<string, Grade[]>
     */
    public function toAll(bool $includeSource = false): array
    {
        return $this->service->convertToAll($this->grade, $includeSource);
    }
}