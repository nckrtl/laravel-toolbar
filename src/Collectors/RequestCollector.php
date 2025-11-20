<?php

namespace NckRtl\Toolbar\Collectors;

use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Data\RequestData;
use NckRtl\Toolbar\Enums\DataProvider;
use NckRtl\Toolbar\Data\Configurations\RequestConfig;

class RequestCollector extends Collector implements CollectorInterface
{
    public array $request;

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

        if($this->config->dataProvider && $this->config->dataProvider === DataProvider::Telescope && ! empty($collectorManager->telescopeEntries)) {
            return $this->telescopeProvidedRequestData($collectorManager);
        }

        $request = request();

        if(! $request) {
            return null;
        }

        return new RequestData(
            uuid: null,
            method: $request->method(),
            uri: $request->uri(),
            ip_address: $request->ip(),
            controller_action: $request->route()?->getActionName() ?? '-',
            middleware: array_values(optional($request->route())->gatherMiddleware() ?? []),
            duration: null,
        );
    }

    private function telescopeProvidedRequestData(CollectorManager $collectorManager): RequestData
    {
        $this->setRequest($collectorManager);

        return new RequestData(
            uuid: $this->request['uuid'] ?? '',
            method: $this->request['method'] ?? '',
            uri: $this->request['uri'] ?? '',
            ip_address: $this->request['ip_address'] ?? '',
            controller_action: $this->request['controller_action'] ?? '',
            middleware: $this->request['middleware'] ?? [],
            memory: $this->request['memory'] ?? 0,
            duration: $this->request['duration'] ?? 0,
        );
    }

    private function setRequest(CollectorManager $collectorManager): void
    {
        $telescopeEntry = $collectorManager->telescopeEntries->get('request')->first() ?? null;

        $this->request = array_merge($telescopeEntry, $telescopeEntry['content']);
    }
}
