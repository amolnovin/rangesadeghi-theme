<?php

declare(strict_types=1);

use App\Core\Application;
use App\Core\Blade;
use App\Core\Options;
use App\Support\Persian;

if (!function_exists('rs_app')) {
    /**
     * دسترسی سراسری به کانتینر Sage قالب
     */
    function rs_app(?string $abstract = null): mixed
    {
        $instance = Application::getInstance();
        if ($instance === null) {
            throw new RuntimeException('Theme application container has not been booted yet.');
        }

        return $abstract === null ? $instance : $instance->make($abstract);
    }
}

if (!function_exists('rs_view')) {
    /**
     * رندر قالب Blade مشابه تابع view() در Sage 10
     *
     * @param array<string, mixed> $data
     */
    function rs_view(string $view, array $data = []): string
    {
        /** @var Blade $blade */
        $blade = rs_app('blade');
        return $blade->render($view, $data);
    }
}

if (!function_exists('rs_render')) {
    /**
     * چاپ مستقیم قالب Blade در فایل‌های ورودی وردپرس/ووکامرس
     *
     * @param array<string, mixed> $data
     */
    function rs_render(string $view, array $data = []): void
    {
        /** @var Blade $blade */
        $blade = rs_app('blade');
        $blade->display($view, $data);
    }
}

if (!function_exists('rs_option')) {
    /**
     * خواندن سریع تنظیمات پنل قالب با کلید نقطه‌ای
     */
    function rs_option(string $key, mixed $fallback = null): mixed
    {
        /** @var Options $options */
        $options = rs_app('options');
        return $options->get($key, $fallback);
    }
}

if (!function_exists('rs_asset')) {
    /**
     * ساخت آدرس کامل دارایی‌های قالب (فونت، تصویر، استایل و اسکریپت)
     */
    function rs_asset(string $path): string
    {
        if (preg_match('#^(?:https?:)?//#i', $path) || str_starts_with($path, 'data:')) {
            return $path;
        }
        return rs_app()->baseUri(ltrim($path, '/'));
    }
}

if (!function_exists('rs_img')) {
    /**
     * ساخت آدرس تصویر در پوشه assets/images یا بازگردانی URL کامل رسانه وردپرس
     */
    function rs_img(string $image): string
    {
        if ($image === '') {
            return rs_asset('assets/images/logo-alt.webp');
        }
        if (preg_match('#^(?:https?:)?//#i', $image) || str_starts_with($image, '/')) {
            return $image;
        }
        if (str_starts_with($image, 'assets/')) {
            return rs_asset($image);
        }
        return rs_asset('assets/images/' . ltrim($image, '/'));
    }
}

if (!function_exists('rs_fa')) {
    function rs_fa(string|int|float $value): string
    {
        return Persian::toFa($value);
    }
}

if (!function_exists('rs_en')) {
    function rs_en(string|int|float $value): string
    {
        return Persian::toEn($value);
    }
}

if (!function_exists('rs_price')) {
    function rs_price(int|float|string $amount, string $separator = '.'): string
    {
        return Persian::formatPrice($amount, $separator);
    }
}

if (!function_exists('rs_jalali')) {
    function rs_jalali(int|string|null $timestamp = null, bool $withTime = false): string
    {
        return Persian::jalaliDate($timestamp, $withTime);
    }
}

if (!function_exists('rs_is_wc')) {
    function rs_is_wc(): bool
    {
        return class_exists('WooCommerce') && function_exists('WC');
    }
}

if (!function_exists('rs_page_url')) {
    /**
     * یافتن هوشمند لینک صفحات فروشگاه، سبد، تسویه، حساب، دسته‌بندی، درباره، تماس، فروشگاه‌ها و پیگیری سفارش
     */
    function rs_page_url(string $slug, array $query = []): string
    {
        $url = match ($slug) {
            'home' => function_exists('home_url') ? home_url('/') : '/',
            'shop' => (rs_is_wc() && function_exists('wc_get_page_permalink'))
                ? wc_get_page_permalink('shop')
                : (function_exists('get_post_type_archive_link') && get_post_type_archive_link('product') ?: home_url('/shop/')),
            'cart' => (rs_is_wc() && function_exists('wc_get_cart_url'))
                ? wc_get_cart_url()
                : rs_find_template_or_slug_url('cart', ''),
            'checkout' => (rs_is_wc() && function_exists('wc_get_checkout_url'))
                ? wc_get_checkout_url()
                : rs_find_template_or_slug_url('checkout', ''),
            'account' => (rs_is_wc() && function_exists('wc_get_page_permalink'))
                ? wc_get_page_permalink('myaccount')
                : rs_find_template_or_slug_url('my-account', ''),
            'blog' => (function () {
                $postsPage = (int) get_option('page_for_posts');
                return $postsPage > 0 ? (string) get_permalink($postsPage) : home_url('/blog/');
            })(),
            'categories' => rs_find_template_or_slug_url('categories', 'template-categories.php'),
            'about' => rs_find_template_or_slug_url('about', 'template-about.php'),
            'contact' => rs_find_template_or_slug_url('contact', 'template-contact.php'),
            'stores' => rs_find_template_or_slug_url('stores', 'template-stores.php'),
            'order-details', 'order-tracking' => rs_find_template_or_slug_url('order-details', 'template-order-tracking.php'),
            default => home_url('/' . trim($slug, '/') . '/'),
        };

        if ($query !== [] && function_exists('add_query_arg')) {
            $url = (string) add_query_arg($query, $url);
        }

        return $url;
    }
}

if (!function_exists('rs_find_template_or_slug_url')) {
    function rs_find_template_or_slug_url(string $slug, string $templateFile): string
    {
        static $memo = [];
        $cacheKey = $slug . '|' . $templateFile;
        if (isset($memo[$cacheKey])) {
            return $memo[$cacheKey];
        }

        if ($templateFile !== '' && function_exists('get_pages')) {
            $pages = get_pages([
                'meta_key' => '_wp_page_template',
                'meta_value' => $templateFile,
                'number' => 1,
            ]);
            if (!empty($pages[0])) {
                return $memo[$cacheKey] = (string) get_permalink($pages[0]->ID);
            }
        }

        if (function_exists('get_page_by_path')) {
            $page = get_page_by_path($slug);
            if ($page) {
                return $memo[$cacheKey] = (string) get_permalink($page->ID);
            }
        }

        return $memo[$cacheKey] = function_exists('home_url') ? home_url('/' . trim($slug, '/') . '/') : '/' . $slug;
    }
}
