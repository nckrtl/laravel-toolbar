<?php

namespace NckRtl\Toolbar\Services\ProfilerService;

use Inertia\Ssr\Gateway;
use Inertia\Ssr\Response;
use NckRtl\Toolbar\Enums\RequestCheckpointId;

/**
 * Wraps Inertia's SSR Gateway so the dispatch call shows up as its own
 * stage in the request breakdown. Records BEFORE_INERTIA_SSR and
 * AFTER_INERTIA_SSR checkpoints around the underlying dispatch — if SSR
 * is disabled or the call never runs, no checkpoints are recorded and
 * the stage is simply absent from the breakdown.
 */
final class ProfiledSsrGateway implements Gateway
{
    public function __construct(private readonly Gateway $inner) {}

    public function dispatch(array $page): ?Response
    {
        Profiler::record(RequestCheckpointId::BEFORE_INERTIA_SSR);

        try {
            return $this->inner->dispatch($page);
        } finally {
            Profiler::record(RequestCheckpointId::AFTER_INERTIA_SSR);
        }
    }
}
