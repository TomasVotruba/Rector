<?php

declare(strict_types=1);

namespace Rector\Naming\Guard\PropertyConflictingNameGuard;

use PhpParser\Node\Stmt\ClassLike;
use Rector\Naming\Contract\RenameValueObjectInterface;
use Rector\Naming\ExpectedNameResolver\MatchPropertyTypeExpectedNameResolver;
use Rector\Naming\PhpArray\ArrayFilter;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\NodeNameResolver\NodeNameResolver;

final class MatchPropertyTypeConflictingNameGuard
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ArrayFilter
     */
    private $arrayFilter;

    /**
     * @var MatchPropertyTypeExpectedNameResolver
     */
    private $matchPropertyTypeExpectedNameResolver;

    public function __construct(
        MatchPropertyTypeExpectedNameResolver $matchPropertyTypeExpectedNameResolver,
        NodeNameResolver $nodeNameResolver,
        ArrayFilter $arrayFilter
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->arrayFilter = $arrayFilter;
        $this->matchPropertyTypeExpectedNameResolver = $matchPropertyTypeExpectedNameResolver;
    }

    /**
     * @param PropertyRename $renameValueObject
     */
    public function isConflicting(RenameValueObjectInterface $renameValueObject): bool
    {
        $conflictingPropertyNames = $this->resolve($renameValueObject->getClassLike());
        return in_array($renameValueObject->getExpectedName(), $conflictingPropertyNames, true);
    }

    /**
     * @return string[]
     */
    public function resolve(ClassLike $classLike): array
    {
        $expectedNames = [];
        foreach ($classLike->getProperties() as $property) {
            $expectedName = $this->matchPropertyTypeExpectedNameResolver->resolve($property);
            if ($expectedName === null) {
                // fallback to existing name
                $expectedName = $this->nodeNameResolver->getName($property);
            }

            $expectedNames[] = $expectedName;
        }

        return $this->arrayFilter->filterWithAtLeastTwoOccurences($expectedNames);
    }
}
