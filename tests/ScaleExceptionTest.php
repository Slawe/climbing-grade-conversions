<?php
declare(strict_types=1);

namespace Climb\Tests;

use Climb\Grades\Domain\Exception\GradeNotFound;
use Climb\Grades\Domain\Exception\IndexOutOfRange;
use Climb\Grades\Domain\Repository\GradeScaleDataRepository;
use Climb\Grades\Domain\Service\Scale\FrenchSportScale;
use Climb\Grades\Domain\Value\DifficultyIndex;
use Climb\Grades\Domain\Value\Grade;
use Climb\Grades\Infrastructure\Config\GradeConfig;
use Climb\Grades\Infrastructure\Persistence\Csv\CsvGradeScaleDataRepository;
use PHPUnit\Framework\TestCase;

final class ScaleExceptionTest extends TestCase
{
    private GradeScaleDataRepository $repo;

    protected function setUp(): void
    {
        $this->repo = new CsvGradeScaleDataRepository(GradeConfig::csvPath());
    }

    public function test_grade_not_found_is_thrown_for_unknown_textual_grade(): void
    {
        $scale = new FrenchSportScale($this->repo);

        $this->expectException(GradeNotFound::class);
        $scale->toAllIndexes(new Grade('Z9', 'FR'));
    }

    public function test_index_out_of_range_is_thrown_for_invalid_index(): void
    {
        $scale = new FrenchSportScale($this->repo);

        $this->expectException(IndexOutOfRange::class);
        $scale->fromIndex(new DifficultyIndex(999999)); // out of range
    }
}
