<?php

namespace Verifarma\SerialCodesGenerator;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Verifarma\SerialCodesGenerator\Contracts\SerialCodesGeneratorContract;
use Verifarma\SerialCodesGenerator\Services\SerialCodesGeneratorService;

class SerialCodesGeneratorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('serial-codes-generator')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(SerialCodesGeneratorContract::class, function () {
            return new SerialCodesGeneratorService;
        });

        // Facade accessor
        $this->app->singleton('serial-codes-generator', function () {
            return new SerialCodesGeneratorService;
        });
    }
}
