<?php

namespace NckRtl\Toolbar\Observers;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Event;
use NckRtl\Toolbar\Enums\RequestCheckpointId;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;
use NckRtl\Toolbar\ToolbarInjector;

class RequestObserver
{
    public function __construct()
    {
        Event::listen(RequestHandled::class, function ($event) {
            Profiler::record(RequestCheckpointId::REQUEST_HANDLED);
            new ToolbarInjector()->inject($event->request, $event->response);
        });
    }
}
