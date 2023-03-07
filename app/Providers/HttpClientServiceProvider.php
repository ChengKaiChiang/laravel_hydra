<?php

namespace App\Providers;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class HttpClientServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function provides()
    {
        return [
            // Guzzle
            GuzzleClient::class,
            GuzzleClientInterface::class,
        ];
    }

    public function register()
    {
        $this->registerGuzzleClient();
    }

    private function registerGuzzleClient(): void
    {
        $this->app->singleton(GuzzleClient::class, function () {
            return new GuzzleClient([]);
        });

        $this->app->alias(GuzzleClient::class, GuzzleClientInterface::class);
    }
}
