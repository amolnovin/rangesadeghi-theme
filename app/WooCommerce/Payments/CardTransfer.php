<?php
declare(strict_types=1);
namespace App\WooCommerce\Payments;

/** Offline card transfer; the merchant must verify payment manually. */
final class CardTransfer extends \WC_Payment_Gateway
{
    public function __construct()
    {
        $this->id = 'rs_card_transfer';
        $this->method_title = 'کارت به کارت';
        $this->method_description = 'پرداخت آفلاین کارت به کارت؛ سفارش تا تأیید پرداخت در انتظار بررسی می‌ماند.';
        $this->has_fields = false;
        $this->supports = ['products'];
        $this->init_form_fields();
        $this->init_settings();
        $this->title = (string) $this->get_option('title', 'کارت به کارت');
        $this->description = (string) $this->get_option('description', 'پس از ثبت سفارش، مبلغ را به شماره کارت اعلام‌شده واریز کنید. تأیید پرداخت توسط فروشگاه انجام می‌شود.');
        $this->enabled = (string) $this->get_option('enabled', 'no');
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        add_action('woocommerce_thankyou_' . $this->id, [$this, 'thankyouPage']);
        add_action('woocommerce_email_before_order_table', [$this, 'emailInstructions'], 10, 4);
    }

    public function init_form_fields(): void
    {
        $this->form_fields = [
            'enabled' => ['title' => 'فعال‌سازی', 'type' => 'checkbox', 'label' => 'فعال‌سازی پرداخت کارت به کارت', 'default' => 'no'],
            'title' => ['title' => 'عنوان', 'type' => 'text', 'default' => 'کارت به کارت'],
            'description' => ['title' => 'توضیح در تسویه حساب', 'type' => 'textarea', 'default' => 'پس از ثبت سفارش، مبلغ را به شماره کارت اعلام‌شده واریز کنید. تأیید پرداخت توسط فروشگاه انجام می‌شود.'],
            'card_number' => ['title' => 'شماره کارت ۱۶ رقمی', 'type' => 'text', 'default' => '', 'description' => 'شماره کارت واقعی فروشگاه را وارد کنید.', 'desc_tip' => true],
            'card_holder' => ['title' => 'نام صاحب کارت', 'type' => 'text', 'default' => ''],
            'instructions' => ['title' => 'راهنمای واریز و ارسال رسید', 'type' => 'textarea', 'default' => 'پس از واریز، برای تأیید پرداخت با فروشگاه تماس بگیرید.'],
        ];
    }

    public function is_available(): bool
    {
        $digits = preg_replace('/\D/', '', \App\Support\Persian::toEn((string) $this->get_option('card_number', '')));
        return parent::is_available() && strlen((string) $digits) === 16 && trim((string) $this->get_option('card_holder', '')) !== '';
    }

    private function instructions(): void
    {
        $number = (string) $this->get_option('card_number', '');
        $holder = (string) $this->get_option('card_holder', '');
        $instructions = (string) $this->get_option('instructions', '');
        echo '<section class="woocommerce-bacs-bank-details"><h2>اطلاعات کارت فروشگاه</h2><p><strong>شماره کارت:</strong> <bdi dir="ltr">' . esc_html($number) . '</bdi><br><strong>صاحب کارت:</strong> ' . esc_html($holder) . '</p>' . wpautop(esc_html($instructions)) . '</section>';
    }

    public function thankyouPage($orderId): void
    {
        $order = wc_get_order($orderId);
        if ($order && !$order->has_status(['cancelled', 'failed', 'refunded'])) $this->instructions();
    }

    public function emailInstructions($order, $sentToAdmin, $plainText, $email): void
    {
        if ($sentToAdmin || !$order || $order->get_payment_method() !== $this->id || !$order->has_status(['on-hold', 'pending'])) return;
        if ($plainText) {
            echo "\n" . wp_strip_all_tags('شماره کارت: ' . $this->get_option('card_number', '') . ' — صاحب کارت: ' . $this->get_option('card_holder', '') . ' — ' . $this->get_option('instructions', '')) . "\n";
        } else $this->instructions();
    }

    public function process_payment($orderId): array
    {
        $order = wc_get_order($orderId);
        if (!$order) return ['result' => 'failure'];
        if ($order->get_total() > 0) $order->update_status('on-hold', 'در انتظار تأیید واریز کارت به کارت توسط فروشگاه.');
        else $order->payment_complete();
        if (WC()->cart) WC()->cart->empty_cart();
        return ['result' => 'success', 'redirect' => $this->get_return_url($order)];
    }
}
