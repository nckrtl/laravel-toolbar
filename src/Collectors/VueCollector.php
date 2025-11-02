<?php

namespace NckRtl\Toolbar\Collectors;

use Illuminate\Foundation\Concerns\ResolvesDumpSource;
use Illuminate\Support\Facades\File;
use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Data\Configurations\VueConfig;
use NckRtl\Toolbar\Data\VueData;

class VueCollector extends Collector implements CollectorInterface
{
    use ResolvesDumpSource;

    /**
     * Cached version string (persists for process lifetime since package versions don't change)
     */
    private static ?string $cachedVersion = null;

    /**
     * Whether we've already checked for the version
     */
    private static bool $versionChecked = false;

    /**
     * Cached editor URL (persists for process lifetime)
     */
    private static ?string $cachedEditorUrl = null;

    /**
     * Whether we've already checked for the editor URL
     */
    private static bool $editorUrlChecked = false;

    public function key(): string
    {
        return 'vue';
    }

    public function configClass(): string
    {
        return VueConfig::class;
    }

    public function collectData(CollectorManager $collectorManager): ?VueData
    {
        return new VueData(
            version: $this->getVersion(),
            version_editor_url: $this->getVersionEditorUrl(),
        );
    }

    private function getVersion(): ?string
    {
        // Return cached result if already checked (version doesn't change during runtime)
        if (self::$versionChecked) {
            return self::$cachedVersion;
        }

        self::$versionChecked = true;

        $packageJsonPath = base_path('node_modules/vue/package.json');

        if (File::exists($packageJsonPath)) {
            $packageJson = json_decode(File::get($packageJsonPath), true);
            self::$cachedVersion = $packageJson['version'] ?? null;

            return self::$cachedVersion;
        }

        return null;
    }

    private function getVersionEditorUrl(): ?string
    {
        // Return cached result if already checked
        if (self::$editorUrlChecked) {
            return self::$cachedEditorUrl;
        }

        self::$editorUrlChecked = true;

        $packageJsonPath = base_path('package.json');

        if (! file_exists($packageJsonPath)) {
            return null;
        }

        $contents = file($packageJsonPath);

        foreach ($contents as $index => $line) {
            if (str_contains($line, '"vue"')) {
                self::$cachedEditorUrl = $this->resolveSourceHref($packageJsonPath, $index + 1);

                return self::$cachedEditorUrl;
            }
        }

        return null;
    }
}
