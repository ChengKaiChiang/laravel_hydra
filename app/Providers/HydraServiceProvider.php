<?php

namespace App\Providers;

use App\OpenIDConnect\Helpers\HydraConfigHelper;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\ServiceProvider;
use Ory\Hydra\Client\Api\AdminApi;
use Ory\Hydra\Client\Api\PublicApi;
use Ory\Hydra\Client\Configuration;

class HydraServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(HydraConfigHelper::class, function () {
            return new HydraConfigHelper(
                config('hydra.admin_url'),
                config('hydra.remember_for'),
            );
        });

        $this->app->singleton(AdminApi::class, function () {
            $hydraConfigHelper = $this->app->make(HydraConfigHelper::class);
            $config = (new Configuration())->setHost($hydraConfigHelper->adminUrl);

            return new AdminApi(
                $this->app->make(ClientInterface::class),
                $config
            );
        });

        $this->app->singleton(PublicApi::class, function () {
            return tap(new PublicApi(), function (PublicApi $instance) {
                $instance->getConfig()
                    ->setHost(config('hydra.public_url'))
                    ->setUsername(config('hydra.client_id'))
                    ->setPassword(config('hydra.client_secret'))
                    ->setAccessToken(null);
            });
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
