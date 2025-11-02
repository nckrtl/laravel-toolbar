<?php

namespace NckRtl\Toolbar\Collectors;

use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Data\Configurations\ResponseConfig;
use NckRtl\Toolbar\Data\ResponseData;
use NckRtl\Toolbar\Enums\DataSizeUnit;
use NckRtl\Toolbar\Measurement;

class ResponseCollector extends Collector implements CollectorInterface
{
    public function key(): string
    {
        return 'response';
    }

    public function configClass(): string
    {
        return ResponseConfig::class;
    }

    public function collectData(CollectorManager $collectorManager): ?ResponseData
    {
        if ($collectorManager->response === null) {
            return null;
        }

        return new ResponseData(
            status_code: $collectorManager->response->getStatusCode(),
            headers: $collectorManager->response->headers->all(),
            size: new Measurement(
                value: strlen($collectorManager->response->getContent()),
                unit: DataSizeUnit::BYTES
            )->formatValue()
        );
    }
}
