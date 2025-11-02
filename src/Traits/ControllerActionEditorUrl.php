<?php

namespace NckRtl\Toolbar\Traits;

use Illuminate\Foundation\Concerns\ResolvesDumpSource;
use Illuminate\Support\Str;
use ReflectionMethod;

trait ControllerActionEditorUrl
{
    use ResolvesDumpSource;

    private function setControllerActionEditorUrl(?string $controllerActionOrfile = null, ?string $method = null, mixed $line = null): void
    {
        if (! $controllerActionOrfile && ! $line && ! $method) {
            return;
        }

        if (Str::contains($controllerActionOrfile, '@')) {
            [$controller, $method] = explode('@', $controllerActionOrfile);
            $reflection = new ReflectionMethod($controller, $method);
            $file = $reflection->getFileName();
            $line = $reflection->getStartLine();
        } else {
            $file = $controllerActionOrfile;
        }

        if (! $file || ! $line) {
            return;
        }

        $this->controller_action_editor_url = $this->resolveSourceHref($file, (int) $line);
    }
}
