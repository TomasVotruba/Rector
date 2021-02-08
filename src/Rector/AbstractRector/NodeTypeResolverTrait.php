<?php

declare(strict_types=1);

namespace Rector\Core\Rector\AbstractRector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait NodeTypeResolverTrait
{
    /**
     * @var TypeUnwrapper
     */
    protected $typeUnwrapper;

    /**
     * @var NodeTypeResolver
     */
    protected $nodeTypeResolver;

    /**
     * @required
     */
    public function autowireNodeTypeResolverTrait(
        NodeTypeResolver $nodeTypeResolver,
        TypeUnwrapper $typeUnwrapper
    ): void {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->typeUnwrapper = $typeUnwrapper;
    }

    /**
     * @param ObjectType|string $type
     */
    protected function isObjectType(Node $node, $type): bool
    {
        return $this->nodeTypeResolver->isObjectType($node, $type);
    }

    /**
     * @param string[]|ObjectType[] $requiredTypes
     */
    protected function isObjectTypes(Node $node, array $requiredTypes): bool
    {
        return $this->nodeTypeResolver->isObjectTypes($node, $requiredTypes);
    }

    /**
     * @param Type[] $desiredTypes
     */
    protected function isSameObjectTypes(ObjectType $objectType, array $desiredTypes): bool
    {
        foreach ($desiredTypes as $abstractClassConstructorParamType) {
            if ($abstractClassConstructorParamType->equals($objectType)) {
                return true;
            }
        }

        return false;
    }

    protected function isNumberType(Node $node): bool
    {
        return $this->nodeTypeResolver->isNumberType($node);
    }

    protected function isStaticType(Node $node, string $staticTypeClass): bool
    {
        return $this->nodeTypeResolver->isStaticType($node, $staticTypeClass);
    }

    protected function getStaticType(Node $node): Type
    {
        return $this->nodeTypeResolver->getStaticType($node);
    }

    protected function isNullableType(Node $node): bool
    {
        return $this->nodeTypeResolver->isNullableType($node);
    }

    protected function getObjectType(Node $node): Type
    {
        return $this->nodeTypeResolver->resolve($node);
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    protected function isMethodStaticCallOrClassMethodObjectType(Node $node, string $type): bool
    {
        if ($node instanceof MethodCall) {
            // method call is variable return
            return $this->isObjectType($node->var, $type);
        }

        if ($node instanceof StaticCall) {
            return $this->isObjectType($node->class, $type);
        }

        if ($node instanceof ClassMethod) {
            $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
            if (! $classLike instanceof Class_) {
                return false;
            }

            return $this->isObjectType($classLike, $type);
        }

        return false;
    }
}
