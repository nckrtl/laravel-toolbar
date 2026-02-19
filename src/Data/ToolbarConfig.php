<?php

namespace NckRtl\Toolbar\Data;

use NckRtl\Toolbar\Collectors\CollectorInterface;
use NckRtl\Toolbar\Collectors\InertiaCollector;
use NckRtl\Toolbar\Collectors\LaravelCollector;
use NckRtl\Toolbar\Collectors\ModelsCollector;
use NckRtl\Toolbar\Collectors\PhpCollector;
use NckRtl\Toolbar\Collectors\ProfilerCollector;
use NckRtl\Toolbar\Collectors\QueriesCollector;
use NckRtl\Toolbar\Collectors\RequestCollector;
use NckRtl\Toolbar\Collectors\ResponseCollector;
use NckRtl\Toolbar\Collectors\TailwindCollector;
use NckRtl\Toolbar\Collectors\VueCollector;
use NckRtl\Toolbar\Data\Configurations\CollectorConfig;
use NckRtl\Toolbar\Data\Configurations\MiddlewareConfig;
use NckRtl\Toolbar\Data\Layout\GroupConfig;
use NckRtl\Toolbar\Data\Layout\LayoutConfig;
use NckRtl\Toolbar\Data\Tools\BreakpointIndicatorTool;
use NckRtl\Toolbar\Data\Tools\DatabaseTool;
use NckRtl\Toolbar\Data\Tools\MemoryUsageTool;
use NckRtl\Toolbar\Data\Tools\ModelsTool;
use NckRtl\Toolbar\Data\Tools\RequestTool;
use NckRtl\Toolbar\Data\Tools\TechStackTool;
use NckRtl\Toolbar\Data\Tools\TimingsTool;
use NckRtl\Toolbar\Enums\Layout\Section;
use NckRtl\Toolbar\Http\Middleware\MiddlewareEnd;
use NckRtl\Toolbar\Http\Middleware\MiddlewareStart;
use NckRtl\Toolbar\Observers\ModelObserver;
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

    public bool $enabledInConsole = false;

    public bool $isVueDevtoolsEnabled = false;

    public LayoutConfig $layout;

    public function __construct()
    {
        $this->debug(false)
            ->middleware(
                new MiddlewareConfig(
                    prepend: [
                        MiddlewareStart::class,
                    ],
                    append: [
                        MiddlewareEnd::class,
                    ],
                )
            )
            ->observers([
                new RequestObserver,
                new RoutingObserver,
                new QueryObserver,
                new ModelObserver,
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
                new TailwindCollector,
                new InertiaCollector,
            ])
            ->layout(
                new LayoutConfig()->addGroup(
                    (new GroupConfig(priority: 10))->setTools(
                        new RequestTool,
                        new TimingsTool,
                        new MemoryUsageTool,
                        new DatabaseTool,
                        new ModelsTool
                    )->section(Section::CENTER)
                )->addGroup(
                    (new GroupConfig(priority: 20))->setTools(
                        new TechStackTool,
                    )->section(Section::CENTER)
                )->addGroup(
                    (new GroupConfig(priority: 30))->setTools(
                        new BreakpointIndicatorTool(show_pixels: true),
                    )->section(Section::CENTER)
                )
            );
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

    public function middleware(MiddlewareConfig $middlewareConfig): self
    {
        if (! $middlewareConfig->isEnabled() || empty($middlewareConfig->prepend) && empty($middlewareConfig->append)) {
            return $this;
        }

        app()->booted(function () use ($middlewareConfig) {
            $kernel = app()->make(\Illuminate\Contracts\Http\Kernel::class);
            $router = app()->make(\Illuminate\Routing\Router::class);

            foreach ($middlewareConfig->prepend as $middleware) {
                $kernel->prependMiddleware($middleware);
            }

            foreach (array_keys($router->getMiddlewareGroups()) as $group) {
                foreach ($middlewareConfig->append as $middleware) {
                    $router->pushMiddlewareToGroup($group, $middleware);
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

    public function updateCollectorConfig(string $collectorClass, CollectorConfig $collectorConfig): self
    {
        $collector = array_values(array_filter($this->collectors, fn ($collector) => $collector instanceof $collectorClass));

        if (empty($collector)) {
            throw new \Exception('Collector '.$collectorClass.' not found');
        }

        $collector[0]->config = $collectorConfig;

        return $this;
    }

    public function enable(): self
    {
        Toolbar::$enabled = true;

        return $this;
    }

    public function disable(): self
    {
        Toolbar::$enabled = false;

        return $this;
    }

    public function show(): self
    {
        Toolbar::$visible = true;

        return $this;
    }

    public function hide(): self
    {
        Toolbar::$visible = false;

        return $this;
    }

    public function enableInConsole(bool $enabled = true): self
    {
        $this->enabledInConsole = $enabled;

        return $this;
    }

    public function layout(LayoutConfig|callable $layoutConfig): self
    {
        if (is_callable($layoutConfig)) {
            $layoutConfig($this->layout);
        } else {
            $this->layout = $layoutConfig;
        }

        return $this;
    }

    public function additionalLightDomHtml(): string
    {
        $additionalHtml = [];

        foreach ($this->layout->sections as $section => $groups) {
            foreach ($groups as $group) {
                foreach ($group->tools as $tool) {
                    if (method_exists($tool, 'additionalLightDomHtml')) {
                        $additionalHtml[] = $tool->additionalLightDomHtml();
                    }
                }
            }
        }

        return implode("\n", array_unique($additionalHtml));
    }
}
