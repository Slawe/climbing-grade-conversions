<?php

namespace Climb\Grades\Domain\Service;

use Climb\Grades\Domain\Value\DifficultyIndex;
use Climb\Grades\Domain\Value\Grade;
use Climb\Grades\Domain\Value\GradeSystem;

interface GradeScale
{
    public function system(): GradeSystem;

    public function toIndex(Grade $grade): DifficultyIndex;

    public function fromIndex(DifficultyIndex $index): Grade;
}