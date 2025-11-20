<?php

namespace NckRtl\Toolbar\Data\Configurations;

use Spatie\LaravelData\Data;

class ModelsConfig extends Data implements CollectorConfig
{
  public function __construct(
    public bool $enabled = true,
  ) {
  }

  public function isEnabled(): bool
  {
    return $this->enabled;
  }
}