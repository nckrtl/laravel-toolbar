<?php

namespace NckRtl\Toolbar\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use NckRtl\Toolbar\Enums\RequestCheckpointId;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;

class WebStart
{
    public function handle(Request $request, Closure $next)
    {
        Profiler::record(RequestCheckpointId::BEFORE_MIDDLEWARE);

        $response = $next($request);

        Profiler::record(RequestCheckpointId::AFTER_MIDDLEWARE);

        return $response;
    }
}
