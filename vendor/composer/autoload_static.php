<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitea10279da4687947679916091928cf85
{
    public static $prefixLengthsPsr4 = array (
        'y' => 
        array (
            'yungouospay\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'yungouospay\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitea10279da4687947679916091928cf85::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitea10279da4687947679916091928cf85::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
