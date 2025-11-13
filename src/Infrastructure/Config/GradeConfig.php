<?php

namespace Climb\Grades\Infrastructure\Config;

final class GradeConfig
{
    /**
     * Default CSV path used by the library.
     * You can change this in your own wiring if needed.
     *
     * @return string
     */
    public static function csvPath(): string
    {
        return dirname(__DIR__, 3) . '/data/grades.csv';
    }
}