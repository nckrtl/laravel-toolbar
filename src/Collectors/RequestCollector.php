<?php

namespace NckRtl\Toolbar\Collectors;

use Illuminate\Http\UploadedFile;
use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Data\Configurations\RequestConfig;
use NckRtl\Toolbar\Data\MiddlewareData;
use NckRtl\Toolbar\Data\RequestData;
use NckRtl\Toolbar\Enums\DataSizeUnit;
use NckRtl\Toolbar\Measurement;
use NckRtl\Toolbar\Observers\ViewObserver;
use NckRtl\Toolbar\Toolbar;

/**
 * @property RequestConfig $config
 */
class RequestCollector extends Collector implements CollectorInterface
{
    public function key(): string
    {
        return 'request';
    }

    public function configClass(): string
    {
        return RequestConfig::class;
    }

    public function collectData(CollectorManager $collectorManager): ?RequestData
    {
        $request = request();
        $route = $request->route();
        $isInertia = $request->header('X-Inertia') === 'true';

        [$viewName, $viewData] = $this->resolveViewData($isInertia, $collectorManager);

        return new RequestData(
            is_inertia: $isInertia,
            uuid: null,
            method: $request->method(),
            uri: $request->getPathInfo(),
            ip_address: $request->ip(),
            controller_action: $route?->getActionName() ?? '-',
            middleware: $this->resolveMiddleware(optional($route)->gatherMiddleware() ?? []),
            duration: null,
            route_uri_pattern: $route?->uri(),
            route_parameters: $this->normalizeValues($route?->parameters() ?? []),
            query_parameters: $this->normalizeValues($request->query()),
            headers: $this->normalizeHeaders($request->headers->all()),
            uploaded_files: $this->summarizeUploadedFiles($request->allFiles()),
            view_name: $viewName,
            view_data: $viewData,
        );
    }

    /**
     * Resolve the view name and data.
     *
     * For Inertia requests, the component name and props come from the response JSON.
     * For Blade views, they come from the ViewObserver.
     *
     * @return array{0: string|null, 1: array|null}
     */
    private function resolveViewData(bool $isInertia, CollectorManager $collectorManager): array
    {
        if ($isInertia) {
            return $this->resolveInertiaData($collectorManager);
        }

        return $this->resolveBladeViewData();
    }

    /**
     * @return array{0: string|null, 1: array|null}
     */
    private function resolveInertiaData(CollectorManager $collectorManager): array
    {
        $response = $collectorManager->response;

        if ($response === null) {
            return [null, null];
        }

        try {
            $content = $response->getContent();

            if (! is_string($content) || $content === '') {
                return [null, null];
            }

            $json = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            $component = is_string($json['component'] ?? null) ? $json['component'] : null;
            $props = is_array($json['props'] ?? null) ? $json['props'] : null;

            return [$component, $props];
        } catch (\Throwable) {
            return [null, null];
        }
    }

    /**
     * @return array{0: string|null, 1: array|null}
     */
    private function resolveBladeViewData(): array
    {
        if (! app()->bound(Toolbar::class)) {
            return [null, null];
        }

        $observer = app(Toolbar::class)->config->getObserver(ViewObserver::class);

        if (! $observer instanceof ViewObserver) {
            return [null, null];
        }

        return [$observer->viewName, $observer->viewData];
    }

    /**
     * @param  array<string>  $middleware
     * @return array<MiddlewareData>
     */
    private function resolveMiddleware(array $middleware): array
    {
        return array_values(array_map(function (string $entry): MiddlewareData {
            // Strip any parameters (e.g. "throttle:60,1")
            $class = explode(':', $entry)[0];

            $file = null;
            $hasOutbound = false;

            try {
                if (class_exists($class)) {
                    $reflection = new \ReflectionClass($class);
                    $file = $reflection->getFileName() ?: null;

                    $hasOutbound = $this->detectOutbound($class, $reflection);
                }
            } catch (\Throwable) {
                // Closures or built-ins — leave file as null
            }

            return new MiddlewareData(class: $entry, file: $file, has_outbound: $hasOutbound);
        }, $middleware));
    }

    /**
     * Determine whether a middleware class contains outbound (post-$next) logic.
     */
    private function detectOutbound(string $class, \ReflectionClass $reflection): bool
    {
        // Middleware implementing a TerminatesWithoutResponse contract always has outbound behaviour
        foreach ($reflection->getInterfaceNames() as $interface) {
            if (str_ends_with($interface, 'TerminatesWithoutResponse')) {
                return true;
            }
        }

        // Check if the class declares its own terminate() method (terminable middleware)
        if ($reflection->hasMethod('terminate')) {
            $terminate = $reflection->getMethod('terminate');
            if ($terminate->getDeclaringClass()->getName() === $class) {
                return true;
            }
        }

        // Inspect the handle() method body for code after the $next(...) call
        try {
            $ref = new \ReflectionMethod($class, 'handle');
            $fileName = $ref->getFileName();

            if ($fileName === false) {
                return false;
            }

            $file = file($fileName);

            if ($file === false) {
                return false;
            }

            $start = $ref->getStartLine() - 1;
            $end = $ref->getEndLine() - 1;
            $body = implode('', array_slice($file, $start, $end - $start + 1));

            // Find position of $next( call; check if there's meaningful code after it
            $nextPos = strpos($body, '$next(');
            if ($nextPos !== false) {
                $afterNext = substr($body, $nextPos);

                return (bool) preg_match('/\$next\([^)]+\)[^;]*;.*\S/s', $afterNext);
            }
        } catch (\Throwable) {
            // Reflection failed — assume no outbound logic
        }

        return false;
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map(
            fn (array $values) => array_values(array_map(static fn ($value) => (string) $value, $values)),
            $headers,
        );
    }

    private function normalizeValues(array $values): array
    {
        return array_map(fn ($value) => $this->normalizeValue($value), $values);
    }

    private function normalizeValue(mixed $value): mixed
    {
        if (is_null($value) || is_bool($value) || is_int($value) || is_float($value) || is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            return array_map(fn ($nestedValue) => $this->normalizeValue($nestedValue), $value);
        }

        if (is_object($value) && method_exists($value, 'getRouteKey')) {
            return $value->getRouteKey();
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return is_object($value) ? class_basename($value) : (string) $value;
    }

    private function summarizeUploadedFiles(array $files, string $prefix = ''): array
    {
        $uploadedFiles = [];

        foreach ($files as $key => $file) {
            $field = $prefix === '' ? (string) $key : $prefix.'.'.$key;

            if (is_array($file)) {
                $uploadedFiles = [...$uploadedFiles, ...$this->summarizeUploadedFiles($file, $field)];

                continue;
            }

            if (! $file instanceof UploadedFile) {
                continue;
            }

            $uploadedFiles[] = [
                'field' => $field,
                'name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType() ?: $file->getMimeType(),
                'size' => (new Measurement(
                    value: $file->getSize() ?: 0,
                    unit: DataSizeUnit::BYTES,
                ))->formattedValue,
            ];
        }

        return $uploadedFiles;
    }
}
