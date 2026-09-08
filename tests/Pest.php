<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\SavvyCal\Tests\TestCase;

uses(TestCase::class)
    ->beforeEach(fn () => Http::preventStrayRequests())
    ->in('Feature');
