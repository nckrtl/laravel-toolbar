<?php

namespace NckRtl\Toolbar\Data\CollectorConfigurations;

use Spatie\LaravelData\Data;
use NunoMaduro\Collision\Provider;

class RequestConfurationData extends Data
{
  public function __construct(
    public bool $enabled = true,
    public ?Provider $provider = null,
  ) {
      if(! $this->enabled || ! $this->provider) {
        return;
      }

      if($this->provider !== Provider::Telescope) {
        throw new \Exception('Request collector is configured to use the '.$this->provider->value.' provider, but this provider is not supported.');
      }

      if($this->provider === Provider::Telescope && ! class_exists('Telescope')) {
        throw new \Exception('Request collector is configured to use the Telescope provider, but Telescope is not installed.');
      }
  }
}
