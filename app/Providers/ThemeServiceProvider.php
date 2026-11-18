<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\ServiceProvider;
use App\Support\DemoCatalog;
use App\Support\Persian;
use App\Support\ProductPresenter;
use WP_Query;

final class ThemeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_action('after_setup_theme', [$this, 'setupTheme']);
        add_action('init', [$this, 'registerBrandTaxonomy'], 5);
        add_filter('body_class', [$this, 'filterBodyClasses']);

        // AJAX عمومی (جستجوی زنده، خبرنامه، فرم تماس، مودال ورود)
        $ajaxActions = [
            'rs_live_search' => [$this, 'ajaxLiveSearch'],
            'rs_newsletter_subscribe' => [$this, 'ajaxNewsletter'],
            'rs_home_newsletter_subscribe' => [$this, 'ajaxHomeNewsletter'],
            'rs_contact_submit' => [$this, 'ajaxContactSubmit'],
            'rs_modal_auth' => [$this, 'ajaxModalAuth'],
        ];

        foreach ($ajaxActions as $action => $handler) {
            add_action("wp_ajax_{$action}", $handler);
            add_action("wp_ajax_nopriv_{$action}", $handler);
        }
    }

    public function setupTheme(): void
    {
        load_theme_textdomain('rangesadeghi', $this->app->basePath('resources/lang'));

        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support('automatic-feed-links');
        add_theme_support('responsive-embeds');
        add_theme_support('custom-logo', [
            'height' => 80,
            'width' => 200,
            'flex-height' => true,
            'flex-width' => true,
        ]);
        add_theme_support('html5', [
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        ]);

        // پشتیبانی بومی و کامل ووکامرس
        add_theme_support('woocommerce', [
            'thumbnail_image_width' => 420,
            'single_image_width' => 760,
            'product_grid' => [
                'default_rows' => 3,
                'min_rows' => 1,
                'default_columns' => 3,
                'min_columns' => 2,
                'max_columns' => 4,
            ],
        ]);
        add_theme_support('wc-product-gallery-zoom');
        add_theme_support('wc-product-gallery-lightbox');
        add_theme_support('wc-product-gallery-slider');

        register_nav_menus([
            'primary_navigation' => 'منوی اصلی هدر (دسکتاپ)',
            'utility_navigation' => 'نوار دسترسی سریع بالای هدر',
            'mobile_navigation' => 'منوی کشویی موبایل',
            'footer_col_1' => 'فوتر — ستون خرید و دسترسی',
            'footer_col_2' => 'فوتر — ستون دسته‌های پرطرفدار',
            'footer_col_3' => 'فوتر — ستون خدمات مشتریان',
        ]);
    }

    /**
     * ثبت تاکسونومی برند محصول (اگر ووکامرس ۹.۶+ خودش ثبت نکرده باشد)
     */
    public function registerBrandTaxonomy(): void
    {
        if (taxonomy_exists('product_brand') || !post_type_exists('product')) {
            return;
        }

        register_taxonomy('product_brand', ['product'], [
            'labels' => [
                'name' => 'برندها',
                'singular_name' => 'برند',
                'search_items' => 'جستجوی برندها',
                'all_items' => 'همه برندها',
                'edit_item' => 'ویرایش برند',
                'add_new_item' => 'افزودن برند جدید',
                'menu_name' => 'برندها',
            ],
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'brand'],
        ]);
    }

    /**
     * تنظیم کلاس‌های دقیق body منطبق با استایل‌های قالب HTML
     *
     * @param array<int, string> $classes
     * @return array<int, string>
     */
    public function filterBodyClasses(array $classes): array
    {
        if (is_front_page()) {
            $classes[] = 'home-v2';
        } else {
            $classes[] = 'hx-page';
        }

        if (rs_is_wc()) {
            if (function_exists('is_cart') && is_cart()) {
                $classes[] = 'page-cart';
            } elseif (function_exists('is_checkout') && is_checkout() && !is_wc_endpoint_url('order-received')) {
                $classes[] = 'page-checkout';
            } elseif ((function_exists('is_wc_endpoint_url') && (is_wc_endpoint_url('view-order') || is_wc_endpoint_url('order-received'))) || is_page_template('template-order-tracking.php')) {
                $classes[] = 'page-order';
            } elseif (function_exists('is_account_page') && is_account_page()) {
                $classes[] = 'page-account';
            }
        }

        return array_values(array_unique($classes));
    }

    /**
     * جستجوی زنده محصولات در هدر دسکتاپ و موبایل (AJAX)
     */
    public function ajaxLiveSearch(): void
    {
        $rawQuery = isset($_GET['q']) ? sanitize_text_field((string) wp_unslash($_GET['q'])) : '';
        $normalized = Persian::normalize($rawQuery);

        if ($normalized === '') {
            wp_send_json_success(['items' => [], 'total' => 0]);
        }

        $items = [];

        if (rs_is_wc()) {
            $query = new WP_Query([
                'post_type' => 'product',
                'post_status' => 'publish',
                's' => Persian::toEn($rawQuery),
                'posts_per_page' => 7,
                'no_found_rows' => true,
            ]);

            // اگر با ارقام لاتین پیدا نشد، با ارقام فارسی نیز بررسی شود
            if (!$query->have_posts() && Persian::toFa($rawQuery) !== Persian::toEn($rawQuery)) {
                $query = new WP_Query([
                    'post_type' => 'product',
                    'post_status' => 'publish',
                    's' => Persian::toFa($rawQuery),
                    'posts_per_page' => 7,
                    'no_found_rows' => true,
                ]);
            }

            foreach ($query->posts as $post) {
                $product = wc_get_product($post->ID);
                if (!$product) {
                    continue;
                }
                $vm = ProductPresenter::fromWcProduct($product);
                $items[] = [
                    'id' => $vm['id'],
                    'title' => $vm['title'],
                    'url' => $vm['url'],
                    'image' => $vm['image'],
                    'price_formatted' => rs_price($vm['price']) . ' تومان',
                ];
            }
        }

        // اگر فروشگاه هنوز محصولی نداشت و فالبک دمو روشن بود
        if ($items === [] && (bool) rs_option('home.demo_fallback_when_empty', true)) {
            $wcCount = post_type_exists('product') ? (int) (wp_count_posts('product')->publish ?? 0) : 0;
            if ($wcCount === 0) {
                foreach (DemoCatalog::products() as $demo) {
                    $haystack = Persian::normalize($demo['title'] . ' ' . ($demo['cat'] ?? '') . ' ' . ($demo['short'] ?? ''));
                    if (str_contains($haystack, $normalized)) {
                        $vm = ProductPresenter::fromDemoArray($demo);
                        $items[] = [
                            'id' => $vm['id'],
                            'title' => rs_fa($vm['title']),
                            'url' => $vm['url'],
                            'image' => $vm['image'],
                            'price_formatted' => rs_price($vm['price']) . ' تومان',
                        ];
                        if (count($items) >= 7) {
                            break;
                        }
                    }
                }
            }
        }

        wp_send_json_success([
            'items' => $items,
            'total' => count($items),
            'count_fa' => rs_fa(count($items)),
            'search_url' => add_query_arg(['s' => $rawQuery, 'post_type' => 'product'], home_url('/')),
        ]);
    }

    /**
     * ثبت عضویت در خبرنامه (ذخیره در دیتابیس برای مشاهده در پنل مدیریت)
     */
    public function ajaxNewsletter(): void
    {
        $this->submitNewsletter(true);
    }

    public function ajaxHomeNewsletter(): void
    {
        $this->submitNewsletter(false);
    }

    private function submitNewsletter(bool $requireCaptcha): void
    {
        check_ajax_referer('rs_public_nonce', 'nonce');
        if ($requireCaptcha) {
            $this->verifyMathCaptcha('newsletter');
        } elseif (!empty($_POST['newsletter_website'])) {
            wp_send_json_error(['message' => 'ارسال فرم امکان‌پذیر نیست.']);
        }

        $contact = isset($_POST['contact']) ? sanitize_text_field((string) wp_unslash($_POST['contact'])) : '';
        $cleanPhone = preg_replace('/\D+/', '', Persian::toEn($contact));
        $isEmail = is_email($contact) !== false;
        $isPhone = $cleanPhone !== null && (bool) preg_match('/^0?9\d{9}$/', $cleanPhone);

        if (!$isEmail && !$isPhone) {
            wp_send_json_error(['message' => 'لطفاً یک ایمیل یا شماره موبایل معتبر وارد کنید.']);
        }

        // The captcha-free form has a bounded per-address submission rate.
        if (!$requireCaptcha && !empty($_SERVER['REMOTE_ADDR'])) {
            $key = 'rs_news_' . substr(wp_hash((string) $_SERVER['REMOTE_ADDR']), 0, 32);
            $count = (int) get_transient($key);
            if ($count >= 10) wp_send_json_error(['message' => 'تعداد درخواست‌ها زیاد است؛ لطفاً بعداً دوباره تلاش کنید.']);
            set_transient($key, $count + 1, 15 * MINUTE_IN_SECONDS);
        }

        $subscribers = get_option('rangesadeghi_newsletter_subscribers', []);
        if (!is_array($subscribers)) {
            $subscribers = [];
        }

        $normalized = $isEmail ? strtolower($contact) : ('0' . ltrim((string) $cleanPhone, '0'));
        $subscribers[$normalized] = [
            'contact' => $normalized,
            'date' => current_time('mysql'),
            'ip' => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field((string) $_SERVER['REMOTE_ADDR']) : '',
        ];

        update_option('rangesadeghi_newsletter_subscribers', array_slice($subscribers, -1000, 1000, true), false);

        wp_send_json_success([
            'message' => 'عضویت شما در خبرنامه رنگ صادقی با موفقیت ثبت شد.',
        ]);
    }

    private function verifyMathCaptcha(string $context): void
    {
        if (!class_exists('WP_MC_Captcha') || !class_exists('WP_MC_Core') || !\WP_MC_Core::get('enabled', 1)) return;
        $result = \WP_MC_Captcha::verify(wp_unslash($_POST['wpmc_answer'] ?? ''), wp_unslash($_POST['wpmc_token'] ?? ''), wp_unslash($_POST['wpmc_website'] ?? ''), 'rangesadeghi-' . $context);
        if (is_wp_error($result)) wp_send_json_error(['message' => $result->get_error_message()]);
    }

    /**
     * ثبت فرم تماس با ما (ارسال ایمیل به مدیر + ذخیره در پنل مدیریت)
     */
    public function ajaxContactSubmit(): void
    {
        check_ajax_referer('rs_public_nonce', 'nonce');
        $this->verifyMathCaptcha('contact');

        $fullname = sanitize_text_field((string) wp_unslash($_POST['fullname'] ?? ''));
        $mobile = Persian::toEn(sanitize_text_field((string) wp_unslash($_POST['mobile'] ?? '')));
        $email = sanitize_email((string) wp_unslash($_POST['email'] ?? ''));
        $subject = sanitize_text_field((string) wp_unslash($_POST['subject'] ?? 'consult'));
        $message = sanitize_textarea_field((string) wp_unslash($_POST['message'] ?? ''));

        $digits = preg_replace('/\D+/', '', $mobile) ?? '';
        if (mb_strlen($fullname) < 2 || !preg_match('/^0?9\d{9}$/', $digits) || mb_strlen($message) < 4) {
            wp_send_json_error(['message' => 'لطفاً فیلدهای ستاره‌دار را به‌درستی تکمیل کنید.']);
        }

        $subjectLabels = [
            'consult' => 'مشاوره خرید محصول',
            'order' => 'پیگیری سفارش',
            'price' => 'استعلام قیمت و خرید عمده',
            'other' => 'سایر موارد',
        ];
        $subjectTitle = $subjectLabels[$subject] ?? 'پیام از فرم تماس سایت';

        $messages = get_option('rangesadeghi_contact_messages', []);
        if (!is_array($messages)) {
            $messages = [];
        }

        array_unshift($messages, [
            'name' => $fullname,
            'mobile' => '0' . ltrim($digits, '0'),
            'email' => $email,
            'subject' => $subjectTitle,
            'message' => $message,
            'date' => current_time('mysql'),
        ]);
        update_option('rangesadeghi_contact_messages', array_slice($messages, 0, 300), false);

        // ارسال ایمیل اطلاع‌رسانی به مدیر سایت
        $adminEmail = (string) (rs_option('general.email') ?: get_option('admin_email'));
        if (is_email($adminEmail)) {
            $body = "نام: {$fullname}\nموبایل: 0" . ltrim($digits, '0') . "\nایمیل: {$email}\nموضوع: {$subjectTitle}\n\nپیام:\n{$message}";
            @wp_mail($adminEmail, "پیام جدید در سایت رنگ صادقی: {$subjectTitle}", $body);
        }

        wp_send_json_success([
            'message' => 'پیام شما ثبت شد؛ کارشناسان رنگ صادقی به‌زودی با شما تماس می‌گیرند.',
        ]);
    }

    /**
     * ورود/ثبت‌نام سریع در مودال شماره موبایل یا هدایت به حساب کاربری
     */
    public function ajaxModalAuth(): void
    {
        check_ajax_referer('rs_public_nonce', 'nonce');

        $identity = sanitize_text_field((string) wp_unslash($_POST['identity'] ?? ''));
        $password = (string) wp_unslash($_POST['password'] ?? '');
        $phone = preg_replace('/\D+/', '', Persian::toEn($identity)) ?? '';

        if ($password !== '') {
            // The plugin's login hook skips validation when both fields are omitted.
            // Reject that case here without consuming the single-use challenge twice.
            if (class_exists('WP_MC_Captcha') && class_exists('WP_MC_Core') && \WP_MC_Core::get('enabled', 1) && \WP_MC_Core::get('apply_login') && (empty($_POST['wpmc_token']) || !isset($_POST['wpmc_answer']))) {
                wp_send_json_error(['message' => 'ابتدا مسئله امنیتی را پاسخ دهید.']);
            }
            $loginUser = $identity;
            if (preg_match('/^0?9\d{9}$/', $phone)) {
                $normalizedPhone = '0' . ltrim($phone, '0');
                $users = get_users([
                    'meta_key' => 'billing_phone',
                    'meta_value' => $normalizedPhone,
                    'number' => 1,
                ]);
                if (!empty($users[0])) {
                    $loginUser = $users[0]->user_login;
                }
            }

            $user = wp_signon([
                'user_login' => $loginUser,
                'user_password' => $password,
                'remember' => true,
            ]);

            if (is_wp_error($user)) {
                $message = $user->get_error_code() === 'wpmc_captcha_failed' ? $user->get_error_message() : 'نام کاربری/موبایل یا رمز عبور اشتباه است.';
                wp_send_json_error(['message' => $message]);
            }

            wp_send_json_success([
                'message' => 'خوش آمدید! در حال انتقال به حساب کاربری…',
                'redirect' => rs_page_url('account'),
            ]);
        }

        if (!preg_match('/^0?9\d{9}$/', $phone)) {
            wp_send_json_error(['message' => 'شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.']);
        }

        // اگر افزونه پیامک OTP نصب نیست، کاربر را به فرم ورود/ثبت‌نام حساب کاربری هدایت کن
        wp_send_json_success([
            'need_password' => true,
            'message' => 'رمز عبور حساب خود را وارد کنید یا از صفحه حساب کاربری ثبت‌نام نمایید.',
            'account_url' => rs_page_url('account'),
        ]);
    }
}
