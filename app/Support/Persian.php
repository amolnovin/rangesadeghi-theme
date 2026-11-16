<?php

declare(strict_types=1);

namespace App\Support;

/**
 * ابزارهای بومی‌سازی فارسی: ارقام فارسی/انگلیسی، فرمت قیمت تومانی با جداکننده نقطه،
 * تبدیل دقیق تاریخ میلادی به هجری شمسی (بدون نیاز به افزونه یا ext-intl) و زمان مطالعه.
 */
final class Persian
{
    private const FA_DIGITS = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    private const AR_DIGITS = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    private const EN_DIGITS = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    private const JALALI_MONTHS = [
        1 => 'فروردین',
        2 => 'اردیبهشت',
        3 => 'خرداد',
        4 => 'تیر',
        5 => 'مرداد',
        6 => 'شهریور',
        7 => 'مهر',
        8 => 'آبان',
        9 => 'آذر',
        10 => 'دی',
        11 => 'بهمن',
        12 => 'اسفند',
    ];

    public static function toFa(string|int|float $value): string
    {
        $str = (string) $value;
        $str = str_replace(self::AR_DIGITS, self::FA_DIGITS, $str);
        return str_replace(self::EN_DIGITS, self::FA_DIGITS, $str);
    }

    public static function toEn(string|int|float $value): string
    {
        $str = (string) $value;
        $str = str_replace(self::FA_DIGITS, self::EN_DIGITS, $str);
        return str_replace(self::AR_DIGITS, self::EN_DIGITS, $str);
    }

    public static function normalize(string $text): string
    {
        $text = str_replace(['ي', 'ك', 'ة', "\u{200C}"], ['ی', 'ک', 'ه', ' '], trim($text));
        return (string) preg_replace('/\s+/u', ' ', mb_strtolower($text, 'UTF-8'));
    }

    /**
     * فرمت قیمت دقیقاً مشابه قالب HTML (مثلاً ۱.۲۲۰.۰۰۰)
     */
    public static function formatPrice(int|float|string $amount, string $separator = '.'): string
    {
        $numeric = (float) self::toEn((string) $amount);
        if (function_exists('get_woocommerce_currency') && get_woocommerce_currency() === 'IRR') {
            $numeric = round($numeric / 10);
        }
        $formatted = number_format(max(0, round($numeric)), 0, '', $separator);
        return self::toFa($formatted);
    }

    /**
     * نرمال‌سازی مبلغ خام ووکامرس به تومان
     */
    public static function toToman(int|float|string $amount): int
    {
        $numeric = (float) self::toEn((string) $amount);
        if (function_exists('get_woocommerce_currency') && get_woocommerce_currency() === 'IRR') {
            return (int) round($numeric / 10);
        }
        return (int) round($numeric);
    }

    /**
     * تبدیل مبلغ به ریال برای اسکیماهای رسمی Google Merchant (IRR)
     */
    public static function toRial(int|float|string $amount): int
    {
        $numeric = (float) self::toEn((string) $amount);
        if (function_exists('get_woocommerce_currency') && get_woocommerce_currency() === 'IRR') {
            return (int) round($numeric);
        }
        return (int) round($numeric * 10);
    }

    /**
     * تبدیل تاریخ یونیکس/میلادی به تاریخ شمسی خوانا (مثلاً «۱۰ آبان ۱۴۰۵»)
     */
    public static function jalaliDate(int|string|null $timestamp = null, bool $withTime = false): string
    {
        if (is_string($timestamp) && !ctype_digit($timestamp)) {
            $parsed = strtotime($timestamp);
            $ts = $parsed !== false ? $parsed : time();
        } else {
            $ts = $timestamp !== null ? (int) $timestamp : time();
        }

        [$gy, $gm, $gd] = [(int) gmdate('Y', $ts + 12600), (int) gmdate('n', $ts + 12600), (int) gmdate('j', $ts + 12600)];
        [$jy, $jm, $jd] = self::gregorianToJalali($gy, $gm, $gd);

        $day = str_pad((string) $jd, 2, '0', STR_PAD_LEFT);
        $monthName = self::JALALI_MONTHS[$jm] ?? '';
        $result = "{$day} {$monthName} {$jy}";

        if ($withTime) {
            $time = gmdate('H:i', $ts + 12600);
            $result .= "، ساعت {$time}";
        }

        return self::toFa($result);
    }

    /**
     * الگوریتم دقیق تبدیل تاریخ میلادی به شمسی
     *
     * @return array{0: int, 1: int, 2: int}
     */
    public static function gregorianToJalali(int $gy, int $gm, int $gd): array
    {
        $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
        $days = 355666 + (365 * $gy)
            + intdiv($gy2 + 3, 4)
            - intdiv($gy2 + 99, 100)
            + intdiv($gy2 + 399, 400)
            + $gd
            + $g_d_m[$gm - 1];

        $jy = -1595 + (33 * intdiv($days, 12053));
        $days %= 12053;
        $jy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $jy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        if ($days < 186) {
            $jm = 1 + intdiv($days, 31);
            $jd = 1 + ($days % 31);
        } else {
            $jm = 7 + intdiv($days - 186, 30);
            $jd = 1 + (($days - 186) % 30);
        }

        return [$jy, $jm, $jd];
    }

    /**
     * محاسبه زمان تقریبی مطالعه مقاله به فارسی
     */
    public static function readingTime(string $html): string
    {
        $plain = trim(strip_tags($html));
        if ($plain === '') {
            return '۳ دقیقه';
        }
        $words = preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY);
        $minutes = max(1, (int) ceil(count($words ?: []) / 190));
        return self::toFa($minutes) . ' دقیقه';
    }
}
