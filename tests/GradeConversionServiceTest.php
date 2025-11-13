<?php

declare(strict_types=1);

namespace Climb\Tests;

use Climb\Grades\Domain\Value\Grade;
use Climb\Grades\Domain\Value\GradeSystem;
use Climb\Grades\Infrastructure\Config\GradeServices;
use PHPUnit\Framework\TestCase;

class GradeConversionServiceTest extends TestCase
{
    public function test_converts_french_to_uiaa_and_yds_as_lists(): void
    {
        $service = GradeServices::conversion();

        $fr = new Grade('6c+', 'FR');

        $uiaa = $service->convert($fr, GradeSystem::UIAA); // Grade[]
        $yds  = $service->convert($fr, GradeSystem::YDS);  // Grade[]

        // basic shape
        self::assertIsArray($uiaa);
        self::assertIsArray($yds);
        self::assertNotEmpty($uiaa);
        self::assertNotEmpty($yds);

        // expected values
        self::assertSame('VIII-', $uiaa[0]->value());
        self::assertSame('5.11b', $yds[0]->value());
    }

    public function test_convert_to_all_excludes_source_system_by_default_and_each_entry_is_a_list(): void
    {
        $service = GradeServices::conversion();

        $fr = new Grade('6c+', 'FR');

        $all = $service->convertToAll($fr); // array<string, Grade[]>

        // keys exist
        self::assertArrayHasKey('UIAA', $all);
        self::assertArrayHasKey('YDS', $all);
        self::assertArrayNotHasKey('FR', $all); // source excluded by default

        // each entry is a list of Grade objects
        self::assertIsArray($all['UIAA']);
        self::assertIsArray($all['YDS']);
        self::assertNotEmpty($all['UIAA']);
        self::assertNotEmpty($all['YDS']);

        // example exact checks
        self::assertSame('VIII-', $all['UIAA'][0]->value());
        self::assertSame('5.11b', $all['YDS'][0]->value());
    }

    public function test_convert_to_all_can_include_source_when_requested(): void
    {
        $service = GradeServices::conversion();

        $fr = new Grade('7a', 'FR');

        $all = $service->convertToAll($fr, includeSource: true); // array<string, Grade[]>

        self::assertArrayHasKey('FR', $all);
        self::assertIsArray($all['FR']);
        self::assertNotEmpty($all['FR']);
        self::assertSame('7a', $all['FR'][0]->value());
    }

    public function test_range_example_returns_multiple_variants_when_defined(): void
    {
        $service = GradeServices::conversion();

        $fr = new Grade('6c+', 'FR');
        $nor = $service->convert($fr, GradeSystem::NO); // Grade[]

        $values = array_map(fn($g) => $g->value(), $nor);
        self::assertEquals(['7', '7+'], $values);
    }
}