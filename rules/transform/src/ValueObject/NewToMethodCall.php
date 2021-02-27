<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;

final class NewToMethodCall
{
    /**
     * @var string
     */
    private $newType;

    /**
     * @var string
     */
    private $serviceType;

    /**
     * @var string
     */
    private $serviceMethod;

    public function __construct(string $newType, string $serviceType, string $serviceMethod)
    {
        $this->newType = $newType;
        $this->serviceType = $serviceType;
        $this->serviceMethod = $serviceMethod;
    }

    public function getNewObjectType(): ObjectType
    {
        return new ObjectType($this->newType);
    }

    public function getServiceObjectType(): ObjectType
    {
        return new ObjectType($this->serviceType);
    }

    public function getServiceMethod(): string
    {
        return $this->serviceMethod;
    }
}
