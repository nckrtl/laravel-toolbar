<?php

namespace NckRtl\Toolbar\Collectors;

use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Data\Configurations\ModelsConfig;
use NckRtl\Toolbar\Observers\ModelObserver;
use NckRtl\Toolbar\Toolbar;

class ModelsCollector extends Collector implements CollectorInterface
{
    public array $models = [];

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

        return $this->models;
    }

    public function setEntries(CollectorManager $collectorManager): void
    {
        $toolbar = app(Toolbar::class);

        $modelObserver = $toolbar->config->getObserver(ModelObserver::class);

        $this->models = $modelObserver->hydrationEntries;
    }
}
