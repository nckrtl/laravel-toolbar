<?php

namespace NckRtl\Toolbar;

use NckRtl\Toolbar\Data\ToolbarConfig;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;

class Toolbar
{
    public static bool $enabled = true;

    public static bool $visible = true;

    public static function enable(): void
    {
        self::$enabled = true;
    }

    public static function disable(): void
    {
        self::$enabled = false;
    }

    public static function show(): void
    {
        self::$visible = true;
    }

    public static function hide(): void
    {
        self::$visible = false;
    }

    public array $collectors = [];

    public array $observers;

    public ToolbarConfig $config;

    public function __construct()
    {
        Profiler::initialize();

        $this->config = new ToolbarConfig;
    }

    public static function isEnabled(): bool
    {
        if (! self::$enabled) {
            return false;
        }

        if (app()->bound(Toolbar::class) && app(Toolbar::class)->config->enabledInConsole) {
            return true;
        }

        if (app()->runningInConsole()) {
            return false;
        }

        return true;
    }

    public function telescopeIsInstalled(): bool
    {
        return class_exists('Laravel\Telescope\Telescope');
    }
}
