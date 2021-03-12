<?php

declare(strict_types=1);

namespace Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\Source;

/**
 * @template TEntity as object
 */
abstract class AbstractGenericRepository
{
    /**
     * @return TEntity
     */
    public function find($id)
    {
    }
}
