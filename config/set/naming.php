<?php

declare(strict_types=1);

use Rector\Naming\Rector\Assign\RenameVariableToMatchGetMethodNameRector;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenamePropertyToMatchTypeRector::class);

    $services->set(RenameVariableToMatchNewTypeRector::class);

    $services->set(RenameVariableToMatchGetMethodNameRector::class);
};
