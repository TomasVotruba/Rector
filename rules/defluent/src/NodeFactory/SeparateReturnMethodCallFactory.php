<?php

declare(strict_types=1);

namespace Rector\Defluent\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Return_;
use Rector\Defluent\ValueObject\FirstAssignFluentCall;
use Rector\Defluent\ValueObject\FluentMethodCalls;

final class SeparateReturnMethodCallFactory
{
    /**
     * @return Node[]
     */
    public function createReturnFromFirstAssignFluentCallAndFluentMethodCalls(
        FirstAssignFluentCall $firstAssignFluentCall,
        FluentMethodCalls $fluentMethodCalls
    ): array {
        $nodesToAdd = [];

        $nodesToAdd[] = $firstAssignFluentCall->createFirstAssign();

        $decoupledMethodCalls = $this->createNonFluentMethodCalls(
            $fluentMethodCalls->getFluentMethodCalls(),
            $firstAssignFluentCall,
            true
        );

        $nodesToAdd = array_merge($nodesToAdd, $decoupledMethodCalls);

        // return the first value
        $nodesToAdd[] = new Return_($firstAssignFluentCall->getAssignExpr());

        return $nodesToAdd;
    }

    /**
     * @param MethodCall[] $chainMethodCalls
     * @return Assign[]|MethodCall[]
     */
    private function createNonFluentMethodCalls(
        array $chainMethodCalls,
        FirstAssignFluentCall $firstAssignFluentCall,
        bool $isNewNodeNeeded
    ): array {
        $decoupledMethodCalls = [];

        $lastKey = array_key_last($chainMethodCalls);

        foreach ($chainMethodCalls as $key => $chainMethodCall) {
            // skip first, already handled
            if ($key === $lastKey && $firstAssignFluentCall->isFirstCallFactory() && $isNewNodeNeeded) {
                continue;
            }

            $chainMethodCall->var = $this->resolveMethodCallVar($firstAssignFluentCall, $key);
            $decoupledMethodCalls[] = $chainMethodCall;
        }

        return array_reverse($decoupledMethodCalls);
    }

    private function resolveMethodCallVar(FirstAssignFluentCall $firstAssignFluentCall, int $key): Expr
    {
        if (! $firstAssignFluentCall->isFirstCallFactory()) {
            return $firstAssignFluentCall->getCallerExpr();
        }

        // very first call
        if ($key !== 0) {
            return $firstAssignFluentCall->getCallerExpr();
        }

        return $firstAssignFluentCall->getFactoryAssignVariable();
    }
}
