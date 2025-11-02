<?php

namespace NckRtl\Toolbar\Collectors;

use Illuminate\Support\Facades\File;
use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Data\Configurations\TailwindConfig;
use NckRtl\Toolbar\Data\TailwindData;

class TailwindCollector extends Collector implements CollectorInterface
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
        return 'tailwind';
    }

    public function configClass(): string
    {
        return TailwindConfig::class;
    }

    public function collectData(CollectorManager $collectorManager): ?TailwindData
    {
        return new TailwindData(
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

        // Check for Tailwind v4 Vite plugin first
        $vitePluginPath = base_path('node_modules/@tailwindcss/vite/package.json');
        if (File::exists($vitePluginPath)) {
            $packageJson = json_decode(File::get($vitePluginPath), true);
            if (isset($packageJson['version'])) {
                self::$cachedVersion = $packageJson['version'];

                return self::$cachedVersion;
            }
        }

        // Fall back to main tailwindcss package (v3 and earlier)
        $mainPackagePath = base_path('node_modules/tailwindcss/package.json');
        if (File::exists($mainPackagePath)) {
            $packageJson = json_decode(File::get($mainPackagePath), true);
            if (isset($packageJson['version'])) {
                self::$cachedVersion = $packageJson['version'];

                return self::$cachedVersion;
            }
        }

        return null;
    }
}
