<?php

declare(strict_types=1);

use Rector\Php72\Rector\ConstFetch\BarewordStringRector;
use Rector\Php72\Rector\Each\ListEachRector;
use Rector\Php72\Rector\Each\ReplaceEachAssignmentWithKeyCurrentRector;
use Rector\Php72\Rector\Each\WhileEachToForeachRector;
use Rector\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector;
use Rector\Php72\Rector\FuncCall\GetClassOnNullRector;
use Rector\Php72\Rector\FuncCall\IsObjectOnIncompleteClassRector;
use Rector\Php72\Rector\FuncCall\ParseStrWithResultArgumentRector;
use Rector\Php72\Rector\FuncCall\StringifyDefineRector;
use Rector\Php72\Rector\FuncCall\StringsAssertNakedRector;
use Rector\Php72\Rector\Unset_\UnsetCastRector;
use Rector\Renaming\Rector\Function_\RenameFunctionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(WhileEachToForeachRector::class);

    $services->set(ListEachRector::class);

    $services->set(ReplaceEachAssignmentWithKeyCurrentRector::class);

    $services->set(UnsetCastRector::class);

    $services->set(BarewordStringRector::class);

    $services->set(RenameFunctionRector::class)
        ->arg('$oldFunctionToNewFunction', [
            # and imagewbmp
            'jpeg2wbmp' => 'imagecreatefromjpeg',
            # or imagewbmp
            'png2wbmp' => 'imagecreatefrompng',
            #migration72.deprecated.gmp_random-function
            # http://php.net/manual/en/migration72.deprecated.php
            # or gmp_random_range
            'gmp_random' => 'gmp_random_bits',
            'read_exif_data' => 'exif_read_data',
        ]);

    $services->set(GetClassOnNullRector::class);

    $services->set(IsObjectOnIncompleteClassRector::class);

    $services->set(ParseStrWithResultArgumentRector::class);

    $services->set(StringsAssertNakedRector::class);

    $services->set(CreateFunctionToAnonymousFunctionRector::class);

    $services->set(StringifyDefineRector::class);
};
