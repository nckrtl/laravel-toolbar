<?php

declare(strict_types=1);

namespace NckRtl\Toolbar\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use NckRtl\Toolbar\Data\RequestProfileSummaryData;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[IsReadOnly]
class GetRequestDataTool extends Tool
{
    public function shouldRegister(Request $request): bool
    {
        return config('toolbar.enabled', false) && $this->requestDataRoutesEnabled();
    }

    /**
     * The tool's description.
     */
    protected string $description = 'Does a http request and return all data collected during the full request lifecycle. This data can be used to analyze the performance and identify bottlenecks like slow request stages and slow database queries';

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        if (! $this->requestDataRoutesEnabled()) {
            return Response::error('Request data is not available in this environment.');
        }

        $validated = $request->validate([
            'route' => 'required|string',
            'type' => 'required|string|in:route_name,url,uri',
            'method' => 'nullable|string|in:GET,POST,PUT,DELETE,PATCH,OPTIONS,HEAD',
            'auth_mode' => 'nullable|string|in:guest,first-user,user',
            'user' => 'nullable|string',
        ], [
            'route.required' => 'You must specify the route name, url or uri.',
            'type.required' => 'You must specify what route type to use. Valid types are: route_name, url, uri.',
            'method.in' => 'You must specify a valid method. Valid methods are: GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD.',
        ]);

        $route = $validated['route'];
        $type = $validated['type'];
        $method = $validated['method'];

        $route = $this->findRoute($route, $type, $method);

        if (! $route) {
            return Response::error('No route found for route: '.$route.', type: '.$type.', method: '.$method);
        }

        $requestId = (string) Str::uuid();
        $authMode = $validated['auth_mode'] ?? 'guest';
        $headers = [
            'X-REQUEST-ID' => $requestId,
            'X-TOOLBAR-AUTH' => $authMode,
        ];

        if (($validated['user'] ?? null) !== null) {
            $headers['X-TOOLBAR-USER'] = $validated['user'];
        }

        try {
            $response = Http::withoutVerifying()
                ->withHeaders($headers)
                ->send(
                    $method ?? 'GET',
                    config('app.url').'/'.ltrim($route->uri, '/'),
                );
        } catch (\Exception $e) {
            return Response::error('Failed to make request to route: '.$route->uri.', method: '.$method.', error: '.$e->getMessage());
        }

        if (! $response->successful()) {
            return Response::error('Failed to make request to route: '.$route->uri.', method: '.$method.', status: '.$response->status());
        }

        $payload = Cache::get('laravel-toolbar-request-data-'.$requestId);

        if (! is_array($payload) || $payload === []) {
            return Response::error('No request data found for request id: '.$requestId);
        }

        return Response::json([
            'summary' => RequestProfileSummaryData::fromPayload($payload),
            'raw' => $payload,
        ]);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'route' => $schema->string()
                ->description('The route name, url or uri of the route to get data for.')
                ->required(),
            'type' => $schema->string()
                ->enum(['route_name', 'url', 'uri'])
                ->description('The type of the route to get data for.')
                ->required(),
            'method' => $schema->string()
                ->enum(['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD'])
                ->description('The method of the route to get data for.')
                ->nullable(),
            'auth_mode' => $schema->string()
                ->enum(['guest', 'first-user', 'user'])
                ->description('Authentication mode for the profiled request.')
                ->nullable(),
            'user' => $schema->string()
                ->description('User primary key to authenticate when auth_mode is user.')
                ->nullable(),
        ];
    }

    private function findRoute(string $route, string $type, ?string $method): ?Route
    {
        $routes = RouteFacade::getRoutes();

        $appUrl = config('app.url');

        try {
            if ($type === 'route_name') {
                return $routes->getByName($route);
            }

            if ($type === 'url') {
                return $routes->match(HttpRequest::create($route, $method ?? 'GET'));
            }

            if ($type === 'uri') {
                $fullUrl = rtrim($appUrl, '/').'/'.ltrim($route, '/');

                return $routes->match(HttpRequest::create($fullUrl, $method ?? 'GET'));
            }
        } catch (NotFoundHttpException) {
            return null;
        }

        return null;
    }

    private function requestDataRoutesEnabled(): bool
    {
        return app()->environment(config('toolbar.request_data_allowed_environments', ['local', 'development']));
    }
}
