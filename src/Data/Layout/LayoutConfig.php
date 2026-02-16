<?php

namespace NckRtl\Toolbar\Data\Layout;

use NckRtl\Toolbar\Enums\Layout\Section;
use Spatie\LaravelData\Data;

class LayoutConfig extends Data
{
    public array $sections = [
        Section::LEFT->value => [],
        Section::CENTER->value => [],
        Section::RIGHT->value => [],
    ];

    public function __construct(

    ) {
    }

    public function addGroup(GroupConfig $groupConfig, bool $prepend = false): self
    {
        if ($prepend) {
            array_unshift($this->sections[$groupConfig->section->value], $groupConfig);
        } else {
            $this->sections[$groupConfig->section->value][] = $groupConfig;
        }

        return $this;
    }

    /**
     * Override toArray to sort groups by priority within each section.
     * Lower priority values render first.
     */
    public function toArray(): array
    {
        $sortedSections = [];

        foreach ($this->sections as $sectionKey => $groups) {
            $sortedGroups = $groups;
            usort($sortedGroups, fn (GroupConfig $a, GroupConfig $b): int => $a->priority <=> $b->priority);
            $sortedSections[$sectionKey] = array_map(fn (GroupConfig $group) => $group->toArray(), $sortedGroups);
        }

        return [
            'sections' => $sortedSections,
        ];
    }
}
