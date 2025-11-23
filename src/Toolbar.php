<?php

namespace NckRtl\Toolbar;

use Illuminate\Http\Request;
use NckRtl\Toolbar\ToolbarInjector;
use Illuminate\Support\Facades\Event;
use Illuminate\Routing\Events\Routing;
use NckRtl\Toolbar\Data\ToolbarConfig;
use NckRtl\Toolbar\Http\Middleware\WebEnd;
use Illuminate\Routing\Events\RouteMatched;
use NckRtl\Toolbar\Observers\QueryObserver;
use NckRtl\Toolbar\Http\Middleware\WebStart;
use NckRtl\Toolbar\Enums\RequestCheckpointId;
use Illuminate\Foundation\Http\Events\RequestHandled;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;

class Toolbar
{
    public static bool $enabled = true;

    public array $collectors = [];

    public array $observers;

    public int $queryMemory = 0;

    public array $queries = [];

    public ToolbarConfig $config;

    public function __construct()
    {
        $this->configure()
            ->registerListeners()
            ->registerMiddleware();
    }

    public function configure(): self
    {
        $this->config = new ToolbarConfig();

        return $this;
    }

    public function registerListeners(): self
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

        $this->observers = [
            QueryObserver::class => new QueryObserver(),
        ];

        return $this;
    }

    protected function getCallerLocation($sql)
    {
        // Small depth = major performance gain
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        static $basePath;
        static $basePathLen;
        static $basePathSlash;

        if (!$basePath) {
            $basePath = base_path();
            $basePathLen = strlen($basePath);
            $basePathSlash = $basePath . '/';
        }

        foreach ($trace as $frame) {
            if (empty($frame['file'])) {
                continue;
            }

            $file = $frame['file'];

            // Skip vendor code (fast prefix check instead of contains)
            // Using strpos is faster than str_contains
            if (strpos($file, $basePathSlash . 'vendor/') !== false) {
                continue;
            }

            // app/ folder or anything under project root
            $isApp = strpos($file, $basePathSlash . 'app/') !== false;
            $isInsideProject = (strncmp($file, $basePathSlash, $basePathLen + 1) === 0);

            if ($isApp || $isInsideProject) {

                // Strip base path with a single substr
                if ($isInsideProject) {
                    $file = substr($file, $basePathLen + 1);
                }

                return [
                    'file' => $file,
                    'line' => $frame['line'] ?? 0,
                    'caller' => isset($frame['class'])
                        ? ($frame['class'] . ($frame['type'] ?? '') . ($frame['function'] ?? 'unknown'))
                        : ($frame['function'] ?? 'unknown'),
                ];
            }
        }

        // Fallback (no extra work)
        $first = $trace[0] ?? [];
        return [
            'file' => $first['file'] ?? 'unknown',
            'line' => $first['line'] ?? 0,
            'caller' => 'unknown',
        ];
    }

    public function registerMiddleware(): self
    {
        app()->booted(function () {
            $kernel = app()->make(\Illuminate\Contracts\Http\Kernel::class);
            $router = app()->make(\Illuminate\Routing\Router::class);

            // WebStart at the very beginning
            $kernel->prependMiddleware(WebStart::class);

            try {
                $request = app()->make('request');
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

        return $this;
    }

    public static function isEnabled(): bool
    {
        if (app()->runningInConsole()) {
            return false;
        }

        if (!self::$enabled) {
            return false;
        }

        if (self::ignoreRequest(request())) {
            return false;
        }

        return true;
    }

    public static function ignoreRequest(Request $request): bool
    {
        return false;

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
