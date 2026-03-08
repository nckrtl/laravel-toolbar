<?php

namespace NckRtl\Toolbar\Services\ProfilerService;

use Illuminate\Contracts\View\Engine;
use NckRtl\Toolbar\Data\RequestCheckpointData;
use NckRtl\Toolbar\Enums\RequestCheckpointId;

class ProfiledViewEngine implements Engine
{
    public function __construct(
        private readonly Engine $engine
    ) {}

    public function get($path, array $data = [])
    {
        if (empty(Profiler::$viewRenders)) {
            Profiler::record(RequestCheckpointId::BEFORE_VIEW_RENDERING);
        }

        $result = $this->engine->get($path, $data);

        Profiler::$viewRenders[$path] = new RequestCheckpointData;

        return $result;
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->engine->{$name}(...$arguments);
    }
}
