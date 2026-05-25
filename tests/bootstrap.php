<?php

declare(strict_types=1);
use Composer\Autoload\ClassLoader;
use Orchestra\Testbench\TestCase;

$candidates = [
    __DIR__.'/../vendor/autoload.php',
    __DIR__.'/../../../../vendor/autoload.php',
];

foreach ($candidates as $path) {
    if (is_file($path)) {
        $loader = require $path;

        if ($loader instanceof ClassLoader) {
            $loader->addPsr4('MortezaAshrafi\\FilamentShieldCaptcha\\Tests\\', __DIR__.'/');
        }

        if (! class_exists(TestCase::class)) {
            fwrite(STDERR, "Composer autoload was loaded but orchestra/testbench was not autoloadable.\n");
            exit(1);
        }

        return;
    }
}

fwrite(STDERR, "Could not locate Composer autoload.php. Tried:\n- ".implode("\n- ", $candidates)."\n");
exit(1);
