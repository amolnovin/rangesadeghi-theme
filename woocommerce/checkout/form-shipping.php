<?php
/** Custom recipient and notes UI preserving WooCommerce shipping field hooks. @version 3.6.0 */
defined('ABSPATH') || exit;
?>
<div class="woocommerce-shipping-fields co-card rs-recipient-card">
    <header class="co-card-head"><span class="co-card-icon"><svg><use href="#i-user"/></svg></span><div class="co-card-title"><h2>مشخصات گیرنده</h2><p>سفارش به چه کسی تحویل داده شود؟</p></div></header>
    <?php if (true === WC()->cart->needs_shipping_address()) : ?>
        <h3 id="ship-to-different-address"><label class="co-check"><input id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" <?php checked(apply_filters('woocommerce_ship_to_different_address_checked', 'shipping' === get_option('woocommerce_ship_to_destination') ? 1 : 0), 1); ?> type="checkbox" name="ship_to_different_address" value="1" /><span class="co-check-box"><svg><use href="#i-check"/></svg></span><span><?php esc_html_e('Ship to a different address?', 'woocommerce'); ?></span></label></h3>
        <div class="shipping_address co-address-form">
            <?php do_action('woocommerce_before_checkout_shipping_form', $checkout); ?>
            <div class="woocommerce-shipping-fields__field-wrapper rs-fields-grid"><?php foreach ($checkout->get_checkout_fields('shipping') as $key => $field) woocommerce_form_field($key, $field, $checkout->get_value($key)); ?></div>
            <?php do_action('woocommerce_after_checkout_shipping_form', $checkout); ?>
        </div>
    <?php else : ?><p class="rs-address-hint">اطلاعات صورت‌حساب برای این سفارش استفاده می‌شود.</p><?php endif; ?>
</div>
<div class="woocommerce-additional-fields co-card rs-order-notes">
    <header class="co-card-head"><span class="co-card-icon"><svg><use href="#i-box"/></svg></span><div class="co-card-title"><h2>توضیحات سفارش</h2><p>در صورت نیاز، توضیحات مربوط به سفارش را بنویسید.</p></div></header>
    <?php do_action('woocommerce_before_order_notes', $checkout); ?>
    <?php if (apply_filters('woocommerce_enable_order_notes_field', 'yes' === get_option('woocommerce_enable_order_comments', 'yes'))) : ?>
        <div class="woocommerce-additional-fields__field-wrapper"><?php foreach ($checkout->get_checkout_fields('order') as $key => $field) woocommerce_form_field($key, $field, $checkout->get_value($key)); ?></div>
    <?php endif; ?>
    <?php do_action('woocommerce_after_order_notes', $checkout); ?>
</div>
