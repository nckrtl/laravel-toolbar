<?php

namespace NckRtl\Toolbar\Data;

use NckRtl\Toolbar\Collectors\CollectorInterface;
use NckRtl\Toolbar\Collectors\LaravelCollector;
use NckRtl\Toolbar\Collectors\ModelsCollector;
use NckRtl\Toolbar\Collectors\PhpCollector;
use NckRtl\Toolbar\Collectors\ProfilerCollector;
use NckRtl\Toolbar\Collectors\QueriesCollector;
use NckRtl\Toolbar\Collectors\RequestCollector;
use NckRtl\Toolbar\Collectors\ResponseCollector;
use NckRtl\Toolbar\Collectors\VueCollector;
use NckRtl\Toolbar\Data\Configurations\CollectorConfig;
use NckRtl\Toolbar\Http\Middleware\MiddlewareEnd;
use NckRtl\Toolbar\Http\Middleware\MiddlewareStart;
use NckRtl\Toolbar\Observers\QueryObserver;
use NckRtl\Toolbar\Observers\RequestObserver;
use NckRtl\Toolbar\Observers\RoutingObserver;
use NckRtl\Toolbar\Toolbar;
use Spatie\LaravelData\Data;

class ToolbarConfig extends Data
{
    public bool $debug;

    public array $observers;

    public array $collectors;

    public function __construct()
    {
        $this->debug(false)
            ->middleware(
                prepend: [
                    MiddlewareStart::class,
                ],
                append: [
                    MiddlewareEnd::class,
                ],
            )
            ->observers([
                new RequestObserver,
                new RoutingObserver,
                new QueryObserver,
            ])
            ->collectors([
                new ProfilerCollector,
                new RequestCollector,
                new ResponseCollector,
                new QueriesCollector,
                new LaravelCollector,
                new PhpCollector,
                new VueCollector,
                new ModelsCollector,
            ]);
    }

    public function debug(?bool $debug = null): self
    {
        if (is_null($debug)) {
            $this->debug = ! $this->debug;

            return $this;
        }

        $this->debug = $debug;

        return $this;
    }

    public function middleware(?array $prepend = [], ?array $append = [], ?bool $enable = true): self
    {
        if (! $enable || empty($prepend) && empty($append)) {
            return $this;
        }

        app()->booted(function () use ($prepend, $append) {
            $kernel = app()->make(\Illuminate\Contracts\Http\Kernel::class);
            $router = app()->make(\Illuminate\Routing\Router::class);

            if (! empty($prepend)) {
                foreach ($prepend as $middleware) {

                    $kernel->prependMiddleware($middleware);
                }
            }

            if (! empty($append)) {
                foreach (array_keys($router->getMiddlewareGroups()) as $group) {
                    foreach ($append as $middleware) {
                        $router->pushMiddlewareToGroup($group, $middleware);
                    }
                }
            }
        });

        return $this;
    }

    public function observers(?array $observers = null): self
    {
        $this->observers = $observers;

        return $this;
    }

    public function getObserver(string $observerClass): ?object
    {
        return array_values(
            array_filter(
                $this->observers, fn ($observer) => $observer instanceof $observerClass
            )
        )[0] ?? null;
    }

    public function collectors(?array $collectors = null): self
    {
        if (is_null($collectors) || empty($collectors)) {
            $this->collectors = [];
        }

        foreach ($collectors as $collector) {
            $this->validateCollector($collector);
        }

        $this->collectors = $collectors;

        return $this;
    }

    private function validateCollector($collectorInstance): void
    {
        if (! in_array(CollectorInterface::class, class_implements($collectorInstance) ?: [])) {
            throw new \Exception('Collector '.$collectorInstance.' must implement '.CollectorInterface::class.' interface');
        }

        if (! $collectorInstance->config instanceof CollectorConfig) {
            throw new \Exception('Collector '.$collectorInstance.' config method should return an instance of '.CollectorConfig::class.' class');
        }
    }

    public function enabledCollectors(): array
    {
        return array_filter($this->collectors, fn ($collector) => $collector->config->enabled);
    }

    public function enable(): void
    {
        Toolbar::$enabled = true;
    }

    public function disable(): void
    {
        Toolbar::$enabled = false;
    }
}
