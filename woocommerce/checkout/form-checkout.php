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
<form name="checkout" method="post" class="checkout woocommerce-checkout cart-layout co-layout rs-native-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__('Checkout', 'woocommerce'); ?>">
    <div class="co-main">
        <section class="co-card rs-address-step" data-rs-checkout-panel="address">
            <header class="co-card-head"><span class="co-card-num">۱</span><div class="co-card-title"><h1>آدرس تحویل سفارش</h1><p>مشخصات و نشانی تحویل را وارد کنید.</p></div></header>
            <?php if ($checkout->get_checkout_fields()) : ?>
                <?php do_action('woocommerce_checkout_before_customer_details'); ?>
                <div id="customer_details">
                    <div><?php do_action('woocommerce_checkout_billing'); ?></div>
                    <div><?php do_action('woocommerce_checkout_shipping'); ?></div>
                </div>
                <?php do_action('woocommerce_checkout_after_customer_details'); ?>
            <?php endif; ?>
        </section>
        <section class="co-card rs-review-step" data-rs-checkout-panel="review">
            <header class="co-card-head"><span class="co-card-num">۲</span><div class="co-card-title"><h2 id="order_review_heading" data-rs-review-heading><?php esc_html_e('Your order', 'woocommerce'); ?></h2><p data-rs-review-description>روش ارسال و پرداخت را بررسی کنید.</p></div></header>
            <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>
            <?php do_action('woocommerce_checkout_before_order_review'); ?>
            <p class="rs-shipping-message" hidden data-rs-shipping-message><?php echo WC()->cart && !WC()->cart->needs_shipping() ? 'برای کالاهای این سفارش ارسال لازم نیست.' : 'برای مشاهده روش‌های ارسال، نشانی تحویل را کامل کنید.'; ?></p>
            <div id="order_review" class="woocommerce-checkout-review-order">
                <?php do_action('woocommerce_checkout_order_review'); ?>
            </div>
            <?php do_action('woocommerce_checkout_after_order_review'); ?>
        </section>
        <nav class="rs-checkout-controls" aria-label="جابه‌جایی مراحل خرید">
            <button type="button" class="btn btn-outline" data-rs-checkout-back>مرحله قبل</button>
            <button type="button" class="btn btn-primary" data-rs-checkout-next>ادامه</button>
        </nav>
    </div>
    <aside class="cart-side"><div class="summary-card"><?php wc_get_template('checkout/rs-summary.php'); ?></div></aside>
</form>
<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
