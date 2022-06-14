<?php

namespace Outl1ne\NovaMediaLibrary;

use Laravel\Nova\Nova;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Http\Middleware\Authenticate;
use Outl1ne\NovaMediaLibrary\Http\Middleware\Authorize;
use Outl1ne\NovaTranslationsLoader\LoadsNovaTranslations;

class NMLServiceProvider extends ServiceProvider
{
    use LoadsNovaTranslations;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslations(__DIR__ . '/../lang', 'nova-medialibrary', true);

        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__ . '/../config/' => config_path(),
            ], 'config');
        }
    }

    public function register()
    {
        $this->registerRoutes();

        $this->mergeConfigFrom(
            __DIR__ . '/../config/nova-medialibrary.php',
            'nova-medialibrary'
        );
    }

    protected function registerRoutes()
    {
        // Register nova routes
        Nova::router()->group(function ($router) {
            $path = MediaLibrary::getBasePath();

            $router
                ->get("{$path}/{pageId?}", fn ($pageId = 'general') => inertia('NovaMediaLibrary', ['basePath' => $path, 'pageId' => $pageId]))
                ->middleware(['nova', Authenticate::class]);
        });

        if ($this->app->routesAreCached()) return;

        Route::middleware(['nova', Authorize::class])
            ->group(__DIR__ . '/../routes/api.php');
    }
}
