<?php

namespace NckRtl\Toolbar\Data\Configurations;

use Spatie\LaravelData\Data;

class LaravelConfig extends Data implements CollectorConfig
{
  public function __construct(
    public bool $enabled = true,
    public bool $version = true,
    public bool $environment = true,
    public bool $debug = true,
    public bool $timezone = true,
    public bool $locale = true,
  ) {
  }

  public function isEnabled(): bool
  {
    return $this->enabled;
  }
}
