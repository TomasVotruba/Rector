<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PHPStan\Reflection\ObjectTypeMethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Core\PHPStan\Reflection\CallReflectionResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ClassMethodAssignManipulator
{
    /**
     * @var VariableManipulator
     */
    private $variableManipulator;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var CallReflectionResolver
     */
    private $callReflectionResolver;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        BetterStandardPrinter $betterStandardPrinter,
        CallableNodeTraverser $callableNodeTraverser,
        NodeFactory $nodeFactory,
        NodeNameResolver $nodeNameResolver,
        VariableManipulator $variableManipulator,
        CallReflectionResolver $callReflectionResolver
    ) {
        $this->variableManipulator = $variableManipulator;
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeFactory = $nodeFactory;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->callReflectionResolver = $callReflectionResolver;
    }

    /**
     * @return Assign[]
     */
    public function collectReadyOnlyAssignScalarVariables(ClassMethod $classMethod): array
    {
        $assignsOfScalarOrArrayToVariable = $this->variableManipulator->collectScalarOrArrayAssignsOfVariable(
            $classMethod
        );

        // filter out [$value] = $array, array destructing
        $readOnlyVariableAssigns = $this->filterOutArrayDestructedVariables(
            $assignsOfScalarOrArrayToVariable,
            $classMethod
        );

        $readOnlyVariableAssigns = $this->filterOutReferencedVariables($readOnlyVariableAssigns, $classMethod);
        $readOnlyVariableAssigns = $this->filterOutMultiAssigns($readOnlyVariableAssigns);
        $readOnlyVariableAssigns = $this->filterOutForeachVariables($readOnlyVariableAssigns);

        return $this->variableManipulator->filterOutChangedVariables($readOnlyVariableAssigns, $classMethod);
    }

    public function addParameterAndAssignToMethod(
        ClassMethod $classMethod,
        string $name,
        ?Type $type,
        Assign $assign
    ): void {
        if ($this->hasMethodParameter($classMethod, $name)) {
            return;
        }

        $classMethod->params[] = $this->nodeFactory->createParamFromNameAndType($name, $type);
        $classMethod->stmts[] = new Expression($assign);
    }

    /**
     * @param Assign[] $variableAssigns
     * @return Assign[]
     */
    private function filterOutArrayDestructedVariables(array $variableAssigns, ClassMethod $classMethod): array
    {
        $arrayDestructionCreatedVariables = [];

        $this->callableNodeTraverser->traverseNodesWithCallable($classMethod, function (Node $node) use (
            &$arrayDestructionCreatedVariables
        ) {
            if (! $node instanceof Assign) {
                return null;
            }

            if (! $node->var instanceof Array_ && ! $node->var instanceof List_) {
                return null;
            }

            foreach ($node->var->items as $arrayItem) {
                // empty item
                if ($arrayItem === null) {
                    continue;
                }

                if (! $arrayItem->value instanceof Variable) {
                    continue;
                }

                /** @var string $variableName */
                $variableName = $this->nodeNameResolver->getName($arrayItem->value);
                $arrayDestructionCreatedVariables[] = $variableName;
            }
        });

        return array_filter($variableAssigns, function (Assign $assign) use ($arrayDestructionCreatedVariables): bool {
            return ! $this->nodeNameResolver->isNames($assign->var, $arrayDestructionCreatedVariables);
        });
    }

    /**
     * @param Assign[] $variableAssigns
     * @return Assign[]
     */
    private function filterOutReferencedVariables(array $variableAssigns, ClassMethod $classMethod): array
    {
        $referencedVariables = $this->collectReferenceVariableNames($classMethod);

        return array_filter($variableAssigns, function (Assign $assign) use ($referencedVariables): bool {
            return ! $this->nodeNameResolver->isNames($assign->var, $referencedVariables);
        });
    }

    /**
     * E.g. $a = $b = $c = '...';
     *
     * @param Assign[] $readOnlyVariableAssigns
     * @return Assign[]
     */
    private function filterOutMultiAssigns(array $readOnlyVariableAssigns): array
    {
        return array_filter($readOnlyVariableAssigns, function (Assign $assign): bool {
            $parentNode = $assign->getAttribute(AttributeKey::PARENT_NODE);

            return ! $parentNode instanceof Assign;
        });
    }

    /**
     * @param Assign[] $variableAssigns
     * @return Assign[]
     */
    private function filterOutForeachVariables(array $variableAssigns): array
    {
        foreach ($variableAssigns as $key => $variableAssign) {
            $foreach = $this->findParentForeach($variableAssign);
            if (! $foreach instanceof Foreach_) {
                continue;
            }

            if ($this->betterStandardPrinter->areNodesEqual($foreach->valueVar, $variableAssign->var)) {
                unset($variableAssigns[$key]);
                continue;
            }

            if ($this->betterStandardPrinter->areNodesEqual($foreach->keyVar, $variableAssign->var)) {
                unset($variableAssigns[$key]);
            }
        }

        return $variableAssigns;
    }

    private function hasMethodParameter(ClassMethod $classMethod, string $name): bool
    {
        foreach ($classMethod->params as $constructorParameter) {
            if ($this->nodeNameResolver->isName($constructorParameter->var, $name)) {
                return true;
            }
        }

        return false;
    }

    private function isExplicitlyReferenced(Node $node): bool
    {
        if ($node instanceof Arg || $node instanceof ClosureUse || $node instanceof Param) {
            return $node->byRef;
        }

        return false;
    }

    private function findParentForeach(Assign $assign): ?Foreach_
    {
        /** @var Foreach_|FunctionLike|null $foreach */
        $foreach = $this->betterNodeFinder->findFirstPreviousOfTypes($assign, [Foreach_::class, FunctionLike::class]);
        if (! $foreach instanceof Foreach_) {
            return null;
        }

        return $foreach;
    }

    /**
     * Matches e.g:
     * - array_shift($value)
     * - sort($values)
     */
    private function isFuncCallWithReferencedArgument(Node $node, Variable $variable): bool
    {
        if (! $node instanceof FuncCall) {
            return false;
        }

        if (! $this->nodeNameResolver->isNames($node, ['array_shift', '*sort'])) {
            return false;
        }

        // is 1t argument
        return $node->args[0]->value !== $variable;
    }

    private function isMethodCallWithReferencedArgument(Node $node, Variable $variable): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        $methodReflection = $this->callReflectionResolver->resolveCall($node);
        if (! $methodReflection instanceof ObjectTypeMethodReflection) {
            return false;
        }

        $variableName = $this->nodeNameResolver->getName($variable);
        $parametersAcceptor = $this->callReflectionResolver->resolveParametersAcceptor($methodReflection, $node);
        if ($parametersAcceptor === null) {
            return false;
        }

        /** @var ParameterReflection $parameter */
        foreach ($parametersAcceptor->getParameters() as $parameter) {
            if ($parameter->getName() !== $variableName) {
                continue;
            }

            return $parameter->passedByReference()
                ->yes();
        }

        return false;
    }

    /**
     * @return string[]
     */
    private function collectReferenceVariableNames(ClassMethod $classMethod): array
    {
        $referencedVariables = [];

        $this->callableNodeTraverser->traverseNodesWithCallable($classMethod, function (Node $node) use (
            &$referencedVariables
        ) {
            if (! $node instanceof Variable) {
                return null;
            }

            if ($this->nodeNameResolver->isName($node, 'this')) {
                return null;
            }

            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode !== null && $this->isExplicitlyReferenced($parentNode)) {
                /** @var string $variableName */
                $variableName = $this->nodeNameResolver->getName($node);
                $referencedVariables[] = $variableName;
                return null;
            }

            if ($parentNode instanceof Arg) {
                $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
            }

            if (! $parentNode instanceof Node) {
                return null;
            }

            /** @var string $variableName */
            $variableName = $this->nodeNameResolver->getName($node);

            if ($this->isCallOrConstructorWithReference($parentNode, $node, $variableName)) {
                $referencedVariables[] = $variableName;
            }
        });

        return $referencedVariables;
    }

    private function isParameterReferencedInMethodReflection(New_ $new, string $variableName): bool
    {
        $methodReflection = $this->callReflectionResolver->resolveConstructor($new);

        $parametersAcceptor = $this->callReflectionResolver->resolveParametersAcceptor($methodReflection, $new);
        if ($parametersAcceptor === null) {
            return false;
        }

        /** @var ParameterReflection $parameter */
        foreach ($parametersAcceptor->getParameters() as $parameter) {
            if ($parameter->getName() !== $variableName) {
                continue;
            }

            return $parameter->passedByReference()
                ->yes();
        }

        return false;
    }

    private function isCallOrConstructorWithReference(Node $node, Variable $variable, string $variableName): bool
    {
        if ($this->isMethodCallWithReferencedArgument($node, $variable)) {
            return true;
        }

        if ($this->isFuncCallWithReferencedArgument($node, $variable)) {
            return true;
        }

        if ($this->isConstructorWithReference($node, $variableName)) {
            return true;
        }

        return false;
    }

    private function isConstructorWithReference(Node $node, string $variableName): bool
    {
        if (! $node instanceof New_) {
            return false;
        }

        return $this->isParameterReferencedInMethodReflection($node, $variableName);
    }
}
