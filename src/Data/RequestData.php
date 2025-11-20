<?php

namespace NckRtl\Toolbar\Data;

use Illuminate\Foundation\Concerns\ResolvesDumpSource;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use ReflectionMethod;
use Spatie\LaravelData\Data;

class RequestData extends Data
{
    use ResolvesDumpSource;

    public string $route_name;

    public ?string $controller_action_editor_url;

    public ?string $route_editor_url;

    private ?RoutingRoute $route = null;

    public function __construct(
        public ?string $uuid = null,
        public string $method,
        public string $uri,
        public string $ip_address,
        public string $controller_action,
        public array $middleware,
        public ?int $memory = null,
        public ?int $duration = null,
    ) {
        $this->setRouteName();
        $this->setRouteEditorUrl();
        $this->setControllerActionEditorUrl();
    }

    private function setRouteName(): void
    {
        try {
            $this->route = Route::getRoutes()->match(
                Request::create(
                    $this->uri, $this->method
                )
            );

            $routeName = $this->route->getName();

            if ($routeName) {
                // Route has a name
                $this->route_name = $routeName;
            } else {
                // Route is not named
                $this->route_name = '-';
            }
        } catch (\Exception $e) {
            $this->route_name = '-';
        }
    }

    private function setRouteEditorUrl(): void
    {
        $action = $this->route->getAction();

        // If the route uses a closure, we can get its location directly
        if (isset($action['uses']) && $action['uses'] instanceof \Closure) {
            $reflection = new \ReflectionFunction($action['uses']);
            $file = $reflection->getFileName();
            $line = $reflection->getStartLine();
        }

        // For controller-based routes, try to find where the route is defined
        else {
            // Search through common route files
            $routeFile = $this->findRouteDefinition($this->route);

            if ($routeFile) {
                [$file, $line] = $routeFile;
            }
        }

        if (! isset($file) || ! isset($line) || ! $file || ! $line) {
            return;
        }

        $this->route_editor_url = $this->resolveSourceHref($file, $line) ?? null;
    }

    private function findRouteDefinition($route): bool|array
    {
        $routeName = $route->getName();
        $uri = $route->uri();
        $methods = $route->methods();

        $routesDirs = [
            base_path('routes'),
        ];

        foreach ($routesDirs as $routesDir) {
            if (is_dir($routesDir)) {
                $routeFiles = glob($routesDir.'/*.php');
                $routeFiles = array_merge($routeFiles);
                $routeFiles = array_unique($routeFiles);
            }
        }

        foreach ($routeFiles as $file) {
            if (! file_exists($file)) {
                continue;
            }

            $contents = file_get_contents($file);
            $lines = explode("\n", $contents);

            foreach ($lines as $lineNumber => $lineContent) {
                // Search for route name if it exists
                if ($routeName && str_contains($lineContent, "->name('{$routeName}')")) {
                    return [$file, $lineNumber + 1];
                }

                // Search for URI pattern
                if (str_contains($lineContent, "'{$uri}'") || str_contains($lineContent, "\"{$uri}\"")) {
                    // Check if this line contains a route method
                    foreach ($methods as $method) {
                        $method = strtolower($method);
                        if ($method !== 'head' && str_contains(strtolower($lineContent), "route::{$method}")) {
                            return [$file, $lineNumber + 1];
                        }
                    }
                }
            }
        }

        return false;
    }

    private function setControllerActionEditorUrl(): void
    {
        $controllerAction = $this->controller_action;

        [$controller, $method] = explode('@', $controllerAction);

        $reflection = new ReflectionMethod($controller, $method);
        $file = $reflection->getFileName();
        $line = $reflection->getStartLine();

        if (! $file || ! $line) {
            return;
        }

        $this->controller_action_editor_url = $this->resolveSourceHref($file, $line);
    }
}
