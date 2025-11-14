# Climbing Grade Conversions

Small PHP library for converting rock climbing grades between multiple grading systems.
It started as a DDD / clean-architecture learning playground and evolved into a reusable package.

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
- ..._(You can add more by implementing a scale class and registering it.)_

Exact enum names/values are defined in [`Climb\Grades\Domain\Value\GradeSystem`](src/Domain/Value/GradeSystem.php).

Grades are normalized into an internal difficulty index.
A single table row may contain multiple textual variants (e.g. 7c/8a) and some systems may map the same textual grade to multiple indices (e.g. certain UIAA duplicates).
The library keeps all those variants, no collapsing to “first”/“median” unless you explicitly ask for it.

---

## Installation

```bash
composer require slawe/climbing-grade-conversions
```
or
```text
git clone git@github.com:slawe/climbing-grade-conversions.git
cd climbing-grade-conversions
composer install
```

> The package ships with a CSV-backed repository by default.
> Configure the CSV path via `Climb\Grades\Infrastructure\Config\GradeConfig::csvPath()`.

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
// or without source
$allWithoutSource = GradeConversion::from('6c+', 'fr')->toAll();
// $allSystems is an associative array: system string => list of Grade objects
// ['UIAA' => [Grade("VIII-")], 'YDS' => [Grade("5.11b")], 'BR' => [...], ...]
```
> When includeSource is true, the source system is returned exactly as entered (a single Grade), not as a range.

### 2. Chain helper

Prefer fluent chaining? Use towards() which returns a small chain object with `.all()` and `.single(...)`.

```php
use Climb\Grades\Domain\Service\GradeConversion;
use Climb\Grades\Domain\Service\PrimaryIndexPolicy;
use Climb\Grades\Domain\Service\TargetVariantPolicy;
use Climb\Grades\Domain\Value\GradeSystem;

// All variants (same as ->to())
$all = GradeConversion::from('7a', 'FR')
    ->towards(GradeSystem::BR)
    ->all();                 // → [Grade("7c","BR"), Grade("8a","BR")]

// A single result with policies:
//  - source index policy (LOWEST / MIDDLE / HIGHEST)
//  - target variant policy (FIRST / MIDDLE / LAST)
$one = GradeConversion::from('7a', 'FR')
    ->towards(GradeSystem::BR)
    ->single(
        PrimaryIndexPolicy::LOWEST,     // which source index to use if the source spans multiple indices
        TargetVariantPolicy::LAST       // which target variant to pick if the cell has "7c/8a"
    );                                  // → Grade("8a","BR")

