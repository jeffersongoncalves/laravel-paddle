<?php

namespace JeffersonGoncalves\Paddle;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PaddleServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('paddle')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(Paddle::class, function () {
            return new Paddle(
                (string) config('paddle.api_key'),
                (string) config('paddle.base_url', 'https://api.paddle.com'),
                (int) config('paddle.default_per_page', 50),
                config('paddle.webhook_secret'),
            );
        });
    }
}
