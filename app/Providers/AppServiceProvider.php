<?php

namespace App\Providers;

use App\Facades\Api\ApiResponse as ApiResponseFacade;
use App\Responses\Api\ApiResponse;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->bind('api-response-accessor', function () {
            return new ApiResponse();
        });
    }
}
