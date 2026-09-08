<?php

namespace JeffersonGoncalves\SavvyCal\Tests;

use JeffersonGoncalves\SavvyCal\SavvyCalServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            SavvyCalServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('savvycal.token', 'fake-token');
        $app['config']->set('savvycal.timeout', 5);
    }
}
