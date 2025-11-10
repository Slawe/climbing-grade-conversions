<?php

namespace Climb\Grades\Domain\Value;

use InvalidArgumentException;

final class DifficultyIndex
{
    public function __construct(private readonly int $value)
    {
        if ($value < 1) {
            throw new InvalidArgumentException('DifficultyIndex must be positive integer above zero!');
        }
    }

    public function value(): int
    {
        return $this->value;
    }
}