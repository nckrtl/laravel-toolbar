<?php

namespace NckRtl\Toolbar\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use NckRtl\Toolbar\Enums\RequestCheckpointId;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;

class WebEnd
{
    public function handle(Request $request, Closure $next)
    {
        if(!Profiler::getCheckpoint(RequestCheckpointId::BEFORE_CONTROLLER)) {
            Profiler::record(RequestCheckpointId::BEFORE_CONTROLLER);
        }

        $response = $next($request);

        if(!Profiler::getCheckpoint(RequestCheckpointId::AFTER_VIEW_RENDERING)) {
            Profiler::record(RequestCheckpointId::AFTER_VIEW_RENDERING);
        }

        return $response;
    }
}
