<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Return_\SimplifyUselessVariableRector;
use Rector\DeadCode\Rector\Array_\RemoveDuplicatedArrayKeyRector;
use Rector\DeadCode\Rector\Assign\RemoveAssignOfVoidReturnFunctionRector;
use Rector\DeadCode\Rector\Assign\RemoveDoubleAssignRector;
use Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector;
use Rector\DeadCode\Rector\BinaryOp\RemoveDuplicatedInstanceOfRector;
use Rector\DeadCode\Rector\BooleanAnd\RemoveAndTrueRector;
use Rector\DeadCode\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector;
use Rector\DeadCode\Rector\ClassConst\RemoveUnusedClassConstantRector;
use Rector\DeadCode\Rector\ClassConst\RemoveUnusedPrivateConstantRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveDeadConstructorRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveDeadRecursiveClassMethodRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedParameterRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use Rector\DeadCode\Rector\Concat\RemoveConcatAutocastRector;
use Rector\DeadCode\Rector\Expression\RemoveDeadStmtRector;
use Rector\DeadCode\Rector\Expression\SimplifyMirrorAssignRector;
use Rector\DeadCode\Rector\For_\RemoveDeadIfForeachForRector;
use Rector\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector;
use Rector\DeadCode\Rector\Function_\RemoveUnusedFunctionRector;
use Rector\DeadCode\Rector\FunctionLike\RemoveCodeAfterReturnRector;
use Rector\DeadCode\Rector\FunctionLike\RemoveDeadReturnRector;
use Rector\DeadCode\Rector\FunctionLike\RemoveDuplicatedIfReturnRector;
use Rector\DeadCode\Rector\FunctionLike\RemoveOverriddenValuesRector;
use Rector\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector;
use Rector\DeadCode\Rector\If_\SimplifyIfElseWithSameContentRector;
use Rector\DeadCode\Rector\MethodCall\RemoveDefaultArgumentValueRector;
use Rector\DeadCode\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\DeadCode\Rector\PropertyProperty\RemoveNullPropertyInitializationRector;
use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector;
use Rector\DeadCode\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector;
use Rector\DeadCode\Rector\Ternary\TernaryToBooleanOrFalseToBooleanAndRector;
use Rector\DeadCode\Rector\TryCatch\RemoveDeadTryCatchRector;
use Rector\PHPUnit\Rector\ClassMethod\RemoveEmptyTestMethodRector;
use Rector\PHPUnit\Rector\MethodCall\RemoveEmptyMethodCallRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveDeadStmtRector::class);

    $services->set(RemoveDuplicatedArrayKeyRector::class);

    $services->set(RemoveUnusedForeachKeyRector::class);

    $services->set(RemoveParentCallWithoutParentRector::class);

    $services->set(RemoveEmptyClassMethodRector::class);

    $services->set(RemoveUnusedPrivatePropertyRector::class);

    $services->set(RemoveDoubleAssignRector::class);

    $services->set(RemoveUnusedParameterRector::class);

    $services->set(SimplifyMirrorAssignRector::class);

    $services->set(RemoveOverriddenValuesRector::class);

    $services->set(RemoveUnusedPrivateConstantRector::class);

    $services->set(RemoveUnusedPrivateMethodRector::class);

    $services->set(RemoveCodeAfterReturnRector::class);

    $services->set(RemoveDeadConstructorRector::class);

    $services->set(RemoveDeadReturnRector::class);

    $services->set(RemoveDeadIfForeachForRector::class);

    $services->set(RemoveAndTrueRector::class);

    $services->set(RemoveDefaultArgumentValueRector::class);

    $services->set(RemoveConcatAutocastRector::class);

    $services->set(SimplifyUselessVariableRector::class);

    $services->set(RemoveDelegatingParentCallRector::class);

    $services->set(RemoveDuplicatedInstanceOfRector::class);

    $services->set(RemoveDuplicatedCaseInSwitchRector::class);

    $services->set(RemoveUnusedDoctrineEntityMethodAndPropertyRector::class);

    $services->set(RemoveSetterOnlyPropertyAndMethodCallRector::class);

    $services->set(RemoveNullPropertyInitializationRector::class);

    $services->set(RemoveUnreachableStatementRector::class);

    $services->set(SimplifyIfElseWithSameContentRector::class);

    $services->set(TernaryToBooleanOrFalseToBooleanAndRector::class);

    $services->set(RemoveEmptyTestMethodRector::class);

    $services->set(RemoveDeadTryCatchRector::class);

    $services->set(RemoveUnusedClassConstantRector::class);

    $services->set(RemoveUnusedVariableAssignRector::class);

    $services->set(RemoveDuplicatedIfReturnRector::class);

    $services->set(RemoveUnusedFunctionRector::class);

    $services->set(RemoveUnusedNonEmptyArrayBeforeForeachRector::class);

    $services->set(RemoveAssignOfVoidReturnFunctionRector::class);

    $services->set(RemoveDeadRecursiveClassMethodRector::class);

    $services->set(RemoveEmptyMethodCallRector::class);
};
