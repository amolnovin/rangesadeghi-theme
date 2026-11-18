<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\ServiceProvider;
use WC_Order;
use WP_Error;

/** Delivery confirmation, securely linked to a single order; no access to the account is granted. */
final class DeliveryFeedbackServiceProvider extends ServiceProvider
{
    public const STATUS = 'rs-delivered';
    public const CRON = 'rs_delivery_feedback_reminder';
    public const SLUG = 'rangesadeghi-delivery';

    public function boot(): void
    {
        add_action('init', [$this, 'registerStatus']);
        add_filter('wc_order_statuses', [$this, 'orderStatuses']);
        add_filter('woocommerce_order_is_paid', static function ($paid, $order) {
            return $order->has_status(self::STATUS) ? ((bool) $order->get_date_paid() || $order->get_meta('_rs_feedback_was_paid') === 'yes') : $paid;
        }, 10, 2);
        add_action('init', [$this, 'registerSmsIntegration'], 30);
        add_action('woocommerce_order_status_changed', [$this, 'statusChanged'], 10, 4);
        add_action(self::CRON, [$this, 'scheduledReminder']);
        add_action('template_redirect', [$this, 'feedbackRoute'], 0);
        add_action('admin_post_rs_delivery_send', [$this, 'adminSend']);
        add_action('wp_ajax_rs_delivery_test', [$this, 'adminTest']);
    }

    public function registerStatus(): void
    {
        register_post_status('wc-' . self::STATUS, ['label' => 'تحویل شده', 'public' => true, 'exclude_from_search' => false, 'show_in_admin_all_list' => true, 'show_in_admin_status_list' => true, 'label_count' => _n_noop('تحویل شده <span class="count">(%s)</span>', 'تحویل شده <span class="count">(%s)</span>')]);
    }

    public function orderStatuses(array $statuses): array
    {
        $statuses['wc-' . self::STATUS] = 'تحویل شده';
        return $statuses;
    }

    /** Core writes the addon token into the shared option itself. No public key-receiving endpoint is needed. */
    public function registerSmsIntegration(): void
    {
        if (function_exists('ippanel_register_external_plugin')) {
            ippanel_register_external_plugin(['slug' => self::SLUG, 'name' => 'بازخورد تحویل رنگ صادقی', 'integration_code' => 'RS-DELIVERY-FEEDBACK', 'callback_url' => '']);
        }
    }

    public function shippingStatus(): string
    {
        $status = (string) rs_option('shop.tracking_shipping', '');
        $statuses = function_exists('wc_get_order_statuses') ? wc_get_order_statuses() : [];
        // Paid, cancelled or delivered statuses must never be treated as shipping automatically.
        return isset($statuses[$status]) && !in_array($status, ['wc-completed', 'wc-cancelled', 'wc-failed', 'wc-refunded', 'wc-' . self::STATUS], true) ? substr($status, 3) : '';
    }

    public function statusChanged($id, $from, $to, $order): void
    {
        if (!$order instanceof WC_Order) return;
        wp_clear_scheduled_hook(self::CRON, [(int) $id]);
        if ($to === $this->shippingStatus() && $to !== '' && rs_option('delivery_feedback.enabled', false)) {
            $wasPaid = in_array($from, wc_get_is_paid_statuses(), true) || (bool) $order->get_date_paid();
            $order->update_meta_data('_rs_feedback_was_paid', $wasPaid ? 'yes' : 'no');
            $order->delete_meta_data('_rs_feedback_resolved');
            $order->delete_meta_data('_rs_feedback_token');
            $order->delete_meta_data('_rs_feedback_no_response');
            $order->save();
            wp_schedule_single_event(time() + 72 * HOUR_IN_SECONDS, self::CRON, [(int) $id]);
        }
    }

    public function eligible(WC_Order $order): bool
    {
        $shipped = $this->shippingStatus();
        return $shipped !== '' && $order->has_status($shipped) && !$order->get_meta('_rs_feedback_resolved');
    }

