<?php

namespace NckRtl\Toolbar\Services\ProfilerService;

use Inertia\Ssr\ExcludesSsrPaths;
use Inertia\Ssr\Gateway;
use Inertia\Ssr\HasHealthCheck;
use Inertia\Ssr\Response;
use NckRtl\Toolbar\Data\RequestCheckpointData;
use NckRtl\Toolbar\Enums\RequestCheckpointId;

/**
 * Wraps Inertia's SSR Gateway so a real SSR dispatch shows up as its own
 * stage in the request breakdown. Checkpoints are only recorded when
 * dispatch() returns a Response — when SSR is disabled, skipped via the
 * except list, or the bundle is missing, dispatch returns null and the
 * stage stays hidden.
 *
 * Implements ExcludesSsrPaths and HasHealthCheck so Inertia's middleware
 * and health commands still see the wrapped gateway as capable of those
 * operations. Calls are forwarded to the inner gateway when it supports
 * them; otherwise they degrade the same way Inertia degrades for a plain
 * Gateway implementation.
 */
final class ProfiledSsrGateway implements ExcludesSsrPaths, Gateway, HasHealthCheck
{
    public function __construct(private readonly Gateway $inner) {}

    public function dispatch(array $page): ?Response
    {
        $before = new RequestCheckpointData;

        $response = $this->inner->dispatch($page);

        if ($response === null) {
            return null;
        }

        Profiler::record(RequestCheckpointId::BEFORE_INERTIA_SSR, $before);
        Profiler::record(RequestCheckpointId::AFTER_INERTIA_SSR);

        return $response;
    }

    public function except(array|string $paths): void
    {
        if ($this->inner instanceof ExcludesSsrPaths) {
            $this->inner->except($paths);
        }
    }

    public function isHealthy(): bool
    {
        if ($this->inner instanceof HasHealthCheck) {
            return $this->inner->isHealthy();
        }

        return false;
    }
}
