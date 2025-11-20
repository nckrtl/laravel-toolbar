<?php

namespace NckRtl\Toolbar\Data\Configurations;

use Spatie\LaravelData\Data;
use NckRtl\Toolbar\Enums\DataProvider;
use Spatie\LaravelData\Attributes\Validation\Rule;

class RequestConfig extends Data implements CollectorConfig
{
  public function __construct(
    public bool $enabled = true,
    public ?DataProvider $dataProvider = null,
  ) {
  }

  public function isEnabled(): bool
  {
    return $this->enabled;
  }
}