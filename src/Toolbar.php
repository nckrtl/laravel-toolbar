<?php

namespace NckRtl\Toolbar;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Events\Routing;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use Laravel\Telescope\Telescope;
use NckRtl\Toolbar\Data\ToolbarConfig;
use NckRtl\Toolbar\Enums\RequestCheckpointId;
use NckRtl\Toolbar\Http\Middleware\WebEnd;
use NckRtl\Toolbar\Http\Middleware\WebStart;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;

class Toolbar extends Telescope
{
    public static bool $enabled = true;

    public array $collectors = [];

    public ToolbarConfig $config;

    public function __construct()
    {
        $this->configure()
            ->registerEventListeners()
            ->registerMiddleware();
    }

    public function configure(): self
    {
        $this->config = new ToolbarConfig;

        return $this;
    }

    public function registerEventListeners(): self
    {
        Event::listen(Routing::class, function () {
            Profiler::record(RequestCheckpointId::BEFORE_ROUTING);
        });

        Event::listen(RouteMatched::class, function ($event) {
            Profiler::record(RequestCheckpointId::AFTER_ROUTING);
        });

        Event::listen(RequestHandled::class, function ($event) {
            Profiler::record(RequestCheckpointId::REQUEST_HANDLED);
            new ToolbarInjector()->inject($event->request, $event->response);
        });

        return $this;
    }

    public function registerMiddleware(): self
    {
        $router = app(Router::class);

        $router->prependMiddlewareToGroup('web', WebStart::class);
        $router->pushMiddlewareToGroup('web', WebEnd::class);

        return $this;
    }

    public static function isEnabled(): bool
    {
        if (app()->runningInConsole()) {
            return false;
        }

        if (! self::$enabled) {
            return false;
        }

        if (self::ignoreRequest(request())) {
            return false;
        }

        return true;
    }

    public static function ignoreRequest(Request $request): bool
    {
        $telescopIgnorePaths = config('telescope.ignore_paths', []);

        $additionalIgnorePaths = [
            'telescope*',
            '_laravel-toolbar*',
        ];

        config(['telescope.ignore_paths' => array_merge($telescopIgnorePaths, $additionalIgnorePaths)]);

        return ! self::requestIsToApprovedUri($request);
    }

    public function telescopeIsInstalled(): bool
    {
        return class_exists('Laravel\Telescope\Telescope');
    }
}
