<?php

declare(strict_types=1);

namespace NckRtl\Toolbar\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use NckRtl\Toolbar\Data\RequestProfileSummaryData;

class RequestDataController
{
    public function __invoke(string $requestId): JsonResponse
    {
        if (! app()->environment(config('toolbar.request_data_allowed_environments', ['local', 'development']))) {
            abort(404);
        }

        $payload = Cache::get('laravel-toolbar-request-data-'.$requestId);

        if (! is_array($payload) || $payload === []) {
            abort(404);
        }

        return response()->json([
            'summary' => RequestProfileSummaryData::fromPayload($payload),
            'raw' => $payload,
        ]);
    }
}
