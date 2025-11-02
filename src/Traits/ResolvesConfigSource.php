<?php

namespace NckRtl\Toolbar\Traits;

use Illuminate\Foundation\Concerns\ResolvesDumpSource;

trait ResolvesConfigSource
{
    use ResolvesDumpSource;

    private function getConfigEditorUrl(string $key): ?string
    {
        $location = $this->findConfigLocation($key);

        if (! $location) {
            return null;
        }

        return $this->resolveSourceHref($location['file'], $location['line']);
    }

    private function findConfigLocation(string $key): ?array
    {
        $parts = explode('.', $key);
        $file = array_shift($parts);
        $configPath = config_path($file.'.php');

        if (! file_exists($configPath)) {
            return null;
        }

        $searchKey = end($parts); // The final key we're looking for
        $contents = file($configPath);

        foreach ($contents as $index => $line) {
            // Match 'key' => or "key" =>
            if (preg_match("/['\"]".preg_quote($searchKey, '/')."['\"]\s*=>/", $line)) {
                return [
                    'file' => $configPath,
                    'line' => $index + 1,
                ];
            }
        }

        return null;
    }
}
