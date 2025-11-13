<?php

namespace Climb\Grades\Domain\Service;

use Climb\Grades\Domain\Value\Grade;
use Climb\Grades\Domain\Value\GradeSystem;
use RuntimeException;

final class GradeConversionService
{
    /** @var array<string, GradeScale> $scales keyed by GradeSystem value (e.g. 'FR','UIAA',...) */
    private array $scales = [];

    /**
     * GradeConversionService constructor.
     *
     * @param GradeScale ...$scales
     */
    public function __construct(GradeScale ...$scales)
    {
        foreach ($scales as $scale) {
            $this->scales[$scale->system()->value] = $scale;
        }
    }

    /**
     * One meta scale - all variants
     * (range/list) exactly according to the table.
     *
     * @param Grade $grade
     * @param GradeSystem $target
     * @return Grade[]
     */
    public function convert(Grade $grade, GradeSystem $target): array
    {
        $from = GradeSystem::from(strtoupper($grade->system()));
        $fromScale = $this->scales[$from->value] ?? null;
        $toScale   = $this->scales[$target->value] ?? null;

        if (!$fromScale || !$toScale) {
            throw new RuntimeException('Missing scale implementation.');
        }

        $indexes = $fromScale->toAllIndexes($grade);
        $seen = [];
        $out = [];

        foreach ($indexes as $index) {
            // all variants in the target scale on that index (e.g. "7/7+" → ["7","7+"])
            foreach ($toScale->variantsFromIndex($index) as $val) {
                $key = mb_strtolower($val);

                if (isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;

                $out[] = new Grade($val, $target->value);
            }
        }

        return $out;
    }

    /**
     * Convert given grade to all registered systems.
     *
     * @return array<string, Grade> associative: ['UIAA' => Grade(...), 'YDS' => Grade(...), ...]
     */
    public function convertToAll(Grade $grade, bool $includeSource = false): array
    {
        $result = [];
        $from = GradeSystem::from(strtoupper($grade->system()));

        foreach ($this->scales as $system => $scale) {
            if (!$includeSource && $system === $from->value) {
                continue; // skip original system
            }

            $result[$system] = $this->convert($grade, GradeSystem::from($system));
        }

        return $result;
    }
}
