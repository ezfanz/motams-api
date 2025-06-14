<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response('Too many requests. Please try again later.', 429, $headers);
                });
        });
    
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Too many authentication attempts. Please wait a moment before trying again.',
                        'error_code' => 'AUTH_RATE_LIMIT'
                    ], 429, $headers);
                });
        });

        // Automatically apply collation fix to 'lateinoutview' queries
        DB::macro('lateinoutviewFix', function ($query) {
            return DB::select(
                str_replace('staffid', 
                "CAST(staffid AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_general_ci", 
                $query)
            );
        });
        
    }
}
