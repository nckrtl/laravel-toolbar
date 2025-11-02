<?php

namespace NckRtl\Toolbar\Enums;

interface Unit
{
    public function abbreviation(): string;

    public function factor(): int;

    public function convertValueTo(int|float $value, Unit $convertToUnit): int|float;

    public function formatMaxFractionDigits(int $maxFractionDigits, int|float $value, ?Unit $formatToUnit = null): string;
}