    public function link(WC_Order $order): string
    {
        $token = (string) $order->get_meta('_rs_feedback_token');
        if ($token === '') {
            $token = rtrim(strtr(base64_encode(random_bytes(16)), '+/', '-_'), '=');
            $order->update_meta_data('_rs_feedback_token', $token);
            $order->save();
        }
        return add_query_arg('rsd', $order->get_id() . '.' . $token, home_url('/'));
    }

    public function validToken(WC_Order $order, string $token): bool
    {
        $stored = (string) $order->get_meta('_rs_feedback_token');
        return $stored !== '' && hash_equals($stored, $token);
    }

    public function send(WC_Order $order): bool|WP_Error
    {
        global $wpdb;
        $lock = 'rs-feedback-' . $order->get_id();
        if ((int) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 0)', $lock)) !== 1) return new WP_Error('busy', 'عملیات دیگری برای سفارش در حال انجام است.');
        try {
            return $this->sendLocked($order);
        } finally {
            $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
        }
    }

    private function sendLocked(WC_Order $order): bool|WP_Error
    {
        $order = wc_get_order($order->get_id());
        if (!$order || !$this->eligible($order)) return new WP_Error('not_shipped', 'سفارش در وضعیت ارسال انتخاب‌شده نیست یا قبلاً تحویل شده است.');
        if (time() - (int) $order->get_meta('_rs_feedback_last_sent') < 60) return new WP_Error('already_sent', 'پیامک اخیراً ارسال شده است؛ لطفاً یک دقیقه صبر کنید.');
        if (!function_exists('ippanel_send_external_sms') || !function_exists('ippanel_get_external_plugin_token')) return new WP_Error('missing_core', 'افزونه IPPanel Core فعال نیست.');
        $token = (string) ippanel_get_external_plugin_token(self::SLUG);
        if ($token === '') return new WP_Error('missing_token', 'اتصال افزونه در IPPanel Core تنظیم نشده است.');
        $phone = \App\Support\Persian::toEn((string) $order->get_billing_phone());
        $phone = preg_replace('/[\s\-()]+/', '', $phone) ?? '';
        if (!preg_match('/^(?:\+98|0)9\d{9}$/', $phone)) return new WP_Error('invalid_phone', 'شماره موبایل صورتحساب معتبر نیست.');
        $url = $this->link($order);
        $mode = (string) rs_option('delivery_feedback.mode', 'text');
        $number = (string) $order->get_order_number();
        if ($mode === 'pattern') {
            $pattern = trim((string) rs_option('delivery_feedback.pattern', ''));
            if ($pattern === '') return new WP_Error('pattern_missing', 'کد پترن تنظیم نشده است.');
            $result = ippanel_send_external_sms($token, $phone, $pattern, ['order_id' => $number, 'link' => $url], 'pattern');
        } else {
            $text = (string) rs_option('delivery_feedback.text', '');
            if (trim($text) === '' || !str_contains($text, '{link}')) return new WP_Error('text_missing', 'متن پیامک باید شامل {link} باشد.');
            $result = ippanel_send_external_sms($token, $phone, strtr($text, ['{order_id}' => $number, '{link}' => $url]), [], 'text');
        }
        if (is_wp_error($result)) return $result;
        if ($result !== true) return new WP_Error('send_failed', 'ارسال پیامک تأیید نشد.');
        $order->add_order_note('پیامک بازخورد تحویل به شماره صورتحساب ارسال شد.');
        $order->update_meta_data('_rs_feedback_last_sent', time());
        $order->save();
        return true;
    }

