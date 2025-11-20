<?php

namespace NckRtl\Toolbar\Collectors;

use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Data\LaravelData;
use NckRtl\Toolbar\Data\Configurations\LaravelConfig;

class LaravelCollector extends Collector implements CollectorInterface
{
    public function key(): string
    {
        return 'laravel';
    }

    public function configClass(): string
    {
        return LaravelConfig::class;
    }

    public function collectData(CollectorManager $collectorManager): ?LaravelData
    {
         return new LaravelData(
            version: $this->config->version ? app()->version() : null,
            environment: $this->config->environment ? app()->environment() : null,
            timezone: $this->config->timezone ? config('app.timezone') : null,
            locale: $this->config->locale ? config('app.locale') : null,
            debug: $this->config->debug ? config('app.debug') : null,
        );
    }
}
