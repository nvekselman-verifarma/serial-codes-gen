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
}
