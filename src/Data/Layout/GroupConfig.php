<?php

namespace NckRtl\Toolbar\Data\Layout;

use NckRtl\Toolbar\Data\Tools\ToolInterface;
use NckRtl\Toolbar\Enums\Layout\Section;
use Spatie\LaravelData\Data;

class GroupConfig extends Data
{
    public array $tools = [];

    public Section $section = Section::CENTER;

    public int $priority = 50;

    public function __construct(
        ?int $priority = null,
    ) {
        if ($priority !== null) {
            $this->priority = $priority;
        }
    }

    public function setTools(...$tools): self
    {
        foreach ($tools as $tool) {
            $this->tools[$tool->component()] = $tool;
        }

        return $this;
    }

    public function addTool(ToolInterface $tool): self
    {
        $this->tools[$tool->component()] = $tool;

        return $this;
    }

    public function section(Section $section): self
    {
        $this->section = $section;

        return $this;
    }

    public function priority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }
}
