<?php

namespace App\Providers;

use App\Services\LoyaltyPointsService;
use App\Services\LoyaltyPointsServiceInterface;
use Illuminate\Support\ServiceProvider;

class LoyaltyPointsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(LoyaltyPointsServiceInterface::class, LoyaltyPointsService::class);
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
