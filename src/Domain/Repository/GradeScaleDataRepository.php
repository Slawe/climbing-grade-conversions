<?php

namespace Climb\Grades\Domain\Repository;

use Climb\Grades\Domain\Value\GradeSystem;

interface GradeScaleDataRepository
{
    /**
     * Returns a map index => grade string for the given system.
     *
     * @param GradeSystem $system
     * @return array<int,string>
     */
    public function indexToGradeMap(GradeSystem $system): array;
}