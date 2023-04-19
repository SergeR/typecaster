<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite1648389ac64def968d95912c94bc7fe
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'SergeR\\Typecaster\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'SergeR\\Typecaster\\' => 
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
            $loader->prefixLengthsPsr4 = ComposerStaticInite1648389ac64def968d95912c94bc7fe::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite1648389ac64def968d95912c94bc7fe::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInite1648389ac64def968d95912c94bc7fe::$classMap;

        }, null, ClassLoader::class);
    }
}