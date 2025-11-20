<?php

namespace NckRtl\Toolbar;

use Spatie\LaravelPackageTools\Package;
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
        app()->singleton(Toolbar::class);
        app()->make(Toolbar::class);

        if(!Toolbar::isEnabled()) {
            return;
        }

        Profiler::initialize();
    }
}
