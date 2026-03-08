<?php

namespace NckRtl\Toolbar\Data;

use NckRtl\Toolbar\Traits\ControllerActionEditorUrl;
use Spatie\LaravelData\Data;

class ModelSourceData extends Data
{
    use ControllerActionEditorUrl;

    public ?string $controller_action_editor_url = null;

    public function __construct(
        public ?string $file,
        public ?int $line,
        public int $count,
    ) {
        $this->setControllerActionEditorUrl($this->file, null, $this->line);
    }
}
