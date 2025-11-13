<?php

namespace Climb\Grades\Domain\Service;

use Climb\Grades\Domain\Repository\GradeScaleDataRepository;
use Climb\Grades\Domain\Value\DifficultyIndex;
use Climb\Grades\Domain\Value\Grade;
use Climb\Grades\Domain\Value\GradeSystem;
use InvalidArgumentException;

/**
 * Base class for concrete grade scales.
 *
 * - Loads index → grade map from a GradeScaleDataRepository (CSV, DB, ...)
 * - Supports multiple textual variants in a single cell (e.g. "5a/5a+")
 * - Supports the same grade value appearing on multiple indexes (e.g. V2)
 */
abstract class AbstractGradeScale implements GradeScale
{
    /**
     * Index → full grade value
     * (possibly containing multiple variants separated by "/").
     *
     * @var array<int,string>
     */
    protected array $indexToGrade;

    /**
     * Normalized grade value (lowercase, single variant like "5a") → list of indexes.
     *
     * Example:
     *  "5a" => [10],
     *  "2"  => [10, 11, 12]  // e.g. V2 covering multiple difficulty indexes
     *
     * @var array<string,int[]>
     */
    private array $gradeToIndexes = [];

    /**
     * AbstractGradeScale constructor.
     *
     * @param GradeScaleDataRepository $repo
     */
    public function __construct(GradeScaleDataRepository $repo)
    {
        $this->indexToGrade = $repo->indexToGradeMap($this->system());

        foreach ($this->indexToGrade as $index => $grade) {
            foreach ($this->splitVariants($grade) as $variant) {
                $this->gradeToIndexes[$this->normalized($variant)][] = $index;
            }
        }
    }

    /**
     * Concrete scale must declare which GradeSystem it represents.
     *
     * @return GradeSystem
     */
    abstract public function system(): GradeSystem;

    /**
     * Convert a Grade into a single canonical DifficultyIndex.
     *
     * @param Grade $grade
     * @return DifficultyIndex
     */
    public function toIndex(Grade $grade): DifficultyIndex
    {
        $key = $this->normalized($grade->value());

        if (!isset($this->gradeToIndexes[$key])) {
            throw new InvalidArgumentException("Unknown grade: {$grade->value()}");
        }

        $indexes = $this->gradeToIndexes[$key];

        if (count($indexes) !== 1) {
            throw new InvalidArgumentException(
                "Grade maps to multiple indexes in this scale; use toAllIndexes(): {$grade->value()}"
            );
        }

        return new DifficultyIndex($indexes[0]);
    }

    /**
     * Return all DifficultyIndex values for the given grade,
     * in cases where the same grade covers multiple levels of difficulty.
     *
     * @param Grade $grade
     * @return DifficultyIndex[]
     */
    public function toAllIndexes(Grade $grade): array
    {
        $key = $this->normalized($grade->value());

        if (!isset($this->gradeToIndexes[$key])) {
            throw new InvalidArgumentException("Unknown grade: {$grade->value()}");
        }

        $idx = $this->gradeToIndexes[$key];
        sort($idx);

        return array_map(static fn(int $i) => new DifficultyIndex($i), $idx);
    }

    /**
     * Convert DifficultyIndex back into a Grade in this scale.
     *
     * @param DifficultyIndex $index
     * @return Grade
     */
    public function fromIndex(DifficultyIndex $index): Grade
    {
        $variants = $this->variantsFromIndex($index);

        if ($variants === []) {
            throw new InvalidArgumentException("Index out of range: {$index->value()}");
        }

        return new Grade($variants[0], $this->system()->value);
    }

    /**
     * Return all variants of grades for passed index.
     *
     * @param DifficultyIndex $index
     * @return array|string[]
     */
    public function variantsFromIndex(DifficultyIndex $index): array
    {
        $i = $index->value();

        if (!isset($this->indexToGrade[$i])) {
            return [];
        }

        return $this->splitVariants($this->indexToGrade[$i]);
    }

    /**
     * Split variants.
     *
     * @param string $grades
     * @return string[]
     */
    private function splitVariants(string $grades): array
    {
        $parts = array_map('trim', explode('/', $grades));
        return array_values(array_filter($parts, static fn($s) => $s !== ''));
    }

    /**
     * Normalized value.
     *
     * @param string $v
     * @return string
     */
    private function normalized(string $v): string
    {
        return mb_strtolower(trim($v));
    }
}