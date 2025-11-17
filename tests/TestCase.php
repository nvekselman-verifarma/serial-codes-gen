<?php

namespace Verifarma\SerialCodesGenerator\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Verifarma\SerialCodesGenerator\SerialCodesGeneratorServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            SerialCodesGeneratorServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        $composer = json_decode(
            file_get_contents(__DIR__.'/../composer.json'),
            true
        );

        return $composer['extra']['laravel']['aliases'] ?? [];
    }
}
