<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitee05d1830576a76d45b8947c45d857b7
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Component\\Console\\Output\\' => 33,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Component\\Console\\Output\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitee05d1830576a76d45b8947c45d857b7::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitee05d1830576a76d45b8947c45d857b7::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitee05d1830576a76d45b8947c45d857b7::$classMap;

        }, null, ClassLoader::class);
    }
}
