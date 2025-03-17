<?php

namespace App\Providers;

use App\Facades\Api\ApiResponse as ApiResponseFacade;
use App\Models\Application;
use App\Policies\Application\ApplicationPolicy;
use App\Responses\Api\ApiResponse;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
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
        $this->_registerPolicies();
        $this->_registerFacades();
        $this->_morphMap();
    }

    /**
     * @return void
     */
    public function _registerFacades(): void
    {
        $this->app->bind('api-response-accessor', function () {
            return new ApiResponse();
        });
    }

    /**
     * @return void
     */
    public function _registerPolicies(): void
    {
        Gate::policy(Application::class, ApplicationPolicy::class);
    }

    /**
     * @return void
     */
    public function _morphMap(): void
    {
        Relation::morphMap([
            'application' => Application::class,
        ]);
    }
}
