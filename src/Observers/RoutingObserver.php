<?php

namespace NckRtl\Toolbar\Observers;

use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Events\Routing;
use Illuminate\Support\Facades\Event;
use NckRtl\Toolbar\Enums\RequestCheckpointId;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;

class RoutingObserver
{
    public function __construct()
    {
        Event::listen(Routing::class, function () {
            Profiler::record(RequestCheckpointId::BEFORE_ROUTING);
        });

        Event::listen(RouteMatched::class, function ($event) {
            Profiler::record(RequestCheckpointId::AFTER_ROUTING);
        });
    }
}
