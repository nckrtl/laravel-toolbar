<?php

namespace NckRtl\Toolbar;

use Illuminate\Http\Request;
use NckRtl\Toolbar\Data\ToolbarConfig;
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
        Profiler::initialize();

        $this->config = new ToolbarConfig;
    }

    public static function isEnabled(): bool
    {
        if (app()->runningInConsole()) {
            return false;
        }

        if (! self::$enabled) {
            return false;
        }

        // if (self::ignoreRequest(request())) {
        //     return false;
        // }

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
