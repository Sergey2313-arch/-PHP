<?php

namespace App\Providers;

use App\Models\Ticket;
use App\Policies\TicketPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        Paginator::useBootstrapFive();
        Gate::policy(Ticket::class, TicketPolicy::class);
        RateLimiter::for('login', function (Request $request) {
            return [
                Limit::perMinute(5)->by(mb_strtolower((string) $request->input('email')).'|'.$request->ip()),
                Limit::perMinute(30)->by($request->ip()),
            ];
        });
    }
}
