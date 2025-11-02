<?php

namespace NckRtl\Toolbar\Collectors;

use Illuminate\Foundation\Concerns\ResolvesDumpSource;
use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Data\Configurations\LaravelConfig;
use NckRtl\Toolbar\Data\LaravelData;
use NckRtl\Toolbar\Traits\ResolvesConfigSource;

/**
 * @property LaravelConfig $config
 */
class LaravelCollector extends Collector implements CollectorInterface
{
    use ResolvesConfigSource;
    use ResolvesDumpSource;

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
            version_editor_url: $this->config->version ? $this->getLaravelVersionEditorUrl() : null,

            environment: $this->config->environment ? app()->environment() : null,
            environment_editor_url: $this->config->environment ? $this->getConfigEditorUrl('app.env') : null,

            host: $this->config->host ? request()->getHost() : null,

            timezone: $this->config->timezone ? config('app.timezone') : null,
            timezone_editor_url: $this->config->timezone ? $this->getConfigEditorUrl('app.timezone') : null,

            locale: $this->config->locale ? config('app.locale') : null,
            locale_editor_url: $this->config->locale ? $this->getConfigEditorUrl('app.locale') : null,

            debug: $this->config->debug ? config('app.debug') : null,
            debug_editor_url: $this->config->debug ? $this->getConfigEditorUrl('app.debug') : null,
        );
    }

    private function getLaravelVersionEditorUrl(): ?string
    {
        $composerPath = base_path('composer.json');

        if (! file_exists($composerPath)) {
            return null;
        }

        $contents = file($composerPath);

        foreach ($contents as $index => $line) {
            if (str_contains($line, '"laravel/framework"')) {
                return $this->resolveSourceHref($composerPath, $index + 1);
            }
        }

        return null;
    }
}
