<?php

namespace App\Support;

use Carbon\CarbonInterface;
use DateTimeInterface;
use IntlDateFormatter;
use NumberFormatter;

class Format
{
    public static function timezone(): string
    {
        return (string) config('app.timezone', 'America/Bogota');
    }

    public static function locale(): string
    {
        return (string) config('app.intl_locale', 'es_CO');
    }

    public static function appLocale(): string
    {
        return (string) config('app.locale', 'es');
    }

    public static function currency(): string
    {
        return (string) config('app.currency', 'COP');
    }

    /**
     * Formats money using Intl if available.
     *
     * Assumes amounts are expressed in major units (e.g., COP pesos, not cents).
     */
    public static function money(float|int|string|null $amount, ?string $currency = null, ?int $decimals = null): string
    {
        $currency = $currency ?: self::currency();
        $amount = is_numeric($amount) ? (float) $amount : 0.0;
        $decimals = $decimals ?? ($currency === 'COP' ? 0 : 2);

        if (class_exists(NumberFormatter::class)) {
            $formatter = new NumberFormatter(self::locale(), NumberFormatter::CURRENCY);
            $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $decimals);

            $formatted = $formatter->formatCurrency($amount, $currency);
            if (is_string($formatted)) {
                return $formatted;
            }
        }

        // Fallback: Colombian-style separators by default.
        return ($currency === 'COP' ? '$' : ($currency . ' ')) . number_format($amount, $decimals, ',', '.');
    }

    /**
     * Formats a date in numeric Colombian format (dd/MM/yyyy).
     */
    public static function date(DateTimeInterface|string|null $value, string $pattern = 'dd/MM/yyyy'): string
    {
        $dt = self::toDateTime($value);
        if (!$dt) {
            return '—';
        }

        return self::intlFormat($dt, $pattern, IntlDateFormatter::NONE);
    }

    /**
     * Formats a date+time in numeric Colombian format (dd/MM/yyyy HH:mm).
     */
    public static function datetime(DateTimeInterface|string|null $value, string $pattern = 'dd/MM/yyyy HH:mm'): string
    {
        $dt = self::toDateTime($value);
        if (!$dt) {
            return '—';
        }

        return self::intlFormat($dt, $pattern, IntlDateFormatter::SHORT);
    }

    /**
     * Formats a date with Spanish month name (e.g., 11 ene 2026).
     */
    public static function dateLong(DateTimeInterface|string|null $value): string
    {
        $dt = self::toDateTime($value);
        if (!$dt) {
            return '—';
        }

        // Prefer Intl (proper localization). Fallback to Carbon translatedFormat.
        if (class_exists(IntlDateFormatter::class)) {
            return self::intlFormat($dt, 'd MMM y', IntlDateFormatter::NONE);
        }

        if ($dt instanceof CarbonInterface) {
            return $dt->copy()->locale(self::appLocale())->translatedFormat('j M Y');
        }

        return $dt->format('d/m/Y');
    }

    public static function datetimeLong(DateTimeInterface|string|null $value): string
    {
        $dt = self::toDateTime($value);
        if (!$dt) {
            return '—';
        }

        if (class_exists(IntlDateFormatter::class)) {
            return self::intlFormat($dt, 'd MMM y, HH:mm', IntlDateFormatter::SHORT);
        }

        if ($dt instanceof CarbonInterface) {
            return $dt->copy()->locale(self::appLocale())->translatedFormat('j M Y, H:i');
        }

        return $dt->format('d/m/Y H:i');
    }

    /**
     * Formats a month name in Spanish (e.g., "enero").
     */
    public static function monthName(DateTimeInterface|string|null $value, string $pattern = 'MMMM'): string
    {
        $dt = self::toDateTime($value);
        if (!$dt) {
            return '—';
        }

        if (class_exists(IntlDateFormatter::class)) {
            return self::intlFormat($dt, $pattern, IntlDateFormatter::NONE);
        }

        if ($dt instanceof CarbonInterface) {
            return $dt->copy()->locale(self::appLocale())->translatedFormat('F');
        }

        return $dt->format('m');
    }

    /**
     * Formats month+year in Spanish (e.g., "enero 2026").
     */
    public static function monthYear(DateTimeInterface|string|null $value, string $pattern = 'MMMM y'): string
    {
        $dt = self::toDateTime($value);
        if (!$dt) {
            return '—';
        }

        if (class_exists(IntlDateFormatter::class)) {
            return self::intlFormat($dt, $pattern, IntlDateFormatter::NONE);
        }

        if ($dt instanceof CarbonInterface) {
            return $dt->copy()->locale(self::appLocale())->translatedFormat('F Y');
        }

        return $dt->format('m/Y');
    }

    private static function toDateTime(DateTimeInterface|string|null $value): ?DateTimeInterface
    {
        if (!$value) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value;
        }

        try {
            return \Carbon\Carbon::parse((string) $value)->timezone(self::timezone());
        } catch (\Throwable) {
            return null;
        }
    }

    private static function intlFormat(DateTimeInterface $dt, string $pattern, int $timeStyle): string
    {
        if (class_exists(IntlDateFormatter::class)) {
            $formatter = new IntlDateFormatter(
                self::locale(),
                IntlDateFormatter::NONE,
                $timeStyle,
                self::timezone(),
                IntlDateFormatter::GREGORIAN,
                $pattern
            );

            $formatted = $formatter->format($dt);
            if (is_string($formatted)) {
                return $formatted;
            }
        }

        // Fallback: numeric.
        return $dt->format($timeStyle === IntlDateFormatter::NONE ? 'd/m/Y' : 'd/m/Y H:i');
    }
}
