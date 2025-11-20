<?php

namespace NckRtl\Toolbar\Mcp\Tools;

use Illuminate\Http\Request as HttpRequest;
use Illuminate\JsonSchema\JsonSchema;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class GetRequestDataTool extends Tool
{
    public function shouldRegister(Request $request): bool
    {
        return config('toolbar.enabled', false);
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
        $validated = $request->validate([
            'route' => 'required|string',
            'type' => 'required|string|in:route_name,url,uri',
            'method' => 'nullable|string|in:GET,POST,PUT,DELETE,PATCH,OPTIONS,HEAD',
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

        $mcpRequestId = Str::uuid();

        try {
            $response = Http::send(
                $method ?? 'GET',
                config('app.url').'/'.ltrim($route->uri, '/'),
                [
                    'headers' => [
                        'X-MCP-ID' => (string) $mcpRequestId,
                    ],
                ]
            );
        } catch (\Exception $e) {
            return Response::error('Failed to make request to route: '.$route->uri.', method: '.$method.', error: '.$e->getMessage());
        }

        if (! $response->successful()) {
            return Response::error('Failed to make request to route: '.$route->uri.', method: '.$method.', status: '.$response->status());
        }

        $requestData = Cache::get('laravel-toolbar-request-data-'.$mcpRequestId);

        if (empty($requestData)) {
            return Response::error('No request data found for request id: '.$mcpRequestId);
        }

        return Response::json($requestData);
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
        ];
    }

    private function findRoute(string $route, string $type, ?string $method): ?Route
    {
        $routes = RouteFacade::getRoutes();

        if ($type === 'route_name') {
            return $routes->getByName($route);
        }

        if ($type === 'url') {
            $uri = explode(config('app.url'), $route);
            $uri = $uri[1];

            return $routes->match(HttpRequest::create($uri, $method ?? 'GET'));
        }

        if ($type === 'uri') {
            return $routes->match(HttpRequest::create($route, $method ?? 'GET'));
        }

        return null;
    }
}
