<?php

namespace LicenseGuard\Client;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use LicenseGuard\Client\Console\CheckCommand;
use LicenseGuard\Client\Http\Middleware\CheckLicense;

class LicenseGuardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Config fusionnée sous la clé "license" (dispo sans publication).
        $this->mergeConfigFrom(__DIR__ . '/../config/license.php', 'license');

        $this->app->singleton(LicenseChecker::class);
    }

    public function boot(Router $router): void
    {
        // Vue de blocage sous le namespace "license::".
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'license');

        // Alias de middleware : 'license' (à appliquer aux routes à protéger).
        $router->aliasMiddleware('license', CheckLicense::class);

        if ($this->app->runningInConsole()) {
            // Publication OPTIONNELLE (personnalisation) :
            $this->publishes([
                __DIR__ . '/../config/license.php' => config_path('license.php'),
            ], 'license-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/license'),
            ], 'license-views');

            $this->commands([CheckCommand::class]);
        }
    }
}
