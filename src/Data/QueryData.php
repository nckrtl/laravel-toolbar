<?php

namespace NckRtl\Toolbar\Data;

use NckRtl\Toolbar\Enums\QueryType;
use NckRtl\Toolbar\Measurement;
use NckRtl\Toolbar\Traits\ControllerActionEditorUrl;
use Spatie\LaravelData\Data;

class QueryData extends Data
{
    use ControllerActionEditorUrl;

    public ?string $controller_action_editor_url = null;

    public ?QueryType $type = null;

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
        $this->setType();
    }

    private function setType(): void
    {
        if (str_contains($this->sql, 'select * from "sessions" where "id" =') ||
            str_contains($this->sql, 'update "sessions" set "payload" =') ||
            str_contains($this->sql, 'delete from "sessions" where')
        ) {
            $this->type = QueryType::SESSION;
        }
    }
}
