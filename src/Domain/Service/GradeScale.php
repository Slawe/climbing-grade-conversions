<?php

namespace Climb\Grades\Domain\Service;

use Climb\Grades\Domain\Value\DifficultyIndex;
use Climb\Grades\Domain\Value\Grade;
use Climb\Grades\Domain\Value\GradeSystem;

interface GradeScale
{
    /**
     * Identifies which grading system this scale represents.
     * (e.g., FR, UIAA, YDS, NORWAY, etc.).
     *
     * @return GradeSystem
     */
    public function system(): GradeSystem;

    /**
     * Returns the first (primary) index for the given grade using a
     * deterministic default policy (LOWEST). Use toAllIndexes() to get all.
     *
     * @param Grade $grade
     * @return DifficultyIndex
     */
    public function toIndex(Grade $grade): DifficultyIndex;

    /**
     * Same as toIndex() but with an explicit policy (LOWEST/MIDDLE/HIGHEST).
     *
     * @param Grade $grade
     * @param PrimaryIndexPolicy $policy
     * @return DifficultyIndex
     */
    public function toIndexWithPolicy(Grade $grade, PrimaryIndexPolicy $policy): DifficultyIndex;

    /**
     * Returns ALL difficulty indices that correspond to the given grade.
     * Example: a V-grade (e.g., "2") may span several indices.
     *
     * @param Grade $grade
     * @return DifficultyIndex[] Ordered list of indices (ascending).
     */
    public function toAllIndexes(Grade $grade): array;

    /**
     * Converts a DifficultyIndex back into a grade for this scale,
     * returning a canonical single value when the underlying cell
     * contains multiple variants (e.g., "7/7+").
     *
     * @param DifficultyIndex $index
     * @return Grade
     */
    public function fromIndex(DifficultyIndex $index): Grade;

    /**
     * Returns ALL textual grade variants for the given index as defined
     * in the data source (e.g., "7/7+" -> ["7", "7+"]).
     * This is used to expose the full range without collapsing to a single value.
     *
     * @param DifficultyIndex $index
     * @return array
     */
    public function variantsFromIndex(DifficultyIndex $index): array;
}