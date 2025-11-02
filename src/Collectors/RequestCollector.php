<?php

namespace NckRtl\Toolbar\Collectors;

use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Data\Configurations\RequestConfig;
use NckRtl\Toolbar\Data\RequestData;

/**
 * @property RequestConfig $config
 */
class RequestCollector extends Collector implements CollectorInterface
{
    public function key(): string
    {
        return 'request';
    }

    public function configClass(): string
    {
        return RequestConfig::class;
    }

    public function collectData(CollectorManager $collectorManager): ?RequestData
    {
        $request = request();

        return new RequestData(
            is_inertia: $request->header('X-Inertia') === 'true',
            uuid: null,
            method: $request->method(),
            uri: $request->getPathInfo(),
            ip_address: $request->ip(),
            controller_action: $request->route()?->getActionName() ?? '-',
            middleware: array_values(optional($request->route())->gatherMiddleware() ?? []),
            duration: null,

        );
    }
}
