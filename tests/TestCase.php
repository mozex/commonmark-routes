<?php

namespace Mozex\CommonMarkRoutes\Tests;

use Illuminate\Support\Facades\Route;
use Mozex\CommonMarkRoutes\CommonMarkRoutesServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            CommonMarkRoutesServiceProvider::class,
        ];
    }

    protected function defineRoutes($router): void
    {
        Route::get('/', fn () => 'home')
            ->name('home');

        Route::get('about', fn () => 'about')
            ->name('about');

        Route::get('product/{id}', fn (int $id) => "product $id")
            ->name('product');

    }
}
