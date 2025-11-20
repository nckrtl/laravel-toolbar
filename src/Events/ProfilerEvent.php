<?php

namespace NckRtl\Toolbar\Events;

use Illuminate\Foundation\Events\Dispatchable;
use NckRtl\Toolbar\Enums\Stage;

class ProfilerEvent
{
    use Dispatchable;

    public function __construct(
        public Stage $event,
    ) {}
}
