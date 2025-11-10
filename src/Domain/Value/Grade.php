<?php

namespace Climb\Grades\Domain\Value;

use InvalidArgumentException;

final class Grade
{
    private readonly string $value;
    private readonly string $system;

    public function __construct(string $value, string $system)
    {
        $v = trim($value);
        $s = strtoupper(trim($system));

        if ($v === '') {
            throw new InvalidArgumentException('Grade value cannot be empty!');
        }

        if ($s === '') {
            throw new InvalidArgumentException('Grade system cannot be empty!');
        }

        $this->value = $v;
        $this->system = $s;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function system(): string
    {
        return $this->system;
    }

    public function __toString(): string
    {
        return $this->value . ' (' . $this->system . ')';
    }
}