<?php

namespace Climb\Grades\Domain\Service\Scale;

use Climb\Grades\Domain\Service\AbstractGradeScale;
use Climb\Grades\Domain\Value\GradeSystem;

class UiaaScale extends AbstractGradeScale
{
    /** @var array<int, string> $indexToGrade */
    protected array $indexToGrade = [
        1 => 'I',
        2 => 'II',
        3 => 'III',
        4 => 'IV',
        5 => 'IV+',
        6 => 'V',
        7 => 'V+',
        8 => 'VI-',
        9 => 'VI',
        10 => 'VI',
        11 => 'VI+',
        12 => 'VII-',
        13 => 'VII',
        14 => 'VII+',
        15 => 'VIII-',
        16 => 'VIII-',
        17 => 'VIII',
        18 => 'VIII+',
        19 => 'VIII+/IX-',
        20 => 'IX-',
        21 => 'IX',
        // ... -> XII+
    ];

    public function system(): GradeSystem
    {
        return GradeSystem::UIAA;
    }
}