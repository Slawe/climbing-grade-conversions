<?php

declare(strict_types=1);

namespace Climb\Tests;

use Climb\Grades\Domain\Value\Grade;
use Climb\Grades\Domain\Value\GradeSystem;
use Climb\Grades\Infrastructure\Config\GradeServices;
use PHPUnit\Framework\TestCase;

class GradeConversionServiceTest extends TestCase
{
    public function test_converts_french_to_uiaa_and_yds(): void
    {
        $service = GradeServices::conversion();

        $fr = new Grade('6c+', 'fr');

        $uiaa = $service->convert($fr, GradeSystem::UIAA);
        $yds  = $service->convert($fr, GradeSystem::YDS);

        self::assertSame('VIII-', $uiaa->value());
        self::assertSame('5.11b', $yds->value());
    }

    public function test_convert_to_all_excludes_source_system_by_default(): void
    {
        $service = GradeServices::conversion();

        $fr = new Grade('6c+', 'FR');

        $all = $service->convertToAll($fr);

        // expected UIAA i YDS keys
        self::assertArrayHasKey('UIAA', $all);
        self::assertArrayHasKey('YDS', $all);

        // FR should not appear by default
        self::assertArrayNotHasKey('FR', $all);

        // example of asserting expected values
        self::assertSame('VIII-', $all['UIAA']->value());
        self::assertSame('5.11b', $all['YDS']->value());
    }

    public function test_convert_to_all_can_include_source_when_requested(): void
    {
        $service = GradeServices::conversion();

        $fr = new Grade('6c+', 'FR');

        $all = $service->convertToAll($fr, includeSource: true);

        self::assertArrayHasKey('FR', $all);
        self::assertSame('6c+', $all['FR']->value());
    }
}