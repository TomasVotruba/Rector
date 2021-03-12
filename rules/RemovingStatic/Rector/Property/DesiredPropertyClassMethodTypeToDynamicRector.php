<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use Rector\Core\Configuration\Option;
use Rector\Core\Rector\AbstractRector;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\RemovingStatic\Rector\Property\DesiredPropertyClassMethodTypeToDynamicRector\DesiredPropertyClassMethodTypeToDynamicRectorTest
 */
final class DesiredPropertyClassMethodTypeToDynamicRector extends AbstractRector
{
    /**
     * @var ObjectType[]
     */
    private $staticObjectTypes = [];

    public function __construct(ParameterProvider $parameterProvider)
    {
        $typesToRemoveStaticFrom = $parameterProvider->provideArrayParameter(Option::TYPES_TO_REMOVE_STATIC_FROM);
        foreach ($typesToRemoveStaticFrom as $typeToRemoveStaticFrom) {
            $this->staticObjectTypes[] = new ObjectType($typeToRemoveStaticFrom);
        }
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change defined static properties and methods to dynamic', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public static $name;

    public static function go()
    {
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public $name;

    public function go()
    {
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return class-string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class, ClassMethod::class];
    }

    /**
     * @param Property|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->staticObjectTypes as $staticObjectType) {
            if (! $this->nodeNameResolver->isInClassNamed($node, $staticObjectType)) {
                continue;
            }

            if (! $node->isStatic()) {
                return null;
            }

            $this->visibilityManipulator->makeNonStatic($node);

            return $node;
        }

        return null;
    }
}