    public function scheduledReminder($id): void
    {
        global $wpdb;
        $id = (int) $id;
        $lock = 'rs-feedback-' . $id;
        if ((int) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 0)', $lock)) !== 1) {
            wp_schedule_single_event(time() + 5 * MINUTE_IN_SECONDS, self::CRON, [$id]);
            return;
        }
        try {
            $order = wc_get_order($id);
            if (!$order || !rs_option('delivery_feedback.enabled', false) || !$this->eligible($order)) return;
            $result = $this->sendLocked($order);
            wp_clear_scheduled_hook(self::CRON, [$id]);
            if (is_wp_error($result)) {
                $order->add_order_note('ارسال پیامک بازخورد ناموفق بود: ' . $result->get_error_message());
                wp_schedule_single_event(time() + DAY_IN_SECONDS, self::CRON, [$id]);
            } elseif ($order->get_meta('_rs_feedback_no_response')) {
                wp_schedule_single_event(time() + 4 * DAY_IN_SECONDS, self::CRON, [$id]);
            }
        } finally {
            $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
        }
    }

    public function adminSend(): void
    {
        if (!current_user_can('manage_woocommerce')) wp_die('عدم دسترسی', '', ['response' => 403]);
        $id = absint($_GET['order_id'] ?? 0);
        check_admin_referer('rs_delivery_send_' . $id);
        $order = wc_get_order($id);
        $result = $order ? $this->send($order) : new WP_Error('missing', 'سفارش پیدا نشد.');
        if (is_wp_error($result)) wp_die(esc_html($result->get_error_message()), 'ارسال ناموفق', ['response' => 400, 'back_link' => true]);
        wp_safe_redirect($order->get_edit_order_url());
        exit;
    }

    public function adminTest(): void
    {
        check_ajax_referer('rs_admin_nonce', 'nonce');
        if (!current_user_can('manage_woocommerce')) wp_send_json_error(['message' => 'عدم دسترسی'], 403);
        $order = wc_get_order(absint($_POST['order_id'] ?? 0));
        $result = $order ? $this->send($order) : new WP_Error('missing', 'سفارش پیدا نشد.');
        if (is_wp_error($result)) wp_send_json_error(['message' => $result->get_error_message()]);
        wp_send_json_success(['message' => 'پیامک به شماره مشتری ارسال شد.']);
    }

    /** The bearer token authenticates only this form, never the account or order details. */
    public function feedbackRoute(): void
    {
        if (!isset($_GET['rsd']) && !isset($_GET['rs_delivery'])) return;
        nocache_headers();
        header('Referrer-Policy: no-referrer');
        header('X-Robots-Tag: noindex, nofollow', true);
        $parts = explode('.', (string) wp_unslash($_GET['rsd'] ?? ''), 2);
        $id = absint($_GET['rs_delivery'] ?? ($parts[0] ?? 0));
        $token = sanitize_text_field((string) wp_unslash($_GET['t'] ?? ($parts[1] ?? '')));
        $order = rs_is_wc() ? wc_get_order($id) : false;
        $valid = $order && $this->validToken($order, $token);
        $message = '';
        $success = false;
        if (!$valid) {
            status_header(404);
            $message = 'این پیوند معتبر نیست یا در دسترس قرار ندارد.';
        } elseif (!$this->eligible($order)) {
            $message = $order->get_meta('_rs_feedback_resolved') ? 'بازخورد این سفارش قبلاً ثبت شده است. سپاس از شما.' : 'در حال حاضر امکان ثبت بازخورد این سفارش وجود ندارد.';
            $success = (bool) $order->get_meta('_rs_feedback_resolved');
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!wp_verify_nonce((string) ($_POST['_wpnonce'] ?? ''), 'rs_feedback_' . $id . '_' . $token)) {
                $message = 'درخواست معتبر نیست. صفحه را دوباره باز کنید.';
            } else {
                $result = $this->saveResponse($order, wp_unslash($_POST));
                if (is_wp_error($result)) $message = $result->get_error_message();
                else { $success = true; $message = $result === 'yes' ? 'از ثبت نظر و تأیید تحویل شما سپاسگزاریم.' : 'از اطلاع‌رسانی شما سپاسگزاریم. وضعیت سفارش تغییر نکرد و پیگیری ادامه دارد.'; }
            }
        }
        $items = [];
        if ($valid && !$success && $this->eligible($order)) {
            foreach ($order->get_items() as $item) {
                if ($item instanceof \WC_Order_Item_Product && $item->get_product_id() && get_post_status($item->get_product_id()) === 'publish') $items[$item->get_product_id()] = $item->get_name();
            }
        }
        include get_template_directory() . '/resources/views/woocommerce/delivery-feedback.php';
        exit;
    }

    /** @param array<string,mixed> $data */
    public function saveResponse(WC_Order $order, array $data): string|WP_Error
    {
        global $wpdb;
        $lock = 'rs-feedback-' . $order->get_id();
        if ((int) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 0)', $lock)) !== 1) return new WP_Error('busy', 'درخواست دیگری در حال ثبت است. دوباره تلاش کنید.');
        try {
            $order = wc_get_order($order->get_id());
            if (!$order || !$this->eligible($order)) return new WP_Error('ineligible', 'امکان ثبت مجدد بازخورد وجود ندارد.');
            $answer = sanitize_key((string) ($data['received'] ?? ''));
            if ($answer === 'no') {
                $last = $order->get_meta('_rs_feedback_no_response');
                if (is_array($last) && time() - (int) ($last['at'] ?? 0) < 60) return new WP_Error('rate_limit', 'پاسخ شما ثبت شده است. لطفاً کمی بعد دوباره تلاش کنید.');
                $reasons = ['carrier' => 'نزد شرکت ارسال‌کننده است و تحویل داده نشده', 'collection' => 'نزد شرکت ارسال‌کننده است و خودم مراجعه نکرده‌ام', 'unknown' => 'از وضعیت ارسال اطلاعی ندارم'];
                $reason = sanitize_key((string) ($data['reason'] ?? ''));
                if (!isset($reasons[$reason])) return new WP_Error('reason', 'لطفاً علت دریافت نکردن سفارش را انتخاب کنید.');
                $contacted = sanitize_key((string) ($data['contacted'] ?? ''));
                if (!in_array($contacted, ['yes', 'no'], true)) return new WP_Error('contacted', 'لطفاً وضعیت پیگیری از شرکت ارسال‌کننده را انتخاب کنید.');
                $detail = mb_substr(sanitize_textarea_field((string) ($data['no_detail'] ?? '')), 0, 1000);
                $order->update_meta_data('_rs_feedback_no_response', ['reason' => $reason, 'contacted' => $contacted, 'detail' => $detail, 'at' => time()]);
                $order->add_order_note('مشتری دریافت نکرده است: ' . $reasons[$reason] . '؛ پیگیری از شرکت: ' . ($contacted === 'yes' ? 'بله' : 'خیر') . ($detail !== '' ? '؛ توضیح: ' . $detail : ''));
                $order->save();
                wp_clear_scheduled_hook(self::CRON, [$order->get_id()]);
                if (rs_option('delivery_feedback.enabled', false)) wp_schedule_single_event(time() + 4 * DAY_IN_SECONDS, self::CRON, [$order->get_id()]);
                return 'no';
            }
            if ($answer !== 'yes') return new WP_Error('answer', 'دریافت یا عدم دریافت سفارش را انتخاب کنید.');
            $date = sanitize_text_field((string) ($data['delivery_datetime'] ?? ''));
            $time = \DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $date, wp_timezone());
            if (!$time || $time->format('Y-m-d\TH:i') !== $date || $time->getTimestamp() > time()) return new WP_Error('date', 'روز و ساعت معتبر و گذشته برای تحویل وارد کنید.');
            $pack = absint($data['pack_rating'] ?? 0); $productRating = absint($data['product_rating'] ?? 0);
            $recommend = sanitize_key((string) ($data['recommend'] ?? ''));
            if ($pack < 1 || $pack > 5 || $productRating < 1 || $productRating > 5 || !in_array($recommend, ['yes', 'no'], true)) return new WP_Error('rating', 'امتیازها و پیشنهاد محصول را انتخاب کنید.');
            $review = trim(sanitize_textarea_field((string) ($data['review'] ?? '')));
            if (mb_strlen($review) > 2000) return new WP_Error('review_length', 'متن نظر نباید بیشتر از ۲۰۰۰ نویسه باشد.');
            $productId = absint($data['product_id'] ?? 0);
            $allowed = [];
            foreach ($order->get_items() as $item) if ($item instanceof \WC_Order_Item_Product) $allowed[] = (int) $item->get_product_id();
            if ($review !== '' && (!in_array($productId, $allowed, true) || get_post_status($productId) !== 'publish')) return new WP_Error('product', 'محصول سفارش را برای ثبت نظر انتخاب کنید.');
            $photos = $this->savePhotos();
            if (is_wp_error($photos)) return $photos;
            $commentId = 0;
            if ($review !== '') {
                $commentId = wp_insert_comment(['comment_post_ID' => $productId, 'comment_author' => $order->get_formatted_billing_full_name() ?: 'مشتری', 'comment_author_email' => $order->get_billing_email(), 'comment_content' => $review, 'comment_type' => 'review', 'comment_approved' => '0', 'user_id' => (int) $order->get_customer_id()]);
                if (!$commentId) {
                    foreach ($photos as $photoId) wp_delete_attachment($photoId, true);
                    return new WP_Error('comment', 'ثبت نظر ممکن نشد. دوباره تلاش کنید.');
                }
                update_comment_meta($commentId, 'rating', $productRating);
                update_comment_meta($commentId, 'verified', 1);
                update_comment_meta($commentId, '_rs_recommend', $recommend);
                update_comment_meta($commentId, '_rs_feedback_images', $photos);
            }
            if ($order->is_paid() || $order->get_date_paid()) $order->update_meta_data('_rs_feedback_was_paid', 'yes');
            $order->update_meta_data('_rs_feedback_response', ['delivered_at' => $date, 'pack_rating' => $pack, 'product_rating' => $productRating, 'recommend' => $recommend, 'comment_id' => (int) $commentId, 'images' => $photos, 'at' => time()]);
            $order->update_meta_data('_rs_feedback_resolved', 'yes');
            $order->delete_meta_data('_rs_feedback_no_response');
            $order->save();
            wp_clear_scheduled_hook(self::CRON, [$order->get_id()]);
            $order->update_status(self::STATUS, 'تحویل توسط مشتری تأیید شد؛ بازخورد در اطلاعات سفارش ثبت شد.');
            return 'yes';
        } finally {
            $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
        }
    }

    /** Store validated images in the media library, for administrators and moderated reviews. */
    private function savePhotos(): array|WP_Error
    {
        $file = $_FILES['photos'] ?? null;
        if (!$file || empty($file['name'][0])) return [];
        if (!is_array($file['name']) || count($file['name']) > 3) return new WP_Error('photos', 'حداکثر سه تصویر انتخاب کنید.');
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        foreach ($file['name'] as $i => $name) {
            $tmp = $file['tmp_name'][$i] ?? '';
            $image = is_uploaded_file($tmp) ? wp_getimagesize($tmp) : false;
            if (($file['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || ($file['size'][$i] ?? 0) > 3 * MB_IN_BYTES || !$image || !isset($allowed[$image['mime'] ?? '']) || $image[0] * $image[1] > 20000000) return new WP_Error('photo_invalid', 'فقط تصاویر JPG، PNG یا WebP تا ۳ مگابایت و حداکثر ۲۰ مگاپیکسل پذیرفته می‌شوند.');
        }
        $ids = [];
        foreach ($file['name'] as $i => $name) {
            $single = ['name' => sanitize_file_name((string) $name), 'type' => $file['type'][$i], 'tmp_name' => $file['tmp_name'][$i], 'error' => $file['error'][$i], 'size' => $file['size'][$i]];
            $uploaded = wp_handle_upload($single, ['test_form' => false, 'mimes' => ['jpg|jpeg|jpe' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp']]);
            if (isset($uploaded['error'])) {
                foreach ($ids as $id) wp_delete_attachment($id, true);
                return new WP_Error('photo_upload', 'ذخیره تصویر ناموفق بود.');
            }
            $id = wp_insert_attachment(['post_mime_type' => $uploaded['type'], 'post_title' => 'تصویر بازخورد سفارش', 'post_status' => 'inherit'], $uploaded['file']);
            if (is_wp_error($id) || !$id) {
                wp_delete_file($uploaded['file']);
                foreach ($ids as $previousId) wp_delete_attachment($previousId, true);
                return new WP_Error('photo_attachment', 'ثبت تصویر ناموفق بود.');
            }
            wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $uploaded['file']));
            $ids[] = (int) $id;
        }
        return $ids;
    }
}
