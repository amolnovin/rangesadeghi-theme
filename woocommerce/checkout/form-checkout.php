<?php
/**
 * Checkout form, adapted to the Rang Sadeghi checkout layout.
 *
 * WooCommerce continues to own field rendering, totals, shipping, payment,
 * validation and order creation. This template intentionally calls its hooks.
 * @version 9.4.0
 */
defined('ABSPATH') || exit;

do_action('woocommerce_before_checkout_form', $checkout);

if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
    echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
    return;
}
?>
<?php wc_get_template('checkout/rs-progress.php', ['step' => 'address']); ?>
<noscript><style>form.checkout_coupon { display: block !important; } .rs-native-checkout [data-rs-slot-card][hidden] { display: block !important; }</style></noscript>
<form name="checkout" method="post" class="checkout woocommerce-checkout cart-layout co-layout rs-native-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__('Checkout', 'woocommerce'); ?>">
    <div class="co-main">
        <section class="co-step rs-address-step" data-rs-checkout-panel="address">
            <?php if ($checkout->get_checkout_fields()) : ?>
                <?php do_action('woocommerce_checkout_before_customer_details'); ?>
                <div id="customer_details">
                    <div class="co-card col-1"><header class="co-card-head"><span class="co-card-num">۱</span><div class="co-card-title"><h1>آدرس تحویل سفارش</h1><p>یکی از آدرس‌های ذخیره‌شده را انتخاب کنید یا آدرس جدید ثبت کنید.</p></div><?php if (is_user_logged_in()) : ?><a class="text-link co-head-link" href="<?php echo esc_url(wc_get_page_permalink('myaccount') . '#addresses'); ?>">مدیریت آدرس‌ها</a><?php endif; ?></header>
                        <?php do_action('woocommerce_checkout_billing'); ?>
                    </div>
                    <div class="col-2"><?php do_action('woocommerce_checkout_shipping'); ?></div>
                </div>
                <?php do_action('woocommerce_checkout_after_customer_details'); ?>
            <?php endif; ?>
        </section>
        <section class="co-step rs-review-step" data-rs-checkout-panel="review">
            <header class="co-card-head co-card rs-review-heading"><span class="co-card-num">۲</span><div class="co-card-title"><h2 id="order_review_heading" data-rs-review-heading><?php esc_html_e('Your order', 'woocommerce'); ?></h2><p data-rs-review-description>روش ارسال و پرداخت را بررسی کنید.</p></div></header>
            <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>
            <?php do_action('woocommerce_checkout_before_order_review'); ?>
            <p class="rs-shipping-message" hidden data-rs-shipping-message><?php echo WC()->cart && !WC()->cart->needs_shipping() ? 'برای کالاهای این سفارش ارسال لازم نیست.' : 'برای مشاهده روش‌های ارسال، نشانی تحویل را کامل کنید.'; ?></p>
            <div id="order_review" class="woocommerce-checkout-review-order">
                <?php do_action('woocommerce_checkout_order_review'); ?>
            </div>
            <?php do_action('woocommerce_checkout_after_order_review'); ?>
        </section>
        <div data-rs-shipping-extra>
            <?php if (WC()->cart && WC()->cart->needs_shipping() && rs_option('shop.delivery_slots_enabled', true)) :
                $days = max(1, min(30, (int) rs_option('shop.delivery_days_ahead', 6)));
                $today = new DateTimeImmutable('today', wp_timezone());
                $postedDay = sanitize_text_field((string) wp_unslash($_POST['rs_delivery_day'] ?? ''));
                $postedSlot = sanitize_text_field((string) wp_unslash($_POST['rs_delivery_slot'] ?? ''));
            ?>
            <div class="co-card rs-delivery-card" data-rs-slot-card hidden><header class="co-card-head"><span class="co-card-icon"><svg><use href="#i-calendar"/></svg></span><div class="co-card-title"><h2>زمان تحویل</h2><p>روز و بازه پیشنهادی را انتخاب کنید؛ زمان نهایی پس از تأیید فروشگاه است.</p><p data-rs-pickup-address hidden><?php echo esc_html((string) rs_option('shop.pickup_address', '')); ?></p></div></header>
                <div class="co-days" role="radiogroup" aria-label="روز تحویل"><?php for ($i = 0; $i < $days; $i++) : $date = $today->modify('+' . $i . ' days'); $value = $date->format('Y-m-d'); ?>
                    <label class="co-day"><input type="radio" name="rs_delivery_day" value="<?php echo esc_attr($value); ?>" <?php checked($postedDay, $value); ?> /><b><?php echo $i === 0 ? 'امروز' : ($i === 1 ? 'فردا' : esc_html(wp_date('l', $date->getTimestamp()))); ?></b><small><?php echo esc_html(\App\Support\Persian::jalaliDate($date->setTime(12, 0)->getTimestamp())); ?></small></label>
                <?php endfor; ?></div>
                <div class="co-slots" role="radiogroup" aria-label="بازه زمانی تحویل"><?php foreach (array_filter(array_map('trim', explode(',', (string) rs_option('shop.delivery_slots', '')))) as $slot) : ?>
                    <label class="co-slot"><input type="radio" name="rs_delivery_slot" value="<?php echo esc_attr($slot); ?>" <?php checked($postedSlot, $slot); ?> /><svg><use href="#i-clock"/></svg><?php echo esc_html($slot); ?></label>
                <?php endforeach; ?></div>
            </div><?php endif; ?>
            <div class="co-card co-eta-card" data-rs-eta-card hidden><span class="co-card-icon"><svg><use href="#i-clock"/></svg></span><div><b>زمان تقریبی تحویل</b><p data-rs-eta-text></p></div></div>
            <div class="co-card"><header class="co-card-head"><span class="co-card-icon"><svg><use href="#i-box"/></svg></span><div class="co-card-title"><h2>مرسوله شما</h2><p>کالاهای سفارش شما</p></div></header><div class="co-shipment-thumbs"><?php foreach (WC()->cart->get_cart() as $item) : if (empty($item['data']) || !$item['data']->exists()) continue; ?><span class="co-thumb"><?php echo wp_kses_post($item['data']->get_image('woocommerce_thumbnail')); ?><em><?php echo esc_html(rs_fa($item['quantity'])); ?></em></span><?php endforeach; ?></div>
                <?php if (rs_option('shop.gift_wrap_enabled', true)) : $gift_cost = max(0, (int) rs_option('shop.gift_wrap_cost', 35000)); if (get_woocommerce_currency() === 'IRR') $gift_cost *= 10; ?>
                <input type="hidden" name="rs_gift_wrap" value="0" /><label class="co-check"><input type="checkbox" name="rs_gift_wrap" value="1" class="update_totals_on_change" <?php checked(isset($_POST['rs_gift_wrap']) ? !empty($_POST['rs_gift_wrap']) : (bool) (WC()->session && WC()->session->get('rs_gift_wrap'))); ?> /><span class="co-check-box"><svg><use href="#i-check"/></svg></span><span>بسته‌بندی هدیه همراه کارت پیام <small>(<?php echo wp_kses_post(wc_price($gift_cost)); ?>)</small></span></label><?php endif; ?>
            </div>
        </div>
        <div data-rs-payment-extra>
            <div class="co-card" data-rs-final-review hidden><header class="co-card-head"><span class="co-card-icon"><svg><use href="#i-check-circle"/></svg></span><div class="co-card-title"><h2>بازبینی نهایی سفارش</h2><p>پیش از پرداخت، اطلاعات زیر را بررسی کنید.</p></div></header><div class="co-review"><div class="co-review-row"><svg><use href="#i-pin"/></svg><div><small>نشانی تحویل</small><b data-rs-review-address>—</b></div><button type="button" data-rs-edit-address>ویرایش</button></div><div class="co-review-row"><svg><use href="#i-truck"/></svg><div><small>شیوه ارسال</small><b data-rs-review-shipping>—</b></div></div><div class="co-review-row"><svg><use href="#i-card"/></svg><div><small>روش پرداخت</small><b data-rs-review-payment>—</b></div></div></div></div>
            <?php if (wc_coupons_enabled()) : ?><div class="co-card rs-coupon-card" hidden data-rs-coupon-card><header class="co-card-head"><span class="co-card-icon"><svg><use href="#i-ticket"/></svg></span><div class="co-card-title"><h2>کد تخفیف</h2><p>کد تخفیف خود را وارد کنید.</p></div></header><div class="coupon co-coupon"><input type="text" data-rs-coupon-input aria-label="کد تخفیف" autocomplete="off" dir="ltr" /><button type="button" data-rs-coupon-submit>ثبت کد</button></div><p class="coupon-note" data-rs-coupon-notice role="status" aria-live="polite"></p></div><?php endif; ?>
            <?php if (rs_option('shop.official_invoice_enabled', true)) : ?><div class="co-card"><label class="co-check"><input type="checkbox" name="rs_official_invoice" value="1" <?php checked(!empty($_POST['rs_official_invoice'])); ?> /><span class="co-check-box"><svg><use href="#i-check"/></svg></span><span>درخواست فاکتور رسمی</span></label></div><?php endif; ?>
        </div>
        <nav class="co-nav rs-checkout-controls" aria-label="جابه‌جایی مراحل خرید">
            <button type="button" class="btn btn-outline" data-rs-checkout-back>مرحله قبل</button>
            <button type="button" class="btn btn-primary" data-rs-checkout-next>ادامه</button>
        </nav>
    </div>
    <aside class="cart-side"><div class="summary-card"><?php wc_get_template('checkout/rs-summary.php'); ?></div></aside>
</form>
<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
