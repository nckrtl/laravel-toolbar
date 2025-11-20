<?php

namespace NckRtl\Toolbar\Collectors;

use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Data\Configurations\CollectorConfig;

interface CollectorInterface
{
    public function key(): string;

    public function configClass(): string;

    public function collectData(CollectorManager $collectorManager): mixed;
}
