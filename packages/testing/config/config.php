<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->load('Rector\Testing\\', __DIR__ . '/../src')
        ->exclude([__DIR__ . '/../src/PHPUnit/Runnable/NodeVisitor', __DIR__ . '/../src/ValueObject']);
};
