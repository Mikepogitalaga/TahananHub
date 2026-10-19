<?php

namespace App\Providers;

use App\Models\RentalRequest;
use App\Policies\RentalRequestPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(RentalRequest::class, RentalRequestPolicy::class);
    }
}
