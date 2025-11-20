<?php

namespace NckRtl\Toolbar\Enums;

use NckRtl\Toolbar\Enums\Unit;

enum DataSizeUnit: string implements Unit
{
    case BYTES = 'bytes';
    case KILOBYTES = 'kilobytes';
    case MEGABYTES = 'megabytes';
    case GIGABYTES = 'gigabytes';
    case TERABYTES = 'terabytes';
    case PETABYTES = 'petabytes';

    private function abbreviations(): array
    {
        return [
            self::BYTES->value => 'B',
            self::KILOBYTES->value => 'KB',
            self::MEGABYTES->value => 'MB',
            self::GIGABYTES->value => 'GB',
            self::TERABYTES->value => 'TB',
            self::PETABYTES->value => 'PB',
        ];
    }

    public function abbreviation(): string
    {
        try {
            return $this->abbreviations()[$this->value];
        } catch (\Exception $e) {
            throw new \Exception('No abbreviation found for data size unit: '.$this->value);
        }
    }

    public function factors(): array
    {
        return [
            self::BYTES->value => 1,
            self::KILOBYTES->value => pow(1024, 1),
            self::MEGABYTES->value => pow(1024, 2),
            self::GIGABYTES->value => pow(1024, 3),
            self::TERABYTES->value => pow(1024, 4),
            self::PETABYTES->value => pow(1024, 5),
        ];
    }

    public function factor(): int
    {
        return $this->factors()[$this->value];
    }

    /**
     * @param  DataSizeUnit  $convertToUnit
     *
     * @throws \Exception
     */
    public function convertValueTo(int|float $value, Unit $convertToUnit): int|float
    {
        if ($this->factor() > $convertToUnit->factor()) {
            return $value / $this->factor() * $convertToUnit->factor();
        }

        return $value * $this->factor() / $convertToUnit->factor();
    }

    public function formatMaxFractionDigits(int $maxFractionDigits, int|float $value, ?Unit $formatToUnit = null): string
    {
        $isNegative = $value < 0;

        if ($isNegative) {
            $value = abs($value);
        }

        if ($formatToUnit) {
            return ($isNegative ? '-' : '').round($formatToUnit->convertValueTo($value, $formatToUnit), $maxFractionDigits).$formatToUnit->abbreviation();
        }

        if ($value > 0 && $value < 1) {
            $value = $value * $this->factor();
        }

        $abbreviations = array_values($this->abbreviations());

        $pow = floor(($value ? log($value) : 0) / log(1024));

        $pow = min($pow, count($abbreviations) - 1);

        return ($isNegative ? '-' : '').round($value / pow(1024, $pow), $maxFractionDigits).' '.$abbreviations[$pow];
    }
}
