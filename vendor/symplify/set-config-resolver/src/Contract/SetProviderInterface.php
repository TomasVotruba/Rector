<?php

declare (strict_types=1);
namespace RectorPrefix20210515\Symplify\SetConfigResolver\Contract;

use RectorPrefix20210515\Symplify\SetConfigResolver\ValueObject\Set;
interface SetProviderInterface
{
    /**
     * @return Set[]
     */
    public function provide() : array;
    /**
     * @return string[]
     */
    public function provideSetNames() : array;
    public function provideByName(string $setName) : ?\RectorPrefix20210515\Symplify\SetConfigResolver\ValueObject\Set;
}