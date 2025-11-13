<?php

namespace Climb\Grades\Domain\Service\Scale;

use Climb\Grades\Domain\Service\AbstractGradeScale;
use Climb\Grades\Domain\Value\GradeSystem;

class EwbankAustralianScale extends AbstractGradeScale
{
    public function system(): GradeSystem
    {
        return GradeSystem::AU;
    }
}