```

### 3. Using the core service directly

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
---

## CLI usage

A small helper lives at `bin/grades`.


Positional:
- `<gradeValue>` - e.g. `6c+`, `7a`
- `<gradeSystem>` - e.g. `FR`, `UIAA`, `YDS`
- `[targetSystem]` - if omitted, converts to **all** other systems (range)

Options:
- `--single` (or `-1`) - return **one** result (instead of a list)
- `--source-policy=lowest|middle|highest` - how to pick the **source** index if the grade spans multiple (default: `lowest`)
- `--target-policy=first|middle|last` - which **target** variant to pick if the cell has multiple values (default: `first`)
- `--include-source` - when converting to **all** systems, include the source too
- `--help` - show help

Examples:
```bash
php bin/grades 6c+ fr
php bin/grades 6c+ fr yds
php bin/grades 7a fr br --single --target-policy=last
php bin/grades 6c+ fr --include-source
```
When installed via Composer in another project, the command is also available as:
```text
vendor/bin/grades 6c+ fr
```
---

## Data source & ranges

* The service consumes an **index → grade** map per system (column), by default from a CSV file.
* A cell may contain **multiple textual variants** (e.g. 7/7+). The API returns **all** of them in order.
* A source grade can **span multiple indices** (e.g. V-grade “2” mapping to several rows).
The converter gathers **all target variants** across those indices (deduplicated, ascending index order).

This design guarantees the output mirrors the table exactly - no guessing or collapsing.

---

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
---

## Design & architecture

The library is intentionally small but structured with DDD / hexagonal ideas.

Autoload root:

```text
Climb\Grades\ → src/
```

Key namespaces:

- **Climb\Grades\Domain\Value**
  - `Grade` - value object representing a grade in a specific system
  - `GradeSystem` - enum of supported systems
  - `DifficultyIndex` - internal normalized difficulty index
- **Climb\Grades\Domain\Service**
  - `GradeScale` - interface for a grading scale
  - `AbstractGradeScale` - base class with common logic for parsing/ranges/normalization
  - `GradeConversionService` - core service that converts between systems
  - `GradeConversion` - small facade / fluent API built on top of the service
  - `ConversionChain` - helper returned by `towards()` (methods `all()` / `single(...)`)
  - `PrimaryIndexPolicy` - **LOWEST / MIDDLE / HIGHEST** (source index selection)
  - `TargetVariantPolicy` - **FIRST / MIDDLE / LAST** (target variant selection)
- **Domain\Exception**
  - `GradeNotFound` - textual grade not present in a scale
  - `IndexOutOfRange` - difficulty index doesn’t exist in a scale
  - `AmbiguousGrade` - (optional usage) indicates multiple indices when a unique one was expected
  - `InvalidScaleData` - scale repository returned invalid/ill-formed data
- **Climb\Grades\Domain\Service\Scale**
  - `AmericanVScale`
  - `AmericanYdsScale`
  - `BrazilianTechnicalScale`
  - `EwbankAustralianScale`
  - `EwbankSouthAfricaScale`
  - `FinlandScale`
  - `FrenchFontainebleauScale`
  - `FrenchSportScale`
  - `NorwayScale`
  - `PolishKurtykasScale`
  - `SaxonScale`
  - `UiaaScale`
  - `UkAdjectivalScale`
  - `UkTechnicalScale`
- **Climb\Grades\Infrastructure\Config**
  - `GradeConfig` - where the CSV path is configured
  - `GradeScaleProvider` - central list of registered scales
  - `GradeServices` - convenience factory (wires repository + scales)
- **Climb\Grades\Infrastructure\Persistence\Csv**
  - `CsvGradeScaleDataRepository` - reads the CSV into index → value maps

The public surface of the library is intentionally simple:
in most cases you only need GradeConversion or GradeConversionService.

### How conversion works

1. **Source grade** (text + system) is normalized → **all matching difficulty indices** are resolved in the source scale (some grades span multiple indices).
2. For each difficulty index, the **target scale** returns **all textual variants** at that index.
3. Variants are **deduplicated**; you get a **list** (range).

   For a **single** result, call `.single(sourcePolicy, targetPolicy)` and choose:
   - which source index to use (`PrimaryIndexPolicy`)
   - and which target variant to pick on that index (`TargetVariantPolicy`)

### Canonical (primary) index policy

* `AbstractGradeScale::toIndex()` returns a single **canonical** index (default policy: **LOWEST**).
* Use `toIndexWithPolicy($grade, PrimaryIndexPolicy::LOWEST|MIDDLE|HIGHEST)` for explicit control.
* Use `toAllIndexes($grade)` to retrieve **all** indices.

---

## Extending with a new grading system

1. **Add a case** to `GradeSystem` enum.
2. **Create a scale class** extending `AbstractGradeScale` and implement `system(): GradeSystem`; its data will come from the repository’s column for that system.
3. **Register** the scale in your composition root (`GradeServices` or your own factory).

No other changes are needed: `GradeConversionService` will pick it up automatically.

---

## Configuration (CSV)

By default, data comes from a CSV:

* One column per system (e.g. `FR`, `UIAA`, `YDS`, `BR`, …)
* One row per **difficulty index** (1..N)
* A cell can contain **one or multiple** textual variants separated by `/`, e.g.:
  ```text
  FR, UIAA, BR
  7a, VIII, 7c/8a
  ```
* Configure the CSV file path via:
  ```php
  use Climb\Grades\Infrastructure\Config\GradeConfig;

  GradeConfig::setCsvPath(__DIR__ . '/../data/grades.csv'); // example
  ```

You can swap the repository for MySQL/Mongo/etc. by implementing
`Climb\Grades\Domain\Repository\GradeScaleDataRepository`.

---

## Errors & exceptions

* `GradeNotFound` — thrown when a textual grade is not known by a scale
* `IndexOutOfRange` — thrown when a difficulty index does not exist in a scale
* `AmbiguousGrade` — available for situations where a unique index was required but multiple were found (useful if you introduce an API surface that must be unique)
* `InvalidScaleData` — thrown if repository returns ill-formed data

> The public facade (GradeConversion) and the service are designed to protect you from most hard failures by favoring list/range results.
> Exceptions primarily guard illegal/invalid inputs or corrupted data.

---

## API Summary

* `GradeConversion::from($text, $system)` → create a facade for one grade
  * `->to(GradeSystem $target): Grade[]` - list/range (BC behavior)
  * `->towards(GradeSystem $target): ConversionChain`
    * `->all(): Grade[]`
    * `->single(PrimaryIndexPolicy $source = LOWEST, TargetVariantPolicy $target = FIRST): ?Grade`
  * `->toAll(bool $includeSource = false): array<string, Grade[]>`
* `PrimaryIndexPolicy` - `LOWEST | MIDDLE | HIGHEST`
* `TargetVariantPolicy` - `FIRST | MIDDLE | LAST`

---

## Development

Run the test suite:

```text
composer test
```
Run CLI locally:
```text
php bin/grades 6c+ FR
php bin/grades 6c+ FR YDS
```

---

## Version notes
* **v0.2.x** - GradeConversion::to() and toAll() (and service counterparts)
return lists to reflect true ranges from the source table. Update code/tests that expected a single value.
---

## License

Released under the [MIT License](https://opensource.org/licenses/MIT).

Copyright © 2025 [slawe](https://github.com/slawe)
