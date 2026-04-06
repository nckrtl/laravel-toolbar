<?php

namespace NckRtl\Toolbar\Collectors;

use Illuminate\Http\UploadedFile;
use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Data\Configurations\RequestConfig;
use NckRtl\Toolbar\Data\RequestData;
use NckRtl\Toolbar\Enums\DataSizeUnit;
use NckRtl\Toolbar\Measurement;

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

        return new RequestData(
            is_inertia: $request->header('X-Inertia') === 'true',
            uuid: null,
            method: $request->method(),
            uri: $request->getPathInfo(),
            ip_address: $request->ip(),
            controller_action: $route?->getActionName() ?? '-',
            middleware: array_values(optional($route)->gatherMiddleware() ?? []),
            duration: null,
            route_uri_pattern: $route?->uri(),
            route_parameters: $this->normalizeValues($route?->parameters() ?? []),
            query_parameters: $this->normalizeValues($request->query()),
            headers: $this->normalizeHeaders($request->headers->all()),
            uploaded_files: $this->summarizeUploadedFiles($request->allFiles()),
        );
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
