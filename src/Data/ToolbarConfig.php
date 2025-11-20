<?php

namespace NckRtl\Toolbar\Data;

use NckRtl\Toolbar\Toolbar;
use Spatie\LaravelData\Data;
use NckRtl\Toolbar\Collectors\PhpCollector;
use NckRtl\Toolbar\Collectors\VueCollector;
use NckRtl\Toolbar\Collectors\ModelsCollector;
use NckRtl\Toolbar\Collectors\LaravelCollector;
use NckRtl\Toolbar\Collectors\QueriesCollector;
use NckRtl\Toolbar\Collectors\RequestCollector;
use NckRtl\Toolbar\Collectors\ProfilerCollector;
use NckRtl\Toolbar\Collectors\ResponseCollector;
use NckRtl\Toolbar\Collectors\CollectorInterface;
use NckRtl\Toolbar\Data\Configurations\CollectorConfig;

class ToolbarConfig extends Data
{
  public bool $debug;
  public array $collectors;

  public function __construct() {
    $this->debug(false)
      ->collectors([
        new ProfilerCollector,
        new RequestCollector,
        new ResponseCollector,
        new QueriesCollector,
        new LaravelCollector,
        new PhpCollector,
        new VueCollector,
        new ModelsCollector,
     ]);
  }

  public function debug(?bool $debug = null): self
  {
    if (is_null($debug)) {
      $this->debug = !$this->debug;
      return $this;
    }

    $this->debug = $debug;

    return $this;
  }

  public function collectors(?array $collectors = null): self
  {
    if(is_null($collectors) || empty($collectors)) {
      $this->collectors = [];
    }

    foreach($collectors as $collector) {
      $this->validateCollector($collector);
    }

    $this->collectors = $collectors;

    return $this;
  }

  private function validateCollector($collectorInstance): void
  {
     if(!in_array(CollectorInterface::class, class_implements($collectorInstance) ?: [])) {
      throw new \Exception('Collector '.$collectorInstance.' must implement '.CollectorInterface::class.' interface');
     }

     if(!$collectorInstance->config instanceof CollectorConfig) {
      throw new \Exception('Collector '.$collectorInstance.' config method should return an instance of '.CollectorConfig::class.' class');
     }
  }

  public function enabledCollectors(): array
  {
      return array_filter($this->collectors, fn ($collector) => $collector->config->enabled);
  }

  public function enable(): void
  {
    Toolbar::$enabled = true;
  }

  public function disable(): void
  {
    Toolbar::$enabled = false;
  }
}
