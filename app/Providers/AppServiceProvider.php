<?php

namespace App\Providers;

use App\Models\ParentProfile;
use App\Policies\ParentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(
            ParentProfile::class,
            ParentPolicy::class
        );
    }
}