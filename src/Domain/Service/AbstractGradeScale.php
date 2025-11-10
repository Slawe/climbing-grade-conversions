<?php

namespace Climb\Grades\Domain\Service;

use Climb\Grades\Domain\Value\DifficultyIndex;
use Climb\Grades\Domain\Value\Grade;
use Climb\Grades\Domain\Value\GradeSystem;
use InvalidArgumentException;

abstract class AbstractGradeScale implements GradeScale
{
    /** @var array<int,string> */
    protected array $indexToGrade = [];

    /** @var array<string,int> */
    private array $gradeToIndex;

    public function __construct()
    {
        $this->gradeToIndex = array_flip($this->indexToGrade);
    }

    abstract public function system(): GradeSystem;

    public function toIndex(Grade $grade): DifficultyIndex
    {
        $key = strtolower($grade->value());

        if (!isset($this->gradeToIndex[$key])) {
            throw new InvalidArgumentException(sprintf(
                'Unknown %s grade: %s',
                $this->system()->value,
                $grade->value()
            ));
        }

        return new DifficultyIndex($this->gradeToIndex[$key]);
    }

    public function fromIndex(DifficultyIndex $index): Grade
    {
        $i = $index->value();

        if (!isset($this->indexToGrade[$i])) {
            throw new InvalidArgumentException(sprintf(
                'No %s grade for index %d',
                $this->system()->value,
                $i
            ));
        }

        return new Grade($this->indexToGrade[$i], $this->system()->value);
    }
}