<?php

namespace NckRtl\Toolbar;

use NckRtl\Toolbar\Console\CustomizeToolbarCommand;
use NckRtl\Toolbar\Console\StartMcpServerCommand;
use NckRtl\Toolbar\Http\Middleware\WebEnd;
use NckRtl\Toolbar\Http\Middleware\WebStart;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;
use Spatie\LaravelPackageTools\Package;
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
        if (! Toolbar::isEnabled()) {
            return;
        }

        app()->singleton(Toolbar::class);
        app()->make(Toolbar::class);

        $this->app->booted(function () {
            $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
            $router = $this->app->make(\Illuminate\Routing\Router::class);

            // WebStart at the very beginning (global middleware)
            $kernel->prependMiddleware(WebStart::class);

            // Push WebEnd to ALL middleware groups
            // The hasCheckpoint guard ensures it only records once
            foreach (array_keys($router->getMiddlewareGroups()) as $group) {
                $router->pushMiddlewareToGroup($group, WebEnd::class);
            }
        });

        Profiler::initialize();
    }
}
