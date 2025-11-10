# Climbing Grade Conversions

Small PHP library for converting rock climbing grades between multiple grading systems.

Currently supported:

- French (FR)
- UIAA
- Yosemite Decimal System (YDS)

The project started as a learning playground for clean architecture / DDD in PHP and evolved into a reusable library.

---

## Installation

Once the package is available on Packagist:

```bash
composer require slawe/climbing-grade-conversions
```

---

## Quick usage

### 1. One-liner conversion API

The simplest way is to use the `GradeConversion` facade:

```php
use Climb\Grades\Domain\Service\GradeConversion;
use Climb\Grades\Domain\Value\GradeSystem;

// Convert French 6c+ to YDS
$yds = GradeConversion::from('6c+', 'FR')->to(GradeSystem::YDS);

echo $yds->value();   // e.g. "5.11b"
```
Convert to all other systems at once:
```php
use Climb\Grades\Domain\Service\GradeConversion;

$all = GradeConversion::from('6c+', 'FR')->toAll(); // FR is excluded by default

foreach ($all as $system => $grade) {
    echo $system . ': ' . $grade->value() . PHP_EOL;
}
```
If you also want the original system included:
```php
$all = GradeConversion::from('6c+', 'FR')->toAll(includeSource: true);
```
The result is an associative array:
```text
[
    'FR'      => Grade(...), // only if includeSource=true
    'UIAA'    => Grade(...),
    'YDS'     => Grade(...),
]
```

### 2. Using the core service directly

Under the hood everything is powered by GradeConversionService and a set of scale implementations.

```php
use Climb\Grades\Domain\Service\GradeConversionService;
use Climb\Grades\Domain\Value\Grade;
use Climb\Grades\Domain\Value\GradeSystem;
use Climb\Grades\Infrastructure\Config\GradeServices;

// Factory that wires all available scales
$service = GradeServices::conversion();

$grade = new Grade('6c+', 'FR');

// Convert FR -> UIAA
$uiaa = $service->convert($grade, GradeSystem::UIAA);

// Convert FR -> all other systems
$all = $service->convertToAll($grade);
```

## CLI usage

The package ships with a simple CLI helper (`bin/grades`).

From the project root:

```bash
php bin/grades 6c+ FR
```
Example output:
```text
Conversions for 6c+ FR:
- UIAA: VIII-
- YDS: 5.11b
```
Convert to a single target system:
```text
php bin/grades 6c+ FR YDS
# 6c+ FR -> 5.11b YDS
```
When installed via Composer in another project, the command is also available as:
```text
vendor/bin/grades 6c+ FR
```

## Design

The library is intentionally small but structured with DDD / hexagonal ideas.

Autoload root:

```text
Climb\Grades\ → src/
```

Main namespaces:

- Climb\Grades\Domain\Value
  - Grade – value object representing a grade in a specific system
  - GradeSystem – enum of supported systems
  - DifficultyIndex – internal normalized difficulty index
- Climb\Grades\Domain\Service
  - GradeScale – interface for a grading scale
  - AbstractGradeScale – base class with common logic
  - GradeConversionService – core service that converts between systems
  - GradeConversion – small facade / fluent API built on top of the service
- Climb\Grades\Domain\Service\Scale
  - FrenchScale
  - UiaaScale
  - YdsScale
  - (more can be added)
- Climb\Grades\Infrastructure\Config
  - GradeScaleRegistry – central list of registered scales
  - GradeServices – factory methods (e.g. conversion())

The public surface of the library is intentionally simple:
in most cases you only need GradeConversion or GradeConversionService.

## Extending with a new grading system

To add a new system:

#### 1. Add the system to `GradeSystem` enum

```php
enum GradeSystem: string
{
    case FR      = 'FR';
    case UIAA    = 'UIAA';
    case YDS     = 'YDS';
    case BRITISH = 'BRITISH';
    case AUSTRALIAN = 'AUSTRALIAN'; // example
}
```

#### 2. Create a new scale class in `Domain/Service/Scale`, extending `AbstractGradeScale`.

Example skeleton:

```php
namespace Climb\Grades\Domain\Service\Scale;

use Climb\Grades\Domain\Service\AbstractGradeScale;
use Climb\Grades\Domain\Value\GradeSystem;

final class AustralianScale extends AbstractGradeScale
{
    /**
     * @var array<int,string>
     */
    protected array $indexToGrade = [
        // 0 => '10', 1 => '11', ...
    ];

    public function system(): GradeSystem
    {
        return GradeSystem::AUSTRALIAN;
    }
}
```

#### 3. Register the scale in `GradeScaleRegistry::all()`:

```php
use Climb\Grades\Domain\Service\Scale\AustralianScale;

final class GradeScaleRegistry
{
    /**
     * @return array<int, \Climb\Grades\Domain\Service\GradeScale>
     */
    public static function all(): array
    {
        return [
            new FrenchScale(),
            new UiaaScale(),
            new YdsScale(),
            new BritishScale(),
            new AustralianScale(), // <-- new one
        ];
    }
}
```

After that, `GradeConversionService::convert()` and `convertToAll()` automatically support the new system. The CLI command and GradeConversion facade will also pick it up.

## Development

Run the test suite:

```text
composer test
```

## License

Released under the [MIT License](https://opensource.org/licenses/MIT).

Copyright © 2025 slawe
