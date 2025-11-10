<?php

namespace Climb\Grades\Domain\Service\Scale;

use Climb\Grades\Domain\Service\AbstractGradeScale;
use Climb\Grades\Domain\Value\GradeSystem;

class YdsScale extends AbstractGradeScale
{
    /** @var array<int, string> $indexToGrade */
    protected array $indexToGrade = [
        1 => '5.1',
        2 => '5.2',
        3 => '5.3',
        4 => '5.4',
        5 => '5.5',
        6 => '5.6',
        7 => '5.7',
        8 => '5.8',
        9 => '5.9',
        10 => '5.10a',
        11 => '5.10a',
        12 => '5.10b',
        13 => '5.10c',
        14 => '5.10d',
        15 => '5.11a',
        16 => '5.11b',
        17 => '5.11d',
        18 => '5.12a',
        19 => '5.12b',
        20 => '5.12c',
        21 => '5.12d',
        // ... -> 515d
    ];

    public function system(): GradeSystem
    {
        return GradeSystem::YDS;
    }
}