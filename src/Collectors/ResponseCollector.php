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

        $response = $collectorManager->response;

        return new ResponseData(
            status_code: $response->getStatusCode(),
            headers: $response->headers->all(),
            size: new Measurement(
                value: strlen($response->getContent()),
                unit: DataSizeUnit::BYTES
            )->formatValue(),
            content_type: $response->headers->get('Content-Type'),
            redirect_to: $response->headers->get('Location'),
            cookies: array_map(
                static fn ($cookie) => [
                    'name' => $cookie->getName(),
                    'value' => $cookie->getValue(),
                    'path' => $cookie->getPath(),
                    'domain' => $cookie->getDomain(),
                    'same_site' => $cookie->getSameSite(),
                    'secure' => $cookie->isSecure(),
                    'http_only' => $cookie->isHttpOnly(),
                    'expires_at' => $cookie->getExpiresTime() > 0 ? date(DATE_ATOM, $cookie->getExpiresTime()) : null,
                ],
                $response->headers->getCookies(),
            ),
        );
    }
}
