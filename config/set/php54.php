<?php

declare(strict_types=1);

use Rector\Php54\Rector\Break_\RemoveZeroBreakContinueRector;
use Rector\Php54\Rector\FuncCall\RemoveReferenceFromCallRector;
use Rector\Renaming\Rector\Function_\RenameFunctionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameFunctionRector::class)
        ->arg('$oldFunctionToNewFunction', ['mysqli_param_count' => 'mysqli_stmt_param_count']);

    $services->set(RemoveReferenceFromCallRector::class);

    $services->set(RemoveZeroBreakContinueRector::class);
};
