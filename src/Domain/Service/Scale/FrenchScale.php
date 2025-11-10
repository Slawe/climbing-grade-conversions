<?php

namespace Climb\Grades\Domain\Service\Scale;

use Climb\Grades\Domain\Service\AbstractGradeScale;
use Climb\Grades\Domain\Value\GradeSystem;

final class FrenchScale extends AbstractGradeScale
{
    /** @var array<int, string> $indexToGrade */
    protected array $indexToGrade = [
        1 => '1',
        2 => '2',
        3 => '3',
        4 => '4a',
        5 => '4b',
        6 => '4c',
        7 => '5a',
        8 => '5b',
        9 => '5c',
        10 => '5c+',
        11 => '6a',
        12 => '6a+',
        13 => '6b',
        14 => '6b+',
        15 => '6c',
        16 => '6c+',
        17 => '7a',
        18 => '7a+',
        19 => '7b',
        20 => '7b+',
        21 => '7c',
        // ... -> 9c
    ];

    public function system(): GradeSystem
    {
        return GradeSystem::FR;
    }
}