<?php

namespace NckRtl\Toolbar\Enums;

enum TimeUnit: string implements Unit
{
    case MICROSECONDS = 'microseconds';
    case MILLISECONDS = 'milliseconds';
    case SECONDS = 'seconds';
    case MINUTES = 'minutes';
    case HOURS = 'hours';
    case DAYS = 'days';
    case WEEKS = 'weeks';
    case MONTHS = 'months';
    case YEARS = 'years';

    private function abbreviations(): array
    {
        return [
            self::MICROSECONDS->value => 'µs',
            self::MILLISECONDS->value => 'ms',
            self::SECONDS->value => 's',
            self::MINUTES->value => 'm',
            self::HOURS->value => 'h',
            self::DAYS->value => 'd',
            self::WEEKS->value => 'w',
            self::MONTHS->value => 'M',
            self::YEARS->value => 'y',
        ];
    }

    public function abbreviation(): string
    {
        try {
            return $this->abbreviations()[$this->value];
        } catch (\Exception $e) {
            throw new \Exception('No abbreviation found for time unit: '.$this->value);
        }
    }

    public function factors(): array
    {
        return [
            self::MICROSECONDS->value => 1,
            self::MILLISECONDS->value => pow(10, 3),
            self::SECONDS->value => pow(10, 6),
            self::MINUTES->value => pow(10, 6) * 60,
            self::HOURS->value => pow(10, 6) * 60 * 60,
            self::DAYS->value => pow(10, 6) * 60 * 60 * 24,
            self::WEEKS->value => pow(10, 6) * 60 * 60 * 24 * 7,
            self::MONTHS->value => pow(10, 6) * 60 * 60 * 24 * 30,
            self::YEARS->value => pow(10, 6) * 60 * 60 * 24 * 365,
        ];
    }

    public function factor(): int
    {
        return $this->factors()[$this->value];
    }

    /**
     * @param  TimeUnit  $convertToUnit
     *
     * @throws \Exception
     */
    public function convertValueTo(int|float $value, Unit $convertToUnit): int|float
    {
        if ($this === $convertToUnit) {
            return $value;
        }

        if ($this->factor() > $convertToUnit->factor()) {
            return $value * $this->factor() / $convertToUnit->factor();
        }

        return $value / $this->factor() * $convertToUnit->factor();
    }

    public function formatMaxFractionDigits(int $maxFractionDigits, int|float $value, ?Unit $formatToUnit = null): string
    {
        if ($value == 0) {
            return '0'.array_values($this->abbreviations())[0];
        }

        if ($value < 0) {
            throw new \Exception('Value cannot be negative: '.$value);
        }

        if ($formatToUnit) {
            return round($formatToUnit->convertValueTo($value, $formatToUnit), $maxFractionDigits).$formatToUnit->abbreviation();
        }

        $units = self::cases();
        $abbreviations = array_values($this->abbreviations());

        $valueInBaseUnit = $value * $this->factor();

        // Find the best fitting unit
        $bestUnitIndex = 0;

        foreach ($units as $index => $unit) {
            $unitFactor = $unit->factor();

            if ($valueInBaseUnit > $unitFactor) {
                continue;
            }

            if ($valueInBaseUnit / $unitFactor < 1) {
                $bestUnitIndex = $index - 1;
                break;
            }
        }

        // The smallest time unit is in microseconds, when a value is smaller than 1 microsecond
        // the best unit index will be -1, so we need to set it to 0 to avoid an exception
        if ($valueInBaseUnit > 0 && $bestUnitIndex == -1) {
            $bestUnitIndex = 0;
        }

        $selectedUnit = $units[$bestUnitIndex];
        $convertedValue = $valueInBaseUnit / $selectedUnit->factor();

        return round($convertedValue, $maxFractionDigits).$abbreviations[$bestUnitIndex];
    }
}
