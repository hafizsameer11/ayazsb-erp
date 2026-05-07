<?php

namespace App\Providers;

use App\Models\Permission;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
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
        if (app()->environment('production') || (bool) env('FORCE_HTTPS', false)) {
            URL::forceScheme('https');

            if (filled(config('app.url'))) {
                URL::forceRootUrl((string) config('app.url'));
            }
        }

        Gate::before(static function ($user) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        if (Schema::hasTable('permissions')) {
            Permission::query()->pluck('name')->each(function (string $permission): void {
                Gate::define($permission, static fn ($user) => $user->hasPermission($permission));
            });
        }

        Blade::if('allowed', static function (string $permission): bool {
            $user = auth()->user();

            return $user !== null && $user->hasPermission($permission);
        });
    }
}
