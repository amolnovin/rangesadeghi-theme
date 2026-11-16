<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\ServiceProvider;
use App\Support\ProductPresenter;

final class AssetsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontAssets'], 20);
        add_action('wp_head', [$this, 'injectHeadBrandingAndCustomCss'], 6);
    }

    public function enqueueFrontAssets(): void
    {
        // ترتیب دقیق بارگذاری استایل‌ها مشابه قالب HTML مرجع: styles.css -> pages.css -> home.css -> wp-compat.css
        $cssFiles = [
            'rangesadeghi-styles' => 'styles.css',
            'rangesadeghi-pages' => 'pages.css',
            'rangesadeghi-home' => 'home.css',
            'rangesadeghi-wp-compat' => 'resources/css/wp-compat.css',
        ];

        $prevDeps = [];
        foreach ($cssFiles as $handle => $relPath) {
            $abs = $this->app->basePath($relPath);
            $ver = is_file($abs) ? (string) filemtime($abs) : '2.1.0';
            wp_enqueue_style($handle, $this->app->baseUri($relPath), $prevDeps, $ver);
            $prevDeps = [$handle];
        }

        // اسکریپت مدرن و سبک اختصاصی فرانت‌اند وردپرس/ووکامرس
        $jsRel = 'resources/js/theme.js';
        $jsAbs = $this->app->basePath($jsRel);
        $jsVer = is_file($jsAbs) ? (string) filemtime($jsAbs) : '2.1.0';

        wp_enqueue_script('rangesadeghi-theme', $this->app->baseUri($jsRel), [], $jsVer, true);

        $popularRaw = (string) rs_option('general.popular_searches', 'مولتی سورفیس ریچ,تار عنکبوتی ساندورا,اکریلیک سوداکو,تکسچر کارن');
        $popularTerms = array_values(array_filter(array_map('trim', explode(',', $popularRaw))));

        wp_localize_script('rangesadeghi-theme', 'RS_WP', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'wcAjaxUrl' => rs_is_wc() && class_exists('WC_AJAX') ? \WC_AJAX::get_endpoint('%%endpoint%%') : '',
            'nonce' => wp_create_nonce('rs_public_nonce'),
            'homeUrl' => home_url('/'),
            'shopUrl' => rs_page_url('shop'),
            'cartUrl' => rs_page_url('cart'),
            'checkoutUrl' => rs_page_url('checkout'),
            'accountUrl' => rs_page_url('account'),
            'isLoggedIn' => is_user_logged_in(),
            'isWcActive' => rs_is_wc(),
            'stickyHeader' => (bool) rs_option('general.sticky_header_enabled', true),
            'heroAutoplayMs' => max(2000, (int) rs_option('home.hero_autoplay_ms', 5000)),
            'dealEndDatetime' => (string) rs_option('home.deal_end_datetime', ''),
            'freeShippingThreshold' => (int) rs_option('shop.free_shipping_threshold', 10000000),
            'ajaxFiltering' => (bool) rs_option('shop.ajax_filtering', true),
            'giftWrapCost' => (int) rs_option('shop.gift_wrap_cost', 35000),
            'popularSearches' => $popularTerms,
            'wishlist' => ProductPresenter::currentUserWishlist(),
        ]);

        if (is_singular() && comments_open() && get_option('thread_comments')) {
            wp_enqueue_script('comment-reply');
        }
    }

    public function injectHeadBrandingAndCustomCss(): void
    {
        $favicon = (string) rs_option('general.favicon', 'assets/images/logo-alt.webp');
        if (!function_exists('has_site_icon') || !has_site_icon()) {
            echo '<link rel="icon" href="' . esc_url(rs_img($favicon)) . '">' . "\n";
        }

        $brand = sanitize_hex_color((string) rs_option('general.brand_color', '#df3639')) ?: '#df3639';
        $brandDark = sanitize_hex_color((string) rs_option('general.brand_color_dark', '#b92427')) ?: '#b92427';
        $gold = sanitize_hex_color((string) rs_option('general.gold_color', '#b08948')) ?: '#b08948';
        $customCss = trim((string) rs_option('general.custom_css', ''));

        if ($brand !== '#df3639' || $brandDark !== '#b92427' || $gold !== '#b08948' || $customCss !== '') {
            echo '<style id="rangesadeghi-theme-vars">:root{--red:' . esc_attr($brand) . ';--accent:' . esc_attr($brand) . ';--accent-dark:' . esc_attr($brandDark) . ';--gold:' . esc_attr($gold) . ';}' . $customCss . "</style>\n";
        }
    }
}
