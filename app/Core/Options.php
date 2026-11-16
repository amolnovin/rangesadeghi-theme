<?php

declare(strict_types=1);

namespace App\Core;

/**
 * مخزن مدیریت تنظیمات قالب (Theme Options Repository)
 * یکپارچه، کش‌شده در حافظه، با ادغام بازگشتی مقادیر پیش‌فرض و پشتیبانی از برون‌ریزی/درون‌ریزی JSON.
 */
final class Options
{
    public const OPTION_KEY = 'rangesadeghi_theme_options';

    /** @var array<string, mixed>|null */
    private ?array $cache = null;

    /** @var array<string, mixed> */
    private array $defaults;

    /**
     * @param array<string, mixed> $defaults
     */
    public function __construct(array $defaults = [])
    {
        $this->defaults = $defaults;
    }

    /**
     * دریافت تمام تنظیمات با ادغام پیش‌فرض‌ها
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $stored = function_exists('get_option') ? get_option(self::OPTION_KEY, []) : [];
        if (!is_array($stored)) {
            $stored = [];
        }

        $this->cache = $this->mergeDefaults($this->defaults, $stored);
        return $this->cache;
    }

    /**
     * دسترسی نقطه‌ای (Dot-notation) مثلاً: get('home.hero_enabled', true)
     */
    public function get(string $key, mixed $fallback = null): mixed
    {
        $data = $this->all();
        if (array_key_exists($key, $data)) {
            return $data[$key];
        }

        $segments = explode('.', $key);
        $cursor = $data;

        foreach ($segments as $segment) {
            if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
                return $fallback;
            }
            $cursor = $cursor[$segment];
        }

        return $cursor;
    }

    /**
     * ذخیره ایمن و پاک‌سازی‌شده تنظیمات
     *
     * @param array<string, mixed> $incoming
     */
    public function update(array $incoming): bool
    {
        $current = $this->all();
        $sanitized = $this->sanitizeTree($incoming);
        $merged = $this->mergeDefaults($current, $sanitized);

        $this->cache = $merged;

        if (function_exists('update_option')) {
            update_option(self::OPTION_KEY, $merged, true);
            if (function_exists('delete_transient')) {
                delete_transient('rs_frontpage_cache');
            }
            return true;
        }

        return false;
    }

    /**
     * بازنشانی تنظیمات به حالت کارخانه (پیش‌فرض قالب HTML)
     */
    public function reset(): void
    {
        $this->cache = $this->defaults;
        if (function_exists('update_option')) {
            update_option(self::OPTION_KEY, $this->defaults, true);
            if (function_exists('delete_transient')) {
                delete_transient('rs_frontpage_cache');
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        return $this->defaults;
    }

    public function exportJson(): string
    {
        return (string) json_encode($this->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function importJson(string $json): bool
    {
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return false;
        }

        $this->cache = $this->mergeDefaults($this->defaults, $this->sanitizeTree($decoded));
        if (function_exists('update_option')) {
            update_option(self::OPTION_KEY, $this->cache, true);
        }

        return true;
    }

    /**
     * @param array<string, mixed> $base
     * @param array<string, mixed> $override
     * @return array<string, mixed>
     */
    private function mergeDefaults(array $base, array $override): array
    {
        foreach ($override as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key]) && !$this->isListArray($value) && !$this->isListArray($base[$key])) {
                $base[$key] = $this->mergeDefaults($base[$key], $value);
            } else {
                $base[$key] = $value;
            }
        }

        return $base;
    }

    /**
     * @param array<mixed> $arr
     */
    private function isListArray(array $arr): bool
    {
        if ($arr === []) {
            return true;
        }
        return array_keys($arr) === range(0, count($arr) - 1);
    }

    /**
     * پاک‌سازی بازگشتی داده‌های ورودی پنل مدیریت
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function sanitizeTree(array $data): array
    {
        $clean = [];
        $rawAllowedKeys = ['head_scripts', 'footer_scripts', 'enamad_code', 'custom_css'];

        foreach ($data as $key => $value) {
            $safeKey = is_string($key) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', $key) : $key;
            if ($safeKey === '' || $safeKey === null) {
                continue;
            }

            if (is_array($value)) {
                $clean[$safeKey] = $this->sanitizeTree($value);
            } elseif (is_bool($value)) {
                $clean[$safeKey] = $value;
            } elseif (is_int($value) || is_float($value)) {
                $clean[$safeKey] = $value;
            } elseif (is_string($value)) {
                $trimmed = wp_unslash_safe($value);
                if (in_array((string) $safeKey, $rawAllowedKeys, true)) {
                    $clean[$safeKey] = function_exists('current_user_can') && current_user_can('unfiltered_html')
                        ? trim($trimmed)
                        : strip_tags(trim($trimmed), '<script><style><meta><link><a><img><span><div><small><b><strong>');
                } elseif (str_contains((string) $safeKey, 'html') || str_contains((string) $safeKey, 'content') || str_contains((string) $safeKey, 'desc')) {
                    $clean[$safeKey] = function_exists('wp_kses_post') ? wp_kses_post(trim($trimmed)) : trim($trimmed);
                } else {
                    $clean[$safeKey] = function_exists('sanitize_textarea_field')
                        ? sanitize_textarea_field(trim($trimmed))
                        : trim(strip_tags($trimmed));
                }
            }
        }

        return $clean;
    }
}

function wp_unslash_safe(string $value): string
{
    return function_exists('wp_unslash') ? (string) wp_unslash($value) : stripslashes($value);
}
