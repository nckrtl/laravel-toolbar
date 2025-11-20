<?php

namespace NckRtl\Toolbar\Collectors;

use Illuminate\Support\Facades\File;
use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Data\Configurations\VueConfig;
use NckRtl\Toolbar\Data\VueData;

class VueCollector extends Collector implements CollectorInterface
{
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
            version: $this->getVueVersion(),
        );
    }

    private function getVueVersion(): ?string
    {
        $packageJsonPath = base_path('node_modules/vue/package.json');

        if (File::exists($packageJsonPath)) {
            $packageJson = json_decode(File::get($packageJsonPath), true);

            return $packageJson['version'] ?? null;
        }

        return null;
    }
}
