<?php

namespace NckRtl\Toolbar\Events;

use Illuminate\Foundation\Events\Dispatchable;
use NckRtl\Toolbar\Enums\RequestCheckpointId;

class ProfilerEvent
{
    use Dispatchable;

    public function __construct(
        public RequestCheckpointId $event,
    ) {
    }
}
