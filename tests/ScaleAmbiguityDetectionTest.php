<?php
declare(strict_types=1);

namespace Climb\Tests;

use Climb\Grades\Domain\Repository\GradeScaleDataRepository;
use Climb\Grades\Domain\Service\PrimaryIndexPolicy;
use Climb\Grades\Domain\Service\Scale\AmericanYdsScale;
use Climb\Grades\Domain\Service\Scale\FrenchSportScale;
use Climb\Grades\Domain\Service\Scale\UiaaScale;
use Climb\Grades\Domain\Value\DifficultyIndex;
use Climb\Grades\Domain\Value\Grade;
use Climb\Grades\Domain\Value\GradeSystem;
use Climb\Grades\Infrastructure\Config\GradeConfig;
use Climb\Grades\Infrastructure\Persistence\Csv\CsvGradeScaleDataRepository;
use Normalizer;
use PHPUnit\Framework\TestCase;

final class ScaleAmbiguityDetectionTest extends TestCase
{
    private GradeScaleDataRepository $repo;

    protected function setUp(): void
    {
        $this->repo = new CsvGradeScaleDataRepository(GradeConfig::csvPath());
    }

    public function test_ambiguous_variant_respects_policies_on_first_scale_that_has_duplicates(): void
    {
        // LIST of tuples [GradeSystem, ScaleClass] — without enum as key!
        $candidates = [
            [GradeSystem::UIAA, UiaaScale::class],
            [GradeSystem::YDS,  AmericanYdsScale::class],
            [GradeSystem::FR,   FrenchSportScale::class],
            // add more scales as desired that often have duplicates...
        ];

        $found = null; // [GradeSystem $system, string $scaleClass, string $variant, int[] $indices]
        foreach ($candidates as [$system, $scaleClass]) {
            [$variant, $idx] = $this->findAmbiguousVariantWithIndices($system);
            if ($variant !== null) {
                $found = [$system, $scaleClass, $variant, $idx];
                break;
            }
        }

        if ($found === null) {
            $this->markTestSkipped(
                'No duplicate textual grades found in configured systems. ' .
                'Add a duplicate value in CSV to cover ambiguity policies.'
            );
        }

        /** @var array{0:GradeSystem,1:class-string,2:string,3:int[]} $found */
        [$system, $scaleClass, $variant, $idxList] = $found;

        /** @var object $scale */
        $scale = new $scaleClass($this->repo);

        // toAllIndexes return all
        $all = $scale->toAllIndexes(new Grade($variant, $system->value));
        $this->assertSame($idxList, array_map(static fn(DifficultyIndex $d) => $d->value(), $all));

        // policy
        $lowest  = $scale->toIndexWithPolicy(new Grade($variant, $system->value), PrimaryIndexPolicy::LOWEST);
        $middle  = $scale->toIndexWithPolicy(new Grade($variant, $system->value), PrimaryIndexPolicy::MIDDLE);
        $highest = $scale->toIndexWithPolicy(new Grade($variant, $system->value), PrimaryIndexPolicy::HIGHEST);

        $this->assertSame($idxList[0], $lowest->value());
        $this->assertSame($idxList[(int) floor((count($idxList) - 1) / 2)], $middle->value());
        $this->assertSame($idxList[array_key_last($idxList)], $highest->value());
    }

    /** @return array{0:?string,1:array<int,int>} */
    private function findAmbiguousVariantWithIndices(GradeSystem $system): array
    {
        $map = $this->repo->indexToGradeMap($system);
        $acc = []; // norm → [idx...]

        foreach ($map as $i => $cell) {
            foreach ($this->splitCell($cell) as $variant) {
                $k = $this->norm($variant);
                $acc[$k] ??= [];
                if (!in_array($i, $acc[$k], true)) {
                    $acc[$k][] = $i;
                }
            }
        }

        foreach ($acc as $variant => $idx) {
            if (count($idx) > 1) {
                sort($idx);
                return [$variant, $idx];
            }
        }
        return [null, []];
    }

    private function splitCell(string $cell): array
    {
        $parts = array_map('trim', explode('/', $cell));
        return array_values(array_filter($parts, static fn($s) => $s !== ''));
    }

    private function norm(string $v): string
    {
        $v = trim($v);
        if (class_exists(Normalizer::class)) {
            $v = Normalizer::normalize($v, Normalizer::FORM_C);
        }
        return mb_strtolower($v);
    }
}
