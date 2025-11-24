<?php

namespace NckRtl\Toolbar\Services\ProfilerService;

use Illuminate\View\Engines\CompilerEngine;
use NckRtl\Toolbar\Data\RequestCheckpointData;
use NckRtl\Toolbar\Data\RequestStageData;
use NckRtl\Toolbar\Enums\RequestCheckpointId;
use NckRtl\Toolbar\Enums\TimeUnit;
use NckRtl\Toolbar\Measurement;

class Profiler
{
    public static array $requestCheckpoints = [];

    public static array $viewRenders = [];

    public static function record(RequestCheckpointId $id, ?RequestCheckpointData $data = null): void
    {
        if (! $data) {
            $data = new RequestCheckpointData;
        }

        if (array_key_exists($id->value, self::$requestCheckpoints)) {
            throw new \Exception('Request checkpoint '.$id->value.' already recorded.');
        }

        self::$requestCheckpoints[$id->value] = $data;
    }

    public static function initialize(): void
    {
        if (defined('LARAVEL_START')) {
            self::record(
                id: RequestCheckpointId::LARAVEL_START,
                data: new RequestCheckpointData(
                    time: new Measurement(LARAVEL_START, TimeUnit::SECONDS),
                    measureMemory: false,
                )
            );
        }

        app()->booting(function () {
            self::record(RequestCheckpointId::BEFORE_SERVICES_PROVIDERS);
        });

        // Track when all providers are booted
        app()->booted(function () {
            self::record(RequestCheckpointId::AFTER_SERVICES_PROVIDERS);

            self::setupViewProfiling();
        });
    }

    private static function setupViewProfiling(): void
    {
        $resolver = app('view.engine.resolver');

        // Wrap the blade engine
        $resolver->register('blade', function () {
            $compiler = app('blade.compiler');
            $files = app('files');

            return new class($compiler, $files) extends CompilerEngine
            {
                public function get($path, array $data = [])
                {
                    $result = parent::get($path, $data);

                    // First view? Record the start
                    if (empty(Profiler::$viewRenders)) {
                        Profiler::record(RequestCheckpointId::BEFORE_VIEW_RENDERING);
                    }

                    Profiler::$viewRenders[$path] = new RequestCheckpointData;

                    return $result;
                }
            };
        });
    }

    public static function getRequestStages(): array
    {
        $requestStages = [];

        $requestStages[] = new RequestStageData(
                                label: 'Bootstrapping',
                                start: Profiler::getCheckpoint(RequestCheckpointId::LARAVEL_START),
                                end: Profiler::getCheckpoint(RequestCheckpointId::BEFORE_SERVICES_PROVIDERS),
                                color: '#FF3031',
                                filesInvolved: [
                                    'index.php',
                                    'bootstrap/app.php',
                                ]
                            );

        $requestStages[] = new RequestStageData(
                                label: 'Booting services providers',
                                start: Profiler::getCheckpoint(RequestCheckpointId::BEFORE_SERVICES_PROVIDERS),
                                end: Profiler::getCheckpoint(RequestCheckpointId::AFTER_SERVICES_PROVIDERS),
                                color: '#FF3031',
                                filesInvolved: [
                                    'app/Providers/*.php',
                                ]
                            );

        $requestStages[] = new RequestStageData(
                                label: 'Preparing request pipeline',
                                start: Profiler::getCheckpoint(RequestCheckpointId::AFTER_SERVICES_PROVIDERS),
                                end: Profiler::getCheckpoint(RequestCheckpointId::BEFORE_MIDDLEWARE),
                                color: '#FF7B4F'
                            );


        $requestStages[] = new RequestStageData(
                                label: 'Middleware in',
                                start: Profiler::getCheckpoint(RequestCheckpointId::BEFORE_MIDDLEWARE),
                                end: Profiler::getCheckpoint(RequestCheckpointId::BEFORE_CONTROLLER),
                                color: '#FFBE4F',
                                filesInvolved: [
                                    'app/Http/Middleware/*.php',
                                ]
                            );

        $requestStages[] = new RequestStageData(
                                label: 'Controller',
                                start: Profiler::getCheckpoint(RequestCheckpointId::BEFORE_CONTROLLER),
                                end: Profiler::getCheckpoint(RequestCheckpointId::BEFORE_VIEW_RENDERING),
                                color: '#0BB981',
                            );

        $requestStages[] = new RequestStageData(
                                label: 'View rendering',
                                start: Profiler::getCheckpoint(RequestCheckpointId::BEFORE_VIEW_RENDERING),
                                end: Profiler::getCheckpoint(RequestCheckpointId::AFTER_VIEW_RENDERING),
                                color: '#09ABF6'
                            );

        $requestStages[] = new RequestStageData(
                                label: 'Middleware out',
                                start: Profiler::getCheckpoint(RequestCheckpointId::AFTER_VIEW_RENDERING),
                                end: Profiler::getCheckpoint(RequestCheckpointId::AFTER_MIDDLEWARE),
                                color: '#FFBE4F',
                                filesInvolved: [
                                    'app/Http/Middleware/*.php',
                                ]
                            );

        $requestStages[] = new RequestStageData(
                                label: 'Preparing response',
                                start: Profiler::getCheckpoint(RequestCheckpointId::AFTER_MIDDLEWARE),
                                end: Profiler::getCheckpoint(RequestCheckpointId::REQUEST_HANDLED),
                                color: '#9879FF'
                            );

        self::$requestCheckpoints = [];

        return $requestStages;
    }

    public static function getCheckpoint(RequestCheckpointId $id): ?RequestCheckpointData
    {
        if (! array_key_exists($id->value, self::$requestCheckpoints)) {
            return null;
        }

        return self::$requestCheckpoints[$id->value];
    }

    public static function getFirstViewRender(): ?RequestCheckpointData
    {
        if (empty(self::$viewRenders)) {
            return null;
        }

        return self::$viewRenders[array_key_first(self::$viewRenders)];
    }

    public static function getLastViewRender(): ?RequestCheckpointData
    {
        if (empty(self::$viewRenders)) {
            return null;
        }

        return self::$viewRenders[array_key_last(self::$viewRenders)];
    }

    public static function getCurrentMemoryUsage(): ?Measurement
    {
        $latestRequestCheckpointWithMemory = array_reverse(
            array_values(
                array_filter(self::$requestCheckpoints, function($checkpoint) {
                    return $checkpoint->measureMemory;
                })
            )
        )[0];

        if(empty($latestRequestCheckpointWithMemory)) {
            throw new \Exception('Current memory usage cant be shared as no checkpoints have been recorded yet.');
        }

        return $latestRequestCheckpointWithMemory->memory_real;
    }
}
