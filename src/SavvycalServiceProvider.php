<?php

namespace Jeffersongoncalves\Savvycal;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SavvycalServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-savvycal')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations();
    }
}
