<?php

declare(strict_types=1);

namespace App\Providers;

use App\Admin\DemoSeeder;
use App\Core\Options;
use App\Core\ServiceProvider;
use App\Updater\PatchManager;

/**
 * پنل مدیریت حرفه‌ای و راست‌چین قالب رنگ صادقی (۸ تب تخصصی + متاباکس کد رهگیری ووکامرس)
 */
final class AdminServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (!is_admin()) {
            return;
        }

        add_action('admin_menu', [$this, 'registerAdminMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);

        // عملیات فرم‌ها و AJAX پنل مدیریت
        add_action('wp_ajax_rs_admin_save_options', [$this, 'ajaxSaveOptions']);
        add_action('wp_ajax_rs_admin_reset_options', [$this, 'ajaxResetOptions']);
        add_action('wp_ajax_rs_admin_flush_cache', [$this, 'ajaxFlushCache']);
        add_action('wp_ajax_rs_admin_seed_demo', [$this, 'ajaxSeedDemo']);
        add_action('wp_ajax_rs_admin_export_options', [$this, 'ajaxExportOptions']);
        add_action('wp_ajax_rs_admin_import_options', [$this, 'ajaxImportOptions']);
        add_action('wp_ajax_rs_admin_check_patch', [$this, 'ajaxCheckPatch']);
        add_action('wp_ajax_rs_admin_install_remote_patch', [$this, 'ajaxInstallRemotePatch']);
        add_action('wp_ajax_rs_admin_upload_patch', [$this, 'ajaxUploadPatch']);
        add_action('wp_ajax_rs_admin_rollback_patch', [$this, 'ajaxRollbackPatch']);

        // متاباکس کد رهگیری پستی و بازه تحویل در سفارش‌های ووکامرس (سازگار با HPOS)
        add_action('add_meta_boxes', [$this, 'registerOrderTrackingMetaBox']);
        add_action('woocommerce_process_shop_order_meta', [$this, 'saveOrderTrackingMetaBox'], 20, 2);
    }

    public function registerAdminMenu(): void
    {
        add_menu_page(
            'تنظیمات قالب رنگ صادقی',
            'قالب رنگ صادقی',
            'manage_options',
            'rangesadeghi-settings',
            [$this, 'renderSettingsPage'],
            'dashicons-art',
            59
        );

        $subTabs = [
            'general' => 'تنظیمات عمومی و برند',
            'home' => 'بخش‌های صفحه اصلی',
            'shop' => 'تنظیمات فروشگاه',
            'product' => 'تنظیمات تک‌محصول',
            'seo' => 'سئو پیشرفته',
            'schema' => 'اسکیماها (JSON-LD)',
            'performance' => 'بهینه‌سازی سرعت سایت',
            'updater' => 'بروزرسانی قالب با پچ',
        ];

        foreach ($subTabs as $tabKey => $label) {
            add_submenu_page(
                'rangesadeghi-settings',
                $label,
                $label,
                'manage_options',
                $tabKey === 'general' ? 'rangesadeghi-settings' : ('rangesadeghi-settings&tab=' . $tabKey),
                [$this, 'renderSettingsPage']
            );
        }
    }

    public function enqueueAdminAssets(string $hook): void
    {
        if (!str_contains($hook, 'rangesadeghi-settings')) {
            return;
        }

        wp_enqueue_media();

        $cssRel = 'resources/css/admin-panel.css';
        $jsRel = 'resources/js/admin-panel.js';

        wp_enqueue_style(
            'rangesadeghi-admin',
            $this->app->baseUri($cssRel),
            [],
            (string) (@filemtime($this->app->basePath($cssRel)) ?: '2.1.0')
        );

        wp_enqueue_script(
            'rangesadeghi-admin',
            $this->app->baseUri($jsRel),
            ['jquery'],
            (string) (@filemtime($this->app->basePath($jsRel)) ?: '2.1.0'),
            true
        );

        wp_localize_script('rangesadeghi-admin', 'RS_ADMIN', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rs_admin_nonce'),
        ]);
    }

    public function renderSettingsPage(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('شما دسترسی لازم به این بخش را ندارید.');
        }

        /** @var Options $options */
        $options = $this->app->make('options');
        /** @var PatchManager $patcher */
        $patcher = $this->app->make('patcher');

        $activeTab = isset($_GET['tab']) ? sanitize_key((string) $_GET['tab']) : 'general';
        $newsletterCount = count((array) get_option('rangesadeghi_newsletter_subscribers', []));
        $contactMessages = array_slice((array) get_option('rangesadeghi_contact_messages', []), 0, 10);

        rs_render('admin.settings', [
            'opts' => $options->all(),
            'activeTab' => $activeTab,
            'themeVersion' => $patcher->currentVersion(),
            'phpVersion' => PHP_VERSION,
            'wpVersion' => get_bloginfo('version'),
            'wcActive' => rs_is_wc(),
            'wcVersion' => defined('WC_VERSION') ? WC_VERSION : 'غیرفعال',
            'hasZipArchive' => class_exists(\ZipArchive::class),
            'backups' => $patcher->listBackups(),
            'patchHistory' => $patcher->history(),
            'newsletterCount' => $newsletterCount,
            'contactMessages' => $contactMessages,
        ]);
    }

    public function ajaxSaveOptions(): void
    {
        $this->guardAdminAjax();

        $rawPayload = isset($_POST['options_json']) ? (string) wp_unslash($_POST['options_json']) : '';
        $decoded = json_decode($rawPayload, true);

        if (!is_array($decoded)) {
            wp_send_json_error(['message' => 'ساختار داده‌های ارسالی معتبر نیست.']);
        }

        /** @var Options $options */
        $options = $this->app->make('options');
        $options->update($decoded);
        if (class_exists('WC_Cache_Helper')) \WC_Cache_Helper::get_transient_version('shipping', true);

        /** @var PatchManager $patcher */
        $patcher = $this->app->make('patcher');
        $patcher->flushThemeCaches();

        wp_send_json_success(['message' => 'تمامی تنظیمات قالب با موفقیت ذخیره و کش‌ها نوسازی شدند.']);
    }

    public function ajaxResetOptions(): void
    {
        $this->guardAdminAjax();
        /** @var Options $options */
        $options = $this->app->make('options');
        $options->reset();
        if (class_exists('WC_Cache_Helper')) \WC_Cache_Helper::get_transient_version('shipping', true);
        $this->app->make('patcher')->flushThemeCaches();

        wp_send_json_success(['message' => 'تنظیمات قالب به مقادیر پیش‌فرض اولیه بازگردانی شد.']);
    }

    public function ajaxFlushCache(): void
    {
        $this->guardAdminAjax();
        /** @var PatchManager $patcher */
        $patcher = $this->app->make('patcher');
        $patcher->flushThemeCaches();

        wp_send_json_success(['message' => 'کش قالب‌های کامپایل‌شده Blade و کش کوئری‌های صفحه اصلی پاکسازی شد.']);
    }

    public function ajaxSeedDemo(): void
    {
        $this->guardAdminAjax();
        $seeder = new DemoSeeder();
        $stats = $seeder->seedAll();

        wp_send_json_success([
            'message' => sprintf(
                'درون‌ریزی نمونه انجام شد: %d برگه، %d دسته، %d برند، %d محصول و %d مقاله آموزشی ثبت گردید.',
                $stats['pages'],
                $stats['categories'],
                $stats['brands'],
                $stats['products'],
                $stats['posts']
            ),
            'stats' => $stats,
        ]);
    }

    public function ajaxExportOptions(): void
    {
        $this->guardAdminAjax();
        /** @var Options $options */
        $options = $this->app->make('options');
        wp_send_json_success([
            'filename' => 'rangesadeghi-settings-' . gmdate('Ymd-His') . '.json',
            'json' => $options->exportJson(),
        ]);
    }

    public function ajaxImportOptions(): void
    {
        $this->guardAdminAjax();
        $json = isset($_POST['json']) ? (string) wp_unslash($_POST['json']) : '';
        /** @var Options $options */
        $options = $this->app->make('options');
        if (!$options->importJson($json)) {
            wp_send_json_error(['message' => 'فایل JSON تنظیمات معتبر نیست.']);
        }
        if (class_exists('WC_Cache_Helper')) \WC_Cache_Helper::get_transient_version('shipping', true);
        $this->app->make('patcher')->flushThemeCaches();
        wp_send_json_success(['message' => 'تنظیمات با موفقیت درون‌ریزی شد. صفحه در حال بارگذاری مجدد است…']);
    }

    public function ajaxCheckPatch(): void
    {
        $this->guardAdminAjax();
        /** @var PatchManager $patcher */
        $patcher = $this->app->make('patcher');
        $info = $patcher->checkRemoteUpdate(true);
        if (empty($info['ok'])) {
            wp_send_json_error($info);
        }
        wp_send_json_success($info);
    }

    public function ajaxInstallRemotePatch(): void
    {
        $this->guardAdminAjax();
        /** @var PatchManager $patcher */
        $patcher = $this->app->make('patcher');
        $res = $patcher->installRemotePatch();
        if (!$res['ok']) {
            wp_send_json_error($res);
        }
        wp_send_json_success($res);
    }

    public function ajaxUploadPatch(): void
    {
        $this->guardAdminAjax();

        if (empty($_FILES['patch_file']['tmp_name']) || !is_uploaded_file((string) $_FILES['patch_file']['tmp_name'])) {
            wp_send_json_error(['message' => 'لطفاً فایل پچ (.zip یا .json) را انتخاب کنید.']);
        }

        $tmpPath = (string) $_FILES['patch_file']['tmp_name'];
        $origName = sanitize_file_name((string) ($_FILES['patch_file']['name'] ?? 'patch.zip'));

        /** @var PatchManager $patcher */
        $patcher = $this->app->make('patcher');
        $result = $patcher->applyPatchFile($tmpPath, $origName);

        if (!$result['ok']) {
            wp_send_json_error($result);
        }

        wp_send_json_success($result);
    }

    public function ajaxRollbackPatch(): void
    {
        $this->guardAdminAjax();
        $backupId = sanitize_text_field((string) wp_unslash($_POST['backup_id'] ?? ''));

        /** @var PatchManager $patcher */
        $patcher = $this->app->make('patcher');
        $res = $patcher->rollback($backupId);

        if (!$res['ok']) {
            wp_send_json_error($res);
        }
        wp_send_json_success($res);
    }

    private function guardAdminAjax(): void
    {
        check_ajax_referer('rs_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'عدم دسترسی مدیریتی.'], 403);
        }
    }

    /* ---------------- متاباکس کد رهگیری مرسوله در سفارش‌های ووکامرس ---------------- */
    public function registerOrderTrackingMetaBox(): void
    {
        if (!rs_is_wc()) {
            return;
        }

        $screens = ['shop_order', 'woocommerce_page_wc-orders'];
        foreach ($screens as $screen) {
            add_meta_box(
                'rs-order-tracking-box',
                'اطلاعات تحویل و رهگیری مرسوله (رنگ صادقی)',
                [$this, 'renderOrderTrackingMetaBox'],
                $screen,
                'side',
                'high'
            );
        }
    }

    public function renderOrderTrackingMetaBox(mixed $postOrOrder): void
    {
        $order = ($postOrOrder instanceof \WC_Order) ? $postOrOrder : wc_get_order($postOrOrder->ID ?? 0);
        if (!$order) {
            return;
        }

        wp_nonce_field('rs_save_order_tracking', 'rs_order_tracking_nonce');
        $tracking = (string) $order->get_meta('_rs_tracking_code');
        $carrier = (string) ($order->get_meta('_rs_carrier_name') ?: 'پست پیشتاز');
        $day = (string) $order->get_meta('_rs_delivery_day');
        $slot = (string) $order->get_meta('_rs_delivery_slot');
        $gift = (string) $order->get_meta('_rs_gift_wrap');
        $invoice = (string) $order->get_meta('_rs_official_invoice');
        ?>
        <div style="direction:rtl;text-align:right;font-size:13px">
            <p>
                <label style="display:block;font-weight:600;margin-bottom:4px">شرکت حمل‌ونقل:</label>
                <input type="text" name="rs_carrier_name" value="<?php echo esc_attr($carrier); ?>" style="width:100%">
            </p>
            <p>
                <label style="display:block;font-weight:600;margin-bottom:4px">کد رهگیری ۲۴ رقمی مرسوله:</label>
                <input type="text" name="rs_tracking_code" value="<?php echo esc_attr($tracking); ?>" dir="ltr" style="width:100%" placeholder="294710485629301847502914">
            </p>
            <?php if ($day !== '' || $slot !== ''): ?>
                <p style="background:#f7f4ef;padding:8px;border-radius:6px">
                    <strong>زمان تحویل انتخابی مشتری:</strong><br>
                    <?php echo esc_html($day . ' — ساعت ' . $slot); ?>
                </p>
            <?php endif; ?>
            <?php if (current_user_can('manage_woocommerce')) : ?>
                <?php $sendUrl = wp_nonce_url(add_query_arg(['action' => 'rs_delivery_send', 'order_id' => $order->get_id()], admin_url('admin-post.php')), 'rs_delivery_send_' . $order->get_id()); ?>
                <p><a class="button" href="<?php echo esc_url($sendUrl); ?>" onclick="return confirm('پیامک واقعی به شماره مشتری ارسال شود؟')">ارسال پیام بازخورد تحویل</a></p>
                <?php $feedback = $order->get_meta('_rs_feedback_response'); $notReceived = $order->get_meta('_rs_feedback_no_response'); ?>
                <?php if (is_array($feedback)) : ?>
                  <p><strong>بازخورد تحویل:</strong> <?php echo esc_html((string) ($feedback['delivered_at'] ?? '')); ?><br>بسته‌بندی: <?php echo esc_html((string) ($feedback['pack_rating'] ?? '')); ?>/۵، محصول: <?php echo esc_html((string) ($feedback['product_rating'] ?? '')); ?>/۵، توصیه: <?php echo ($feedback['recommend'] ?? '') === 'yes' ? 'بله' : 'خیر'; ?></p>
                  <?php foreach ((array) ($feedback['images'] ?? []) as $photoId) : ?><p><a href="<?php echo esc_url(get_edit_post_link((int) $photoId)); ?>">مشاهده تصویر بازخورد در کتابخانه رسانه</a></p><?php endforeach; ?>
                <?php elseif (is_array($notReceived)) : ?><p><strong>مشتری دریافت نکرده:</strong> <?php echo esc_html((string) ($notReceived['reason'] ?? '')); ?> — <?php echo esc_html((string) ($notReceived['detail'] ?? '')); ?></p><?php endif; ?>
            <?php endif; ?>
            <p style="margin:6px 0">
                بسته‌بندی هدیه: <strong><?php echo $gift === 'yes' ? 'بله' : 'خیر'; ?></strong> |
                فاکتور رسمی: <strong><?php echo $invoice === 'yes' ? 'بله' : 'خیر'; ?></strong>
            </p>
        </div>
        <?php
    }

    public function saveOrderTrackingMetaBox(int $orderId, mixed $postOrOrder = null): void
    {
        if (!isset($_POST['rs_order_tracking_nonce']) || !wp_verify_nonce((string) $_POST['rs_order_tracking_nonce'], 'rs_save_order_tracking')) {
            return;
        }
        $order = wc_get_order($orderId);
        if (!$order) {
            return;
        }
        $order->update_meta_data('_rs_carrier_name', sanitize_text_field((string) wp_unslash($_POST['rs_carrier_name'] ?? '')));
        $order->update_meta_data('_rs_tracking_code', sanitize_text_field((string) wp_unslash($_POST['rs_tracking_code'] ?? '')));
        $order->save();
    }
}
