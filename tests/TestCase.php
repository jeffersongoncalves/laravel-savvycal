<?php

namespace Jeffersongoncalves\Savvycal\Tests;

use Jeffersongoncalves\Savvycal\SavvycalServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            SavvycalServiceProvider::class,
        ];
    }
}
