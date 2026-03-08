<?php

namespace NckRtl\Toolbar\Data;

use NckRtl\Toolbar\Enums\QueryType;
use NckRtl\Toolbar\Measurement;
use NckRtl\Toolbar\Traits\ControllerActionEditorUrl;
use Spatie\LaravelData\Data;

class QueryData extends Data
{
    use ControllerActionEditorUrl;

    public ?string $editor_url = null;

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
        if (preg_match('/(?:select \* from|update|delete from)\s+[`"\[]?sessions[`"\]]?\s/i', $this->sql)) {
            $this->type = QueryType::SESSION;
        }
    }
}
