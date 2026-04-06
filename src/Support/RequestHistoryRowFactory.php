<?php

declare(strict_types=1);

namespace NckRtl\Toolbar\Support;

use Illuminate\Http\Request;
use NckRtl\Toolbar\Enums\DataSizeUnit;
use NckRtl\Toolbar\Measurement;

final class RequestHistoryRowFactory
{
    public function fromPayload(Request $request, array $payload): array
    {
        $statusCode = $this->resolveStatusCode(data_get($payload, 'response.status_code'));
        $isInertia = (bool) data_get($payload, 'request.is_inertia', false);
        $responseType = $this->resolveResponseType(
            request: $request,
            statusCode: $statusCode,
            isInertia: $isInertia,
            headers: data_get($payload, 'response.headers'),
        );

        return [
            'id' => $this->resolveRequestId($payload),
            'is_xhr' => $this->isAsyncRequest($request, $isInertia, $responseType),
            'method' => (string) data_get($payload, 'request.method', $request->getMethod()),
            'uri' => (string) data_get($payload, 'request.uri', $request->getPathInfo()),
            'name' => $this->resolveRouteName(data_get($payload, 'request.route_name'), $request),
            'action' => $this->resolveControllerAction(data_get($payload, 'request.controller_action'), $request),
            'middleware_count' => $this->resolveMiddlewareCount(data_get($payload, 'request.middleware'), $request),
            'status_code' => $statusCode,
            'size' => $this->resolveSize(data_get($payload, 'response.size')),
            'duration' => $this->resolveDuration($payload),
            'response_type' => $responseType,
        ];
    }

    public function fromRequest(Request $request, mixed $response, string $requestId): array
    {
        $statusCode = $this->resolveStatusCode(method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null);
        $isInertia = $request->header('X-Inertia') === 'true';
        $responseType = $this->resolveResponseType(
            request: $request,
            statusCode: $statusCode,
            isInertia: $isInertia,
            headers: is_object($response) && isset($response->headers) ? $response->headers->all() : [],
        );

        return [
            'id' => $requestId,
            'is_xhr' => $this->isAsyncRequest($request, $isInertia, $responseType),
            'method' => $request->getMethod(),
            'uri' => $request->getPathInfo(),
            'name' => $request->route()?->getName() ?? '-',
            'action' => $this->resolveControllerAction($request->route()?->getActionName(), $request),
            'middleware_count' => count(array_values($request->route()?->gatherMiddleware() ?? [])),
            'status_code' => $statusCode,
            'size' => $this->resolveResponseSize($response),
            'duration' => null,
            'response_type' => $responseType,
        ];
    }

    private function resolveRequestId(array $payload): string
    {
        $requestId = data_get($payload, 'metadata.request_id');

        if (is_string($requestId) && $requestId !== '') {
            return $requestId;
        }

        $id = data_get($payload, 'metadata.id');

        return is_string($id) ? $id : '';
    }

    private function resolveRouteName(mixed $routeName, Request $request): string
    {
        if (is_string($routeName) && $routeName !== '') {
            return $routeName;
        }

        return $request->route()?->getName() ?? '-';
    }

    private function resolveControllerAction(mixed $controllerAction, Request $request): ?string
    {
        $action = $controllerAction;

        if (! is_string($action) || $action === '') {
            $action = $request->route()?->getActionName();
        }

        if (! is_string($action) || $action === '' || $action === '-' || $action === 'Closure') {
            return null;
        }

        return $action;
    }

    private function resolveMiddlewareCount(mixed $middleware, Request $request): int
    {
        if (is_array($middleware)) {
            return count($middleware);
        }

        return count(array_values($request->route()?->gatherMiddleware() ?? []));
    }

    private function resolveStatusCode(mixed $statusCode): int
    {
        return is_numeric($statusCode) ? (int) $statusCode : 0;
    }

    private function resolveSize(mixed $size): ?string
    {
        if ($size instanceof Measurement) {
            return $size->formattedValue;
        }

        if (is_array($size)) {
            $formattedValue = $size['formattedValue'] ?? null;

            return is_string($formattedValue) && $formattedValue !== '' ? $formattedValue : null;
        }

        if (is_object($size) && isset($size->formattedValue) && is_string($size->formattedValue)) {
            return $size->formattedValue !== '' ? $size->formattedValue : null;
        }

        return is_string($size) && $size !== '' ? $size : null;
    }

    private function resolveResponseSize(mixed $response): ?string
    {
        if (! is_object($response) || ! method_exists($response, 'getContent')) {
            return null;
        }

        $content = $response->getContent();

        if (! is_string($content)) {
            return null;
        }

        return (new Measurement(
            value: strlen($content),
            unit: DataSizeUnit::BYTES,
        ))->formattedValue;
    }

    private function resolveDuration(array $payload): string|int|float|null
    {
        $profilerDuration = data_get($payload, 'profiler.total_wall_time.formattedValue');

        if (is_string($profilerDuration) && $profilerDuration !== '') {
            return $profilerDuration;
        }

        $duration = data_get($payload, 'request.duration');

        if (is_string($duration) && $duration !== '') {
            return $duration;
        }

        if (is_int($duration) || is_float($duration)) {
            return $duration;
        }

        $totalWallTime = data_get($payload, 'metadata.wall_time.total');

        return is_string($totalWallTime) && $totalWallTime !== '' ? $totalWallTime : null;
    }

    private function isAsyncRequest(Request $request, bool $isInertia = false, ?string $responseType = null): bool
    {
        if ($isInertia) {
            return ! $this->shouldResetInertiaHistory($request, $responseType);
        }

        if ($request->ajax()) {
            return true;
        }

        return $request->expectsJson();
    }

    private function shouldResetInertiaHistory(Request $request, ?string $responseType): bool
    {
        if ($request->isMethod('GET')) {
            return true;
        }

        return in_array($responseType, ['Inertia', 'Inertia Redirect', 'Redirect'], true);
    }

    private function resolveResponseType(Request $request, int $statusCode, bool $isInertia, mixed $headers): string
    {
        if ($statusCode >= 300 && $statusCode < 400) {
            return $isInertia ? 'Inertia Redirect' : 'Redirect';
        }

        if ($statusCode >= 400 && $statusCode < 500) {
            return 'Client Error';
        }

        if ($statusCode >= 500 && $statusCode < 600) {
            return 'Server Error';
        }

        if ($isInertia) {
            return 'Inertia';
        }

        $contentType = $this->resolveContentType($headers);

        if ($contentType !== null && str_contains($contentType, 'json')) {
            return 'JSON';
        }

        if ($contentType !== null && str_contains($contentType, 'html')) {
            return 'HTML';
        }

        if ($request->expectsJson()) {
            return 'JSON';
        }

        return 'Other';
    }

    private function resolveContentType(mixed $headers): ?string
    {
        if (! is_array($headers)) {
            return null;
        }

        $contentType = $headers['content-type'] ?? $headers['Content-Type'] ?? null;

        if (is_string($contentType) && $contentType !== '') {
            return strtolower($contentType);
        }

        if (is_array($contentType) && $contentType !== []) {
            $first = reset($contentType);

            return is_string($first) && $first !== '' ? strtolower($first) : null;
        }

        return null;
    }
}
