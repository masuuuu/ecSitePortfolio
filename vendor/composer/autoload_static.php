<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit383fa4cc90a41cab1c85bd18afb732f5
{
    public static $files = array (
        '320cde22f66dd4f5d3fd621d3e88b98f' => __DIR__ . '/..' . '/symfony/polyfill-ctype/bootstrap.php',
    );

    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'Twig\\' => 5,
        ),
        'S' => 
        array (
            'Symfony\\Polyfill\\Ctype\\' => 23,
            'Stripe\\' => 7,
        ),
        'P' => 
        array (
            'Payjp\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Twig\\' => 
        array (
            0 => __DIR__ . '/..' . '/twig/twig/src',
        ),
        'Symfony\\Polyfill\\Ctype\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-ctype',
        ),
        'Stripe\\' => 
        array (
            0 => __DIR__ . '/..' . '/stripe/stripe-php/lib',
        ),
        'Payjp\\' => 
        array (
            0 => __DIR__ . '/..' . '/payjp/payjp-php/lib',
        ),
    );

    public static $prefixesPsr0 = array (
        'T' => 
        array (
            'Twig_' => 
            array (
                0 => __DIR__ . '/..' . '/twig/twig/lib',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit383fa4cc90a41cab1c85bd18afb732f5::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit383fa4cc90a41cab1c85bd18afb732f5::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit383fa4cc90a41cab1c85bd18afb732f5::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit383fa4cc90a41cab1c85bd18afb732f5::$classMap;

        }, null, ClassLoader::class);
    }
}
