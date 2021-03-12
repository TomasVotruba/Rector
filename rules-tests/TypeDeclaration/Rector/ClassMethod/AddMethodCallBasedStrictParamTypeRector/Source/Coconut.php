<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Source;

use Ramsey\Uuid\UuidInterface;

final class Coconut
{
    public function getId(): UuidInterface
    {
    }
}
