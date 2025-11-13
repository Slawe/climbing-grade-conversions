<?php

namespace Climb\Grades\Infrastructure\Config;

use Climb\Grades\Domain\Repository\GradeScaleDataRepository;
use Climb\Grades\Domain\Service\GradeScale;
use Climb\Grades\Domain\Service\Scale\AmericanVScale;
use Climb\Grades\Domain\Service\Scale\BrazilianTechnicalScale;
use Climb\Grades\Domain\Service\Scale\EwbankAustralianScale;
use Climb\Grades\Domain\Service\Scale\EwbankSouthAfricaScale;
use Climb\Grades\Domain\Service\Scale\FinlandScale;
use Climb\Grades\Domain\Service\Scale\FrenchFontainebleauScale;
use Climb\Grades\Domain\Service\Scale\FrenchSportScale;
use Climb\Grades\Domain\Service\Scale\NorwayScale;
use Climb\Grades\Domain\Service\Scale\PolishKurtykasScale;
use Climb\Grades\Domain\Service\Scale\SaxonScale;
use Climb\Grades\Domain\Service\Scale\UiaaScale;
use Climb\Grades\Domain\Service\Scale\UkAdjectivalScale;
use Climb\Grades\Domain\Service\Scale\UkTechnicalScale;
use Climb\Grades\Domain\Service\Scale\AmericanYdsScale;

class GradeScaleProvider
{
    /**
     * Registry All scales.
     *
     * @var array<class-string<GradeScale>>
     */
    private const SCALES = [
        UiaaScale::class,
        FrenchSportScale::class,
        AmericanYdsScale::class,
        UkTechnicalScale::class,
        UkAdjectivalScale::class,
        SaxonScale::class,
        EwbankAustralianScale::class,
        EwbankSouthAfricaScale::class,
        FinlandScale::class,
        NorwayScale::class,
        BrazilianTechnicalScale::class,
        PolishKurtykasScale::class,
        AmericanVScale::class,
        FrenchFontainebleauScale::class,
    ];

    /**
     * All scales.
     *
     * @return GradeScale[]
     */
    public static function all(GradeScaleDataRepository $repo): array
    {
        return array_map(fn (string $cls) => new $cls($repo), self::SCALES);
    }
}