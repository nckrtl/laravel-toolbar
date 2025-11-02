<?php

namespace NckRtl\Toolbar\Collectors;

use Illuminate\Support\Facades\File;
use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Data\Configurations\InertiaConfig;
use NckRtl\Toolbar\Data\InertiaData;

class InertiaCollector extends Collector implements CollectorInterface
{
    /**
     * Cached version string (persists for process lifetime since package versions don't change)
     */
    private static ?string $cachedVersion = null;

    /**
     * Whether we've already checked for the version
     */
    private static bool $versionChecked = false;

    public function key(): string
    {
        return 'inertia';
    }

    public function configClass(): string
    {
        return InertiaConfig::class;
    }

    public function collectData(CollectorManager $collectorManager): ?InertiaData
    {
        return new InertiaData(
            version: $this->getVersion(),
        );
    }

    private function getVersion(): ?string
    {
        // Return cached result if already checked (version doesn't change during runtime)
        if (self::$versionChecked) {
            return self::$cachedVersion;
        }

        self::$versionChecked = true;

        $packageJsonPath = base_path('node_modules/@inertiajs/core/package.json');

        if (File::exists($packageJsonPath)) {
            $packageJson = json_decode(File::get($packageJsonPath), true);
            self::$cachedVersion = $packageJson['version'] ?? null;

            return self::$cachedVersion;
        }

        return null;
    }
}
