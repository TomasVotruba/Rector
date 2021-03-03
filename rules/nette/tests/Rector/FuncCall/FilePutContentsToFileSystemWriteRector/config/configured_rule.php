<?php

declare(strict_types=1);

use Rector\Nette\Rector\FuncCall\FilePutContentsToFileSystemWriteRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(FilePutContentsToFileSystemWriteRector::class);
};
