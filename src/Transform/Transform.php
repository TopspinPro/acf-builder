<?php

namespace Tsp\AcfBuilder\Transform;

use Tsp\AcfBuilder\Builder;

/**
 * A Transform that is applied to configuration array of a Builder
 */
abstract class Transform
{
    /**
     * Used to call funtions on the builder.
     * @var \Tsp\AcfBuilder\Builder
     */
    private $builder;

    /**
     * @param Builder $builder
     */
    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @return Builder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * Impelment in all discrete classes
     * @param  array $config input
     * @return array output config
     */
    abstract public function transform($config);
}
