<?php

namespace NckRtl\Toolbar;

use Spatie\LaravelPackageTools\Package;
use NckRtl\Toolbar\Http\Middleware\WebEnd;
use NckRtl\Toolbar\Http\Middleware\WebStart;
use NckRtl\Toolbar\Console\StartMcpServerCommand;
use NckRtl\Toolbar\Console\CustomizeToolbarCommand;
use NckRtl\Toolbar\Providers\ToolbarConfigProvider;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ToolbarServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class LaravelToolbarServiceProvider a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-toolbar')
            ->hasRoutes(['toolbar', 'ai'])
            ->publishesServiceProvider('ToolbarConfigProvider')
            ->hasCommands([
                StartMcpServerCommand::class,
                CustomizeToolbarCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        if(!Toolbar::isEnabled()) {
            return;
        }

        app()->singleton(Toolbar::class);
        app()->make(Toolbar::class);



          $this->app->booted(function () {
            $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
            $router = $this->app->make(\Illuminate\Routing\Router::class);

            // WebStart at the very beginning
            $kernel->prependMiddleware(WebStart::class);

            try {
                $request = $this->app->make('request');
                $route = $router->getRoutes()->match($request);

                // Get middleware groups in execution order
                $activeGroups = collect($route->gatherMiddleware())
                    ->filter(fn($m) => is_string($m) && array_key_exists($m, $router->getMiddlewareGroups()))
                    ->unique()
                    ->values();

                // Add WebEnd only to the LAST group
                if ($lastGroup = $activeGroups->last()) {
                    $router->pushMiddlewareToGroup($lastGroup, WebEnd::class);
                }

            } catch (\Throwable $e) {

            }
        });

        Profiler::initialize();
    }
}
