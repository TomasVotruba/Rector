<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\Comment\CommentsMerger;
use Rector\Core\PhpParser\Node\Manipulator\BinaryOpManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php71\ValueObject\TwoNodeMatch;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\Foreach_\ForeachToInArrayRector\ForeachToInArrayRectorTest
 */
final class ForeachToInArrayRector extends AbstractRector
{
    /**
     * @var BinaryOpManipulator
     */
    private $binaryOpManipulator;

    /**
     * @var CommentsMerger
     */
    private $commentsMerger;

    public function __construct(BinaryOpManipulator $binaryOpManipulator, CommentsMerger $commentsMerger)
    {
        $this->binaryOpManipulator = $binaryOpManipulator;
        $this->commentsMerger = $commentsMerger;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Simplify `foreach` loops into `in_array` when possible',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
foreach ($items as $item) {
    if ($item === 'something') {
        return true;
    }
}

return false;
CODE_SAMPLE
                    ,
                    'in_array("something", $items, true);'
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Foreach_::class];
    }

    /**
     * @param Foreach_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipForeach($node)) {
            return null;
        }

        /** @var If_ $firstNodeInsideForeach */
        $firstNodeInsideForeach = $node->stmts[0];
        if ($this->shouldSkipIf($firstNodeInsideForeach)) {
            return null;
        }

        /** @var Identical|Equal $ifCondition */
        $ifCondition = $firstNodeInsideForeach->cond;
        $foreachValueVar = $node->valueVar;

        $twoNodeMatch = $this->matchNodes($ifCondition, $foreachValueVar);
        if ($twoNodeMatch === null) {
            return null;
        }

        $comparedNode = $twoNodeMatch->getSecondExpr();
        if (! $this->isIfBodyABoolReturnNode($firstNodeInsideForeach)) {
            return null;
        }

        $funcCall = $this->createInArrayFunction($comparedNode, $ifCondition, $node);

        /** @var Return_ $returnToRemove */
        $returnToRemove = $node->getAttribute(AttributeKey::NEXT_NODE);

        /** @var Return_ $return */
        $return = $firstNodeInsideForeach->stmts[0];
        if ($returnToRemove->expr === null) {
            return null;
        }

        if (! $this->isBool($returnToRemove->expr)) {
            return null;
        }

        if ($return->expr === null) {
            return null;
        }

        // cannot be "return true;" + "return true;"
        if ($this->areNodesEqual($return, $returnToRemove)) {
            return null;
        }

        $this->removeNode($returnToRemove);

        $return = $this->createReturn($return->expr, $funcCall);

        $this->commentsMerger->keepChildren($return, $node);

        return $return;
    }

    private function shouldSkipForeach(Foreach_ $foreach): bool
    {
        if ($foreach->keyVar !== null) {
            return true;
        }

        if (count((array) $foreach->stmts) > 1) {
            return true;
        }

        $nextNode = $foreach->getAttribute(AttributeKey::NEXT_NODE);
        if ($nextNode === null || ! $nextNode instanceof Return_) {
            return true;
        }

        $returnExpression = $nextNode->expr;

        if ($returnExpression === null) {
            return true;
        }

        if (! $this->isBool($returnExpression)) {
            return true;
        }

        $foreachValueStaticType = $this->getStaticType($foreach->expr);
        if ($foreachValueStaticType instanceof ObjectType) {
            return true;
        }

        return ! $foreach->stmts[0] instanceof If_;
    }

    private function shouldSkipIf(If_ $if): bool
    {
        $ifCondition = $if->cond;
        if ($ifCondition instanceof Identical) {
            return false;
        }
        return ! $ifCondition instanceof Equal;
    }

    private function matchNodes(BinaryOp $binaryOp, Expr $expr): ?TwoNodeMatch
    {
        return $this->binaryOpManipulator->matchFirstAndSecondConditionNode(
            $binaryOp,
            Variable::class,
            function (Node $node, Node $otherNode) use ($expr): bool {
                return $this->areNodesEqual($otherNode, $expr);
            }
        );
    }

    private function isIfBodyABoolReturnNode(If_ $if): bool
    {
        $ifStatment = $if->stmts[0];
        if (! $ifStatment instanceof Return_) {
            return false;
        }

        if ($ifStatment->expr === null) {
            return false;
        }

        return $this->isBool($ifStatment->expr);
    }

    /**
     * @param Identical|Equal $binaryOp
     */
    private function createInArrayFunction(Node $node, BinaryOp $binaryOp, Foreach_ $foreach): FuncCall
    {
        $arguments = $this->createArgs([$node, $foreach->expr]);

        if ($binaryOp instanceof Identical) {
            $arguments[] = $this->createArg($this->createTrue());
        }

        return $this->createFuncCall('in_array', $arguments);
    }

    private function createReturn(Expr $expr, FuncCall $funcCall): Return_
    {
        $expr = $this->isFalse($expr) ? new BooleanNot($funcCall) : $funcCall;

        return new Return_($expr);
    }
}
