<?php

namespace JeffersonGoncalves\SavvyCal;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SavvyCalServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-savvycal')
            ->hasConfigFile();
    }
}
