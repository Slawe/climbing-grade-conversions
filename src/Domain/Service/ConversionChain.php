<?php

namespace Climb\Grades\Domain\Service;

use ArrayIterator;
use Climb\Grades\Domain\Value\Grade;
use Climb\Grades\Domain\Value\GradeSystem;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * Chain helper returned by GradeConversion::towards().
 * Lets you choose between .all() (list/range) and .single(policy).
 */
class ConversionChain implements IteratorAggregate, JsonSerializable
{
    /**
     * ConversionChain constructor.
     *
     * @param GradeConversionService $service
     * @param Grade $grade
     * @param GradeSystem $target
     */
    public function __construct
    (
        private readonly GradeConversionService $service,
        private readonly Grade                  $grade,
        private readonly GradeSystem            $target,
    ) {}

    /**
     * Return all variants (list/range) for target system.
     *
     * @return Grade[]
     */
    public function all(): array
    {
        return $this->service->convert($this->grade, $this->target);
    }

    /**
     * Return a SINGLE Grade, using the given primary-index policy.
     *
     * @param PrimaryIndexPolicy $sourcePolicy
     * @param TargetVariantPolicy $targetPolicy
     * @return Grade|null
     */
    public function single(
        PrimaryIndexPolicy $sourcePolicy = PrimaryIndexPolicy::LOWEST,
        TargetVariantPolicy $targetPolicy = TargetVariantPolicy::FIRST
    ): ?Grade {
        return $this->service->convertOne($this->grade, $this->target, $sourcePolicy, $targetPolicy);
    }

    /**
     * Allow foreach directly on the chain (iterates .all()).
     *
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->all());
    }

    /**
     * Allow json_encode to serialize the list.
     *
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->all();
    }
}