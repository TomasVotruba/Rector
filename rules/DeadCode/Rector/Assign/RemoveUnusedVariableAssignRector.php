<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\RemoveUnusedVariableAssignRectorTest
 */
final class RemoveUnusedVariableAssignRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unused assigns to variables', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $value = 5;
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        $classMethod = $node->getAttribute(AttributeKey::METHOD_NODE);
        if (! $classMethod instanceof FunctionLike) {
            return null;
        }

        if (! $node->var instanceof Variable) {
            return null;
        }

        // variable is used
        if ($this->isUsed($node)) {
            return null;
        }

        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof Expression) {
            return null;
        }

        $this->removeNode($node);
        return $node;
    }

    /**
     * @return bool
     */
    private function isUsed(Assign $assign): bool
    {
        $variable = $assign->var;
        $isUsedPrev = (bool) $this->betterNodeFinder->findFirstPreviousOfNode($assign, function (Node $node) use ($variable): bool {
            return $this->isUsedIn($node, $variable);
        });

        if ($isUsedPrev) {
            return true;
        }

        return (bool) $this->betterNodeFinder->findFirstNext($assign, function (Node $node) use ($variable): bool {
            return $this->isUsedIn($node, $variable);
        });
    }

    private function isUsedIn(Node $node, Variable $variable): bool
    {
        if (! $node instanceof Variable) {
            return false;
        }

        return $this->isName($variable, (string) $this->getName($node));
    }
}
