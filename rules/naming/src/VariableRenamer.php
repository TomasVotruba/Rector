<?php

declare(strict_types=1);

namespace Rector\Naming;

use Closure;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Naming\PhpDoc\VarTagValueNodeRenamer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class VariableRenamer
{
    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var VarTagValueNodeRenamer
     */
    private $varTagValueNodeRenamer;

    public function __construct(
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        NodeNameResolver $nodeNameResolver,
        VarTagValueNodeRenamer $varTagValueNodeRenamer
    ) {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->varTagValueNodeRenamer = $varTagValueNodeRenamer;
    }

    /**
     * @param ClassMethod|Function_|Closure $functionLike
     */
    public function renameVariableInFunctionLike(
        FunctionLike $functionLike,
        ?Assign $assign = null,
        string $oldName,
        string $expectedName
    ): void {
        $isRenamingActive = false;

        if ($assign === null) {
            $isRenamingActive = true;
        }

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable(
            (array) $functionLike->stmts,
            function (Node $node) use ($oldName, $expectedName, $assign, &$isRenamingActive): ?Variable {
                if ($assign !== null && $node === $assign) {
                    $isRenamingActive = true;
                    return null;
                }

                if (! $node instanceof Variable) {
                    return null;
                }

                // skip param names
                $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
                if ($parent instanceof Param) {
                    return null;
                }

                // TODO: Remove in next PR (with above param check?),
                // TODO: Should be implemented in BreakingVariableRenameGuard::shouldSkipParam()
                if ($this->isParamInParentFunction($node)) {
                    return null;
                }

                if (! $isRenamingActive) {
                    return null;
                }

                return $this->renameVariableIfMatchesName($node, $oldName, $expectedName);
            }
        );
    }

    private function isParamInParentFunction(Variable $variable): bool
    {
        $closure = $variable->getAttribute(AttributeKey::CLOSURE_NODE);
        if (! $closure instanceof Closure) {
            return false;
        }

        $variableName = $this->nodeNameResolver->getName($variable);
        if ($variableName === null) {
            return false;
        }

        foreach ($closure->params as $param) {
            if ($this->nodeNameResolver->isName($param, $variableName)) {
                return true;
            }
        }

        return false;
    }

    private function renameVariableIfMatchesName(Variable $variable, string $oldName, string $expectedName): ?Variable
    {
        if (! $this->nodeNameResolver->isName($variable, $oldName)) {
            return null;
        }

        $variable->name = $expectedName;
        $this->varTagValueNodeRenamer->renameAssignVarTagVariableName($variable, $oldName, $expectedName);

        return $variable;
    }
}
