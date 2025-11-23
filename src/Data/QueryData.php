<?php

namespace NckRtl\Toolbar\Data;

use Spatie\LaravelData\Data;
use NckRtl\Toolbar\Measurement;
use NckRtl\Toolbar\Traits\ControllerActionEditorUrl;

class QueryData extends Data
{
    use ControllerActionEditorUrl;

    public ?string $controller_action_editor_url = null;

    public function __construct(
        public string $hash,
        public string $sql,
        public array $bindings,
        public float $duration,
        public string $connection,
        public string $driver,
        public bool $is_duplicate,
        public bool $is_slow,
        public float $percentage = 0,
        public float $offset = 0,
        public ?Measurement $memory_used = null,
        public ?string $file = null,
        public ?int $line = null,

    ) {
        $this->setControllerActionEditorUrl($this->file, null, $this->line);
    }
}
