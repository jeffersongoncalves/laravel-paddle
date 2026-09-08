<?php

namespace JeffersonGoncalves\Paddle\Tests;

use JeffersonGoncalves\Paddle\PaddleServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            PaddleServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('paddle.api_key', 'test-api-key');
        $app['config']->set('paddle.base_url', 'https://api.paddle.com');
        $app['config']->set('paddle.webhook_secret', 'test-secret');
    }
}
