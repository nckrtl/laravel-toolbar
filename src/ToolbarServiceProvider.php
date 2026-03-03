<?php

namespace NckRtl\Toolbar;

use NckRtl\Toolbar\Console\CustomizeToolbarCommand;
use NckRtl\Toolbar\Console\StartMcpServerCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ToolbarServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-toolbar')
            ->hasConfigFile()
            ->hasRoutes(['toolbar', 'ai'])
            ->publishesServiceProvider('ToolbarConfigProvider')
            ->hasCommands([
                StartMcpServerCommand::class,
                CustomizeToolbarCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        if (! config('toolbar.enabled', true)) {
            Toolbar::$enabled = false;

            return;
        }

        if (! config('toolbar.visible', true)) {
            Toolbar::$visible = false;
        }

        app()->instance(Toolbar::class, new Toolbar);
    }
}
