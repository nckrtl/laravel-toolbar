<?php

namespace NckRtl\Toolbar\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use NckRtl\Toolbar\Enums\RequestCheckpointId;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;
use NckRtl\Toolbar\Toolbar;

class MiddlewareStart
{
    public function handle(Request $request, Closure $next)
    {
        // Reset static state for Octane/long-running processes
        Toolbar::$enabled = config('toolbar.enabled', true);
        Toolbar::$visible = config('toolbar.visible', true);

        if (! Profiler::getCheckpoint(RequestCheckpointId::BEFORE_MIDDLEWARE)) {
            Profiler::record(RequestCheckpointId::BEFORE_MIDDLEWARE);
        }

        $response = $next($request);

        if (! Profiler::getCheckpoint(RequestCheckpointId::AFTER_MIDDLEWARE)) {
            Profiler::record(RequestCheckpointId::AFTER_MIDDLEWARE);
        }

        return $response;
    }
}
