<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\ServiceProvider;

/**
 * موتور بهینه‌سازی سرعت سایت و Core Web Vitals (LCP, INP, CLS)
 * بدون وابستگی به افزونه‌های کش سنگین، قابل کنترل کامل از پنل تنظیمات قالب.
 */
final class PerformanceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_action('init', [$this, 'applyCoreCleanups']);
        add_action('wp_head', [$this, 'outputResourceHintsAndPreloads'], 1);
        add_action('wp_enqueue_scripts', [$this, 'dequeueBloatAssets'], 100);
        add_filter('script_loader_tag', [$this, 'deferNonBlockingScripts'], 10, 3);
        add_filter('wp_get_attachment_image_attributes', [$this, 'optimizeAttachmentAttributes'], 10, 2);

        if ((bool) rs_option('performance.remove_query_strings', false) && !is_admin()) {
            add_filter('style_loader_src', [$this, 'stripAssetVersion'], 15);
            add_filter('script_loader_src', [$this, 'stripAssetVersion'], 15);
        }

        if ((bool) rs_option('performance.disable_xmlrpc', true)) {
            add_filter('xmlrpc_enabled', '__return_false');
        }

        if ((bool) rs_option('performance.disable_self_pingbacks', true)) {
            add_action('pre_ping', [$this, 'disableSelfPingbacks']);
        }

        $heartbeat = (string) rs_option('performance.heartbeat_mode', 'slow');
        if ($heartbeat === 'disable_front') {
            add_action('init', static function (): void {
                if (!is_admin()) {
                    wp_deregister_script('heartbeat');
                }
            }, 1);
        } elseif ($heartbeat === 'slow') {
            add_filter('heartbeat_settings', static function (array $settings): array {
                $settings['interval'] = 60;
                return $settings;
            });
        }

        if ((bool) rs_option('performance.minify_html', false) && !is_admin() && !defined('DOING_AJAX')) {
            add_action('template_redirect', [$this, 'startHtmlMinifier']);
        }
    }

    public function applyCoreCleanups(): void
    {
        if ((bool) rs_option('performance.disable_emojis', true)) {
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
            remove_action('wp_print_styles', 'print_emoji_styles');
            remove_action('admin_print_styles', 'print_emoji_styles');
            remove_filter('the_content_feed', 'wp_staticize_emoji');
            remove_filter('comment_text_rss', 'wp_staticize_emoji');
            remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
            add_filter('emoji_svg_url', '__return_false');
        }

        if ((bool) rs_option('performance.clean_wp_head', true)) {
            remove_action('wp_head', 'wp_generator');
            remove_action('wp_head', 'wlwmanifest_link');
            remove_action('wp_head', 'rsd_link');
            remove_action('wp_head', 'wp_shortlink_wp_head');
            remove_action('wp_head', 'rest_output_link_wp_head');
        }

        if ((bool) rs_option('performance.disable_embeds', true) && !is_admin()) {
            remove_action('wp_head', 'wp_oembed_add_discovery_links');
            remove_action('wp_head', 'wp_oembed_add_host_js');
        }
    }

    public function outputResourceHintsAndPreloads(): void
    {
        // ۱) پیش‌بارگذاری فونت لوکال اصلی دقیقاً مثل قالب HTML
        if ((bool) rs_option('performance.preload_fonts', true)) {
            echo '<link rel="preload" href="' . esc_url(rs_asset('assets/fonts/iranyekanx-regular.woff2')) . '" as="font" type="font/woff2" crossorigin>' . "\n";
        }

        // ۲) پیش‌بارگذاری تصویر LCP صفحه (اسلاید اول هیرو یا تصویر اصلی محصول/مقاله)
        if ((bool) rs_option('performance.preload_lcp_image', true)) {
            if (is_front_page()) {
                $slides = (array) rs_option('home.hero_slides', []);
                if (!empty($slides[0]['desktop'])) {
                    echo '<link rel="preload" href="' . esc_url(rs_img((string) $slides[0]['desktop'])) . '" as="image" fetchpriority="high">' . "\n";
                }
            } elseif (is_singular() && has_post_thumbnail()) {
                $lcpUrl = get_the_post_thumbnail_url(null, 'large');
                if ($lcpUrl) {
                    echo '<link rel="preload" href="' . esc_url((string) $lcpUrl) . '" as="image" fetchpriority="high">' . "\n";
                }
            }
        }

        // ۳) دامنه‌های Preconnect سفارشی
        $preconnects = array_filter(array_map('trim', preg_split('/[\r\n,]+/', (string) rs_option('performance.preconnect_urls', '')) ?: []));
        foreach ($preconnects as $domain) {
            echo '<link rel="preconnect" href="' . esc_url($domain) . '" crossorigin>' . "\n";
        }
    }

    public function dequeueBloatAssets(): void
    {
        if (is_admin()) {
            return;
        }

        if ((bool) rs_option('performance.disable_dashicons_guests', true) && !is_user_logged_in()) {
            wp_deregister_style('dashicons');
        }

        if ((bool) rs_option('performance.dequeue_block_styles', true) && !is_singular('post')) {
            wp_dequeue_style('wp-block-library');
            wp_dequeue_style('wp-block-library-theme');
            wp_dequeue_style('classic-theme-styles');
            wp_dequeue_style('global-styles');
        }

        if ((bool) rs_option('performance.dequeue_wc_styles', true) && rs_is_wc()) {
            // حفظ چیدمان اختصاصی قالب بدون تحمیل CSS سنگین پیش‌فرض ووکامرس
            wp_dequeue_style('wc-blocks-style');
            wp_dequeue_style('wc-blocks-vendors-style');
            wp_dequeue_style('woocommerce-general');
            wp_dequeue_style('woocommerce-layout');
            wp_dequeue_style('woocommerce-smallscreen');
        }

        if ((bool) rs_option('performance.disable_cart_fragments', true) && rs_is_wc()) {
            if (!is_cart() && !is_checkout()) {
                wp_dequeue_script('wc-cart-fragments');
            }
        }

        if ((bool) rs_option('performance.disable_embeds', true)) {
            wp_deregister_script('wp-embed');
        }
    }

    public function deferNonBlockingScripts(string $tag, string $handle, string $src): string
    {
        if (is_admin() || !(bool) rs_option('performance.defer_scripts', true)) {
            return $tag;
        }

        if (function_exists('is_checkout') && is_checkout()) {
            return $tag;
        }

        $excluded = ['jquery', 'jquery-core', 'jquery-migrate', 'wc-checkout'];
        if (in_array($handle, $excluded, true) || str_contains($tag, ' defer') || str_contains($tag, ' async')) {
            return $tag;
        }

        return str_replace(' src=', ' defer src=', $tag);
    }

    /**
     * @param array<string, string> $attr
     * @return array<string, string>
     */
    public function optimizeAttachmentAttributes(array $attr, mixed $attachment): array
    {
        if (!isset($attr['decoding'])) {
            $attr['decoding'] = 'async';
        }
        return $attr;
    }

    public function stripAssetVersion(string $src): string
    {
        if (str_contains($src, 'ver=')) {
            $src = (string) remove_query_arg('ver', $src);
        }
        return $src;
    }

    /**
     * @param array<int, string> $links
     */
    public function disableSelfPingbacks(array &$links): void
    {
        $home = home_url();
        foreach ($links as $idx => $link) {
            if (str_starts_with($link, $home)) {
                unset($links[$idx]);
            }
        }
    }

    public function startHtmlMinifier(): void
    {
        ob_start(static function (string $html): string {
            if (trim($html) === '' || str_starts_with(ltrim($html), '{')) {
                return $html;
            }

            // محافظت از تگ‌های حساس به فاصله (script, style, pre, textarea)
            $protected = [];
            $html = (string) preg_replace_callback(
                '#<(script|style|pre|textarea)\b[^>]*>.*?</\1>#is',
                static function (array $m) use (&$protected): string {
                    $token = '___RS_PROTECTED_' . count($protected) . '___';
                    $protected[$token] = $m[0];
                    return $token;
                },
                $html
            );

            $html = (string) preg_replace('/<!--(?!\[if).*?-->/s', '', $html);
            $html = (string) preg_replace('/>\s{2,}</', '> <', $html);
            $html = (string) preg_replace('/\s{2,}/', ' ', $html);

            if ($protected !== []) {
                $html = strtr($html, $protected);
            }

            return trim($html);
        });
    }
}
