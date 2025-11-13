# Climbing Grade Conversions

Small PHP library for converting rock climbing grades between multiple grading systems.

Currently supported (out of the box):

- French (sport) - (FR)
- UIAA  - UIAA
- Yosemite Decimal System - (YDS)
- British Technical - (UK_tech)
- British Adjectival - (UK_adj)
- German/Swiss Saxon scale - (SAXON)
- Ewbank Australian - (AU)
- Ewbank South African - (SA)
- Scandinavian Finland - (FIN)
- Scandinavian Norway - (NO)
- Brazilian Technical - (BR)
- Polish Cracow/Kurtyka - (PO)
- Boulder American Verm/Hueco V-Grade - (V)
- Boulder Fontainebleau Scale - (FONT)

Exact enum names/values are defined in [`Climb\Grades\Domain\Value\GradeSystem`](src/Domain/Value/GradeSystem.php).

The project started as a learning playground for clean architecture / DDD in PHP and evolved into a reusable library.

---

## Installation

```bash
composer require slawe/climbing-grade-conversions
```

---

## Quick usage

### 1) One-liner conversion API

Use the `GradeConversion` facade for a fluent, single-line experience.

> **IMPORTANT**
> Conversions return **lists** (ranges) of `Grade` objects to reflect the source table exactly.
> If a cell contains multiple variants (e.g., `7/7+`) or a source grade spans several indices (e.g., `V2`), you get **all** variants. No collapsing to “first/median”.

```php
use Climb\Grades\Domain\Service\GradeConversion;
use Climb\Grades\Domain\Value\GradeSystem;

// FR 6c+ → YDS (returns a LIST)
$yds = GradeConversion::from('6c+', 'fr')->to(GradeSystem::YDS);
// e.g. ["5.11b"]

// FR 6c+ → Norway (returns all variants from the table)
$norway = GradeConversion::from('6c+', 'fr')->to(GradeSystem::NORWAY);
// e.g. ["7", "7+"]

// FR 6c+ → all systems (each key holds a LIST)
$all = GradeConversion::from('6c+', 'fr')->toAll(); // FR excluded by default
// e.g. ['UIAA' => ["VIII-"], 'YDS' => ["5.11b"], 'NORWAY' => ["7","7+"], ...]

// Include source system as well:
$allWithSource = GradeConversion::from('6c+', 'fr')->toAll(includeSource: true);
```

### 2. Using the core service directly

Under the hood everything is powered by GradeConversionService and a set of scale implementations.

```php
use Climb\Grades\Domain\Service\GradeConversionService;
use Climb\Grades\Domain\Value\Grade;
use Climb\Grades\Domain\Value\GradeSystem;
use Climb\Grades\Infrastructure\Config\GradeServices;

// CSV by default (see “Wiring” below)
$service = GradeServices::conversion();

$grade = new Grade('6c+', 'fr');

// One target (LIST of Grade variants)
$uiaa = $service->convert($grade, GradeSystem::UIAA);

// All targets (each entry is a LIST of Grade variants)
$all = $service->convertToAll($grade);
```

## CLI usage

A small helper lives at `bin/grades`.

From the project root:

```bash
php bin/grades 6c+ fr
```
Example output:
```text
Conversions for 6c+ fr:
- UIAA: VIII-
- YDS: 5.11b
- NORWAY: 7, 7+
...
```
Convert to a single target system:
```text
php bin/grades 6c+ fr yds
6c+ FR -> YDS:
- 5.11b
```
Include the source system:
```text
php bin/grades 6c+ FR --include-source
```
When installed via Composer in another project, the command is also available as:
```text
vendor/bin/grades 6c+ fr
```

## Data source & ranges

* The service consumes an **index → grade** map per system (column), by default from a CSV file.
* A cell may contain **multiple textual variants** (e.g. 7/7+). The API returns **all** of them in order.
* A source grade can **span multiple indices** (e.g. V-grade “2” mapping to several rows).
The converter gathers **all target variants** across those indices (deduplicated, ascending index order).

This design guarantees the output mirrors the table exactly—no guessing or collapsing.

## Wiring (storage-agnostic)

The library is storage-agnostic. Scales are wired via a provider/registry; you can pass any repository that implements `GradeScaleDataRepository`.

* Default wiring: CSV
```php
use Climb\Grades\Infrastructure\Config\GradeServices;

$service = GradeServices::conversion(); // uses CsvGradeScaleDataRepository
```
* Custom wiring: JSON / DB / whatever
```php
$repo    = new MySqlGradeScaleDataRepository($pdo);   // your implementation
$service = GradeServices::conversion($repo); 
```

## Design

The library is intentionally small but structured with DDD / hexagonal ideas.

Autoload root:

```text
Climb\Grades\ → src/
```

Key namespaces:

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
  - AmericanVScale
  - AmericanYdsScale
  - BrazilianTechnicalScale
  - EwbankAustralianScale
  - EwbankSouthAfricaScale
  - FinlandScale
  - FrenchFontainebleauScale
  - FrenchSportScale
  - NorwayScale
  - PolishKurtykasScale
  - SaxonScale
  - UiaaScale
  - UkAdjectivalScale
  - UkTechnicalScale
- Climb\Grades\Infrastructure\Config
  - GradeConfig - current defined path to CSV file
  - GradeScaleProvider – central list of registered scales
  - GradeServices – factory methods (e.g. conversion())
- Climb\Grades\Infrastructure\Persistence\Csv
  - CsvGradeScaleDataRepository - fetch data from CSV

The public surface of the library is intentionally simple:
in most cases you only need GradeConversion or GradeConversionService.

## Extending with a new grading system

1. Add a case to `GradeSystem` enum.
2. Create a scale class extending `AbstractGradeScale` and implement `system(): GradeSystem`.
3. Register the class in the provider so it’s constructed with the repository.
The CLI and services will pick it up automatically.

## Development

Run the test suite:

```text
composer test
```

## Version notes
* **v0.2.x** - GradeConversion::to() and toAll() (and service counterparts)
return lists to reflect true ranges from the source table. Update code/tests that expected a single value.

## License

Released under the [MIT License](https://opensource.org/licenses/MIT).

Copyright © 2025 slawe
