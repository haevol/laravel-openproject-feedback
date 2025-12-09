<?php

namespace Haevol\OpenProjectFeedback;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class OpenProjectFeedbackServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/openproject-feedback.php',
            'openproject-feedback'
        );

        // Register service as singleton
        $this->app->singleton(Services\OpenProjectService::class, function ($app) {
            return new Services\OpenProjectService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/openproject-feedback.php' => config_path('openproject-feedback.php'),
        ], 'openproject-feedback-config');

        // Publish assets
        $this->publishes([
            __DIR__ . '/../resources/js' => resource_path('js/vendor/openproject-feedback'),
        ], 'openproject-feedback-assets');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/openproject-feedback'),
        ], 'openproject-feedback-views');

        // Load routes
        $this->loadRoutes();

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'openproject-feedback');
        
        // Register Blade component namespace
        $this->loadViewComponentsAs('openproject-feedback', [
            \Haevol\OpenProjectFeedback\View\Components\FeedbackWidget::class,
        ]);
    }

    /**
     * Load package routes.
     */
    protected function loadRoutes(): void
    {
        if (config('openproject-feedback.routes.enabled', true)) {
            Route::group([
                'prefix' => config('openproject-feedback.routes.prefix', 'api'),
                'middleware' => config('openproject-feedback.routes.middleware', ['web', 'auth']),
            ], function () {
                require __DIR__ . '/../routes/web.php';
            });
        }
    }
}

