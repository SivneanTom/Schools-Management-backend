<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\ParentProfile;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    // Because the route parameter name and model class name are different.
    // Because the route parameter name and model class name are different.

    //     Route model binding
    // =
    // convert URL ID into Model automatically

    public function boot(): void
    {
        Route::model(
            'parent',
            ParentProfile::class
        );
    }
}
