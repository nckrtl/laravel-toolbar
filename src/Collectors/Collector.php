<?php

namespace NckRtl\Toolbar\Collectors;

use NckRtl\Toolbar\Data\Configurations\CollectorConfig;
use Spatie\LaravelData\Data;

abstract class Collector extends Data
{
    abstract public function configClass(): string;

    abstract public function key(): string;

    public function __construct(public ?CollectorConfig $config = null)
    {
        $configClass = $this->configClass();

        if (! $configClass) {
            throw new \Exception('Collector '.$this->key().' config class must be set.');
        }

        if (! new $configClass instanceof CollectorConfig) {
            throw new \Exception('Collector '.$this->key().' configClass property must be an instance of '.CollectorConfig::class.' class.');
        }

        if (! empty($this->config) && ! $this->config instanceof $configClass) {
            throw new \Exception('Collector '.$this->key().' config must be an instance of '.$configClass.' class.');
        }

        $this->config ??= new $configClass;
    }
}
