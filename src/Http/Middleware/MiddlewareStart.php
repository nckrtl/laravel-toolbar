<?php

declare(strict_types=1);

namespace NckRtl\Toolbar\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use NckRtl\Toolbar\Enums\RequestCheckpointId;
use NckRtl\Toolbar\Observers\QueryObserver;
use NckRtl\Toolbar\Services\ProfilerService\Profiler;
use NckRtl\Toolbar\Support\ProfileRequestContext;
use NckRtl\Toolbar\Toolbar;

class MiddlewareStart
{
    public function handle(Request $request, Closure $next)
    {
        // Reset static state for Octane/long-running processes
        Toolbar::$enabled = config('toolbar.enabled', true);
        Toolbar::$visible = config('toolbar.visible', true);
        ProfileRequestContext::setResolvedAuth($request, 'guest');

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
        $context = ProfileRequestContext::fromRequest($request);

        if (! in_array($context->authMode, ['first-user', 'user'], true)) {
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

        $userId = match ($context->authMode) {
            'first-user' => $userModel::query()->first()?->getKey(),
            'user' => $context->userId,
            default => null,
        };

        if ($userId === null) {
            return;
        }

        if (! Auth::onceUsingId($userId)) {
            return;
        }

        ProfileRequestContext::setResolvedAuth($request, $context->authMode, Auth::id());

        $queryObserver = app(Toolbar::class)->config->getObserver(QueryObserver::class);
        $queryObserver?->reset();
    }
}
