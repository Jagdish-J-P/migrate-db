<?php

namespace Tests\Providers;

use Illuminate\Support\ServiceProvider;

final class TestServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/../fixtures/migrations'
        );
    }
}
