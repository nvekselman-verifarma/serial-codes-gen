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
            ->hasConfigFile('serial-codes-generator');
    }

    public function registeringPackage(): void
    {
        $this->app->singleton(SerialCodesGeneratorContract::class, function () {
            return new SerialCodesGeneratorService(
                config('serial-codes-generator.alphabet'),
                config('serial-codes-generator.default_length'),
            );
        });
    }
}
