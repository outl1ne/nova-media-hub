<?php

namespace Outl1ne\NovaMediaHub;

use Laravel\Nova\Nova;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Http\Middleware\Authenticate;
use Outl1ne\NovaMediaHub\Http\Middleware\Authorize;
use Outl1ne\NovaMediaHub\MediaHandler\Support\Filesystem;
use Outl1ne\NovaTranslationsLoader\LoadsNovaTranslations;

class MediaHubServiceProvider extends ServiceProvider
{
    use LoadsNovaTranslations;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load package translations
        $this->loadTranslations(__DIR__ . '/../lang', 'nova-media-hub', true);

        if ($this->app->runningInConsole()) {
            // Publish migrations
            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'migrations');

            // Publish config
            $this->publishes([
                __DIR__ . '/../config/' => config_path(),
            ], 'config');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/nova-media-hub.php',
            'nova-media-hub'
        );

        $this->registerRoutes();

        $this->app->bind(Filesystem::class, Filesystem::class);
    }

    protected function registerRoutes()
    {
        // Register nova routes
        Nova::router()->group(function ($router) {
            $path = MediaHub::getBasePath();

            $router
                ->get("/{$path}/{collectionId?}", fn ($collectionId = 'default') => inertia('NovaMediaHub', [
                    'collectionId' => $collectionId,
                ]))
                ->middleware(['nova', Authenticate::class]);
        });

        if ($this->app->routesAreCached()) return;

        Route::middleware(['nova', Authorize::class])
            ->group(__DIR__ . '/../routes/api.php');
    }
}
