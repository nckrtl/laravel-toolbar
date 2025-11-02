<?php

namespace NckRtl\Toolbar\Collectors;

use NckRtl\Toolbar\CollectorManager;

interface CollectorInterface
{
    public function key(): string;

    public function configClass(): string;

    public function collectData(CollectorManager $collectorManager): mixed;
}
