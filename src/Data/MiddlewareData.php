<?php

namespace NckRtl\Toolbar\Data;

use NckRtl\Toolbar\Traits\ControllerActionEditorUrl;
use Spatie\LaravelData\Data;

class MiddlewareData extends Data
{
    use ControllerActionEditorUrl;

    public ?string $editor_url = null;

    public function __construct(
        public string $class,
        public ?string $file = null,
        public bool $has_outbound = false,
    ) {
        if ($this->file) {
            $this->setControllerActionEditorUrl($this->file, line: 1);
        }
    }
}
