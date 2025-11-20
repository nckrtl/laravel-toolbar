<?php

namespace NckRtl\Toolbar\Collectors;

use NckRtl\Toolbar\Toolbar;
use NckRtl\Toolbar\Data\ModelData;
use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Data\Configurations\ModelsConfig;

class ModelsCollector extends Collector implements CollectorInterface
{
    public array $entries = [];

    public function key(): string
    {
        return 'models';
    }

    public function configClass(): string
    {
        return ModelsConfig::class;
    }

    public function collectData(CollectorManager $collectorManager): array
    {
        $this->setEntries($collectorManager);

        return $this->entries;
    }

    public function setEntries(CollectorManager $collectorManager): void
    {
        $toolbar = app(Toolbar::class);

        if (!$toolbar->telescopeIsInstalled() || ! $collectorManager->telescopeEntries->has('model')) {
            return;
        }

        $this->entries = $collectorManager->telescopeEntries->get('model')->toArray() ?? [];

        $this->entries = array_map(function ($entry) {
            return ModelData::from($entry['content']);
        }, $this->entries);
    }
}
