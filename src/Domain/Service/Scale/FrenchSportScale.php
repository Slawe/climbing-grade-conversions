<?php

namespace Climb\Grades\Domain\Service\Scale;

use Climb\Grades\Domain\Service\AbstractGradeScale;
use Climb\Grades\Domain\Value\GradeSystem;

final class FrenchSportScale extends AbstractGradeScale
{
    public function system(): GradeSystem
    {
        return GradeSystem::FR;
    }
}