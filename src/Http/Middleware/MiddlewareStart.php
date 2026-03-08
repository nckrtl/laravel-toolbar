<?php

namespace NckRtl\Toolbar\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use NckRtl\Toolbar\Enums\RequestCheckpointId;
use NckRtl\Toolbar\Observers\QueryObserver;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;
use NckRtl\Toolbar\Toolbar;

class MiddlewareStart
{
    public function handle(Request $request, Closure $next)
    {
        // Reset static state for Octane/long-running processes
        Toolbar::$enabled = config('toolbar.enabled', true);
        Toolbar::$visible = config('toolbar.visible', true);

        $this->authenticateMcpRequest($request);

        if (! Profiler::getCheckpoint(RequestCheckpointId::BEFORE_MIDDLEWARE)) {
            Profiler::record(RequestCheckpointId::BEFORE_MIDDLEWARE);
        }

        $response = $next($request);

        if (! Profiler::getCheckpoint(RequestCheckpointId::AFTER_MIDDLEWARE)) {
            Profiler::record(RequestCheckpointId::AFTER_MIDDLEWARE);
        }

        return $response;
    }

    private function authenticateMcpRequest(Request $request): void
    {
        if (! $request->hasHeader('X-MCP-ID')) {
            return;
        }

        if (! app()->environment(config('login-link.allowed_environments', ['local']))) {
            return;
        }

        $userModel = config('login-link.user_model')
            ?? config('auth.providers.'.config('auth.guards.web.provider').'.model');

        if (! $userModel) {
            return;
        }

        $user = $userModel::query()->first();

        if (! $user) {
            return;
        }

        Auth::onceUsingId($user->getKey());

        $queryObserver = app(Toolbar::class)->config->getObserver(QueryObserver::class);
        $queryObserver?->reset();
    }
}
