<?php

namespace NckRtl\Toolbar;

use Illuminate\Cookie\Middleware\EncryptCookies;
use Inertia\Ssr\Gateway;
use NckRtl\Toolbar\Console\CustomizeToolbarCommand;
use NckRtl\Toolbar\Console\StartMcpServerCommand;
use NckRtl\Toolbar\Services\ProfilerService\ProfiledSsrGateway;
use NckRtl\Toolbar\Support\RedirectChainStore;
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
        EncryptCookies::except(RedirectChainStore::COOKIE_NAME);

        if (! config('toolbar.enabled', true)) {
            Toolbar::$enabled = false;

            return;
        }

        if (! config('toolbar.visible', true)) {
            Toolbar::$visible = false;
        }

        app()->instance(Toolbar::class, new Toolbar);

        $this->decorateInertiaSsrGateway();
    }

    /**
     * If the host app uses Inertia, wrap its SSR gateway so the dispatch
     * call shows up as its own stage in the profiler breakdown. Silent
     * no-op if Inertia isn't installed or SSR is disabled.
     */
    private function decorateInertiaSsrGateway(): void
    {
        if (! interface_exists(Gateway::class)) {
            return;
        }

        app()->extend(Gateway::class, fn ($gateway) => new ProfiledSsrGateway($gateway));
    }
}
