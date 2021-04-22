<?php

declare(strict_types=1);

namespace Rector\DowngradePhp71\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DowngradePhp71\Rector\FuncCall\DowngradeIsIterableRector\DowngradeIsIterableRectorTest
 */
final class DowngradeIsIterableRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change is_iterable with array and Traversable object type check',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($obj)
    {
        is_iterable($obj);
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($obj)
    {
        is_array($obj) || $obj instanceof \Traversable;
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        /** @var mixed $arg */
        $arg = $node->args[0]->value;
        $funcCall = $this->nodeFactory->createFuncCall('is_array', [$arg]);
        $instanceOf = new Instanceof_($arg, new FullyQualified('Traversable'));

        $booleanOr = new BooleanOr($funcCall, $instanceOf);
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        $this->traverseNodesWithCallable($parent, function (Node $n) use ($node, $booleanOr) {
            if ($n === $node) {
                $n = $booleanOr;
                return $n;
            }
        });

        return null;
    }
}
