<?php

namespace NckRtl\Toolbar\Collectors;

use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Data\Configurations\PhpConfig;
use NckRtl\Toolbar\Data\PhpData;

class PhpCollector extends Collector implements CollectorInterface
{
    public function key(): string
    {
        return 'php';
    }

    public function configClass(): string
    {
        return PhpConfig::class;
    }

    public function collectData(CollectorManager $collectorManager): ?PhpData
    {
        return new PhpData(
            version: phpversion(),
            memory_limit: ini_get('memory_limit'),
            max_execution_time: ini_get('max_execution_time'),
        );
    }
}
