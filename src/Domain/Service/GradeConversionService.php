<?php

namespace Climb\Grades\Domain\Service;

use Climb\Grades\Domain\Value\Grade;
use Climb\Grades\Domain\Value\GradeSystem;
use RuntimeException;

final class GradeConversionService
{
    /** @var array<string, GradeScale> $scales keyed by GradeSystem value (e.g. 'FR','UIAA',...) */
    private array $scales = [];

    public function __construct(GradeScale ...$scales)
    {
        foreach ($scales as $scale) {
            $this->scales[$scale->system()->value] = $scale;
        }
    }

    public function convert(Grade $grade, GradeSystem $target): Grade
    {
        $from = GradeSystem::from(strtoupper($grade->system()));
        $fromScale = $this->scales[$from->value] ?? null;
        $toScale   = $this->scales[$target->value] ?? null;

        if (!$fromScale || !$toScale) {
            throw new RuntimeException('Missing scale implementation.');
        }

        $index = $fromScale->toIndex($grade);
        return $toScale->fromIndex($index);
    }

    /**
     * Convert given grade to all registered systems.
     *
     * @return array<string, Grade> associative: ['UIAA' => Grade(...), 'YDS' => Grade(...), ...]
     */
    public function convertToAll(Grade $grade, bool $includeSource = false): array
    {
        $from = GradeSystem::from(strtoupper($grade->system()));

        $out = [];
        foreach ($this->scales as $sys => $_scale) {
            $system = GradeSystem::from($sys);

            if (!$includeSource && $system === $from) {
                continue; // skip original system
            }

            // include original value system
            if ($includeSource && $system === $from) {
                $out[$system->value] = $grade;
                continue;
            }

            $out[$system->value] = $this->convert($grade, $system);
        }

        return $out;
    }

    /** (usable sometimes) */
    public function systems(): array
    {
        return array_keys($this->scales);
    }
}
