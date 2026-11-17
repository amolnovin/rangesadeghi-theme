<?php
/** Custom recipient and notes UI preserving WooCommerce shipping field hooks. @version 3.6.0 */
defined('ABSPATH') || exit;
?>
<div class="woocommerce-shipping-fields co-card rs-recipient-card">
    <header class="co-card-head"><span class="co-card-icon"><svg><use href="#i-user"/></svg></span><div class="co-card-title"><h2>مشخصات گیرنده</h2><p>سفارش به چه کسی تحویل داده شود؟</p></div></header>
    <?php if (WC()->cart->needs_shipping()) : ?>
        <input type="hidden" name="rs_recipient_present" value="1" />
        <label class="co-check"><input type="checkbox" id="rs-receiver-self" name="rs_receiver_self" value="1" <?php checked(!isset($_POST['rs_recipient_present']) || !empty($_POST['rs_receiver_self'])); ?> /><span class="co-check-box"><svg><use href="#i-check"/></svg></span><span>گیرنده سفارش خودم هستم <small data-rs-self-info></small></span></label>
        <div class="rs-fields-grid co-receiver" data-rs-receiver-fields>
            <p class="form-row"><label for="rs-receiver-name">نام و نام خانوادگی گیرنده <span class="required">*</span></label><input class="input-text" id="rs-receiver-name" name="rs_receiver_name" value="<?php echo esc_attr(sanitize_text_field(wp_unslash($_POST['rs_receiver_name'] ?? ''))); ?>" autocomplete="off" /></p>
            <p class="form-row"><label for="rs-receiver-phone">شماره موبایل گیرنده <span class="required">*</span></label><input class="input-text" id="rs-receiver-phone" name="rs_receiver_phone" type="tel" inputmode="tel" dir="ltr" value="<?php echo esc_attr(sanitize_text_field(wp_unslash($_POST['rs_receiver_phone'] ?? ''))); ?>" /></p>
        </div>
    <?php endif; ?>
    <?php if (true === WC()->cart->needs_shipping_address()) : ?>
        <details class="rs-shipping-details" data-rs-shipping-details <?php echo apply_filters('woocommerce_ship_to_different_address_checked', 'shipping' === get_option('woocommerce_ship_to_destination') ? 1 : 0) ? 'open' : ''; ?>>
            <summary>تنظیمات نشانی متفاوت ووکامرس</summary>
            <h3 id="ship-to-different-address"><label class="co-check"><input id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" <?php checked(apply_filters('woocommerce_ship_to_different_address_checked', 'shipping' === get_option('woocommerce_ship_to_destination') ? 1 : 0), 1); ?> type="checkbox" name="ship_to_different_address" value="1" /><span class="co-check-box"><svg><use href="#i-check"/></svg></span><span><?php esc_html_e('Ship to a different address?', 'woocommerce'); ?></span></label></h3>
            <div class="shipping_address co-address-form">
                <?php do_action('woocommerce_before_checkout_shipping_form', $checkout); ?>
                <div class="woocommerce-shipping-fields__field-wrapper rs-fields-grid"><?php foreach ($checkout->get_checkout_fields('shipping') as $key => $field) woocommerce_form_field($key, $field, $checkout->get_value($key)); ?></div>
                <?php do_action('woocommerce_after_checkout_shipping_form', $checkout); ?>
            </div>
        </details>
    <?php endif; ?>
</div>
<div class="woocommerce-additional-fields co-card rs-order-notes">
    <header class="co-card-head"><span class="co-card-icon"><svg><use href="#i-box"/></svg></span><div class="co-card-title"><h2>توضیحات سفارش</h2><p>در صورت نیاز، توضیحات مربوط به سفارش را بنویسید.</p></div></header>
    <?php do_action('woocommerce_before_order_notes', $checkout); ?>
    <?php if (apply_filters('woocommerce_enable_order_notes_field', 'yes' === get_option('woocommerce_enable_order_comments', 'yes'))) : ?>
        <div class="woocommerce-additional-fields__field-wrapper"><?php foreach ($checkout->get_checkout_fields('order') as $key => $field) woocommerce_form_field($key, $field, $checkout->get_value($key)); ?></div>
    <?php endif; ?>
    <?php do_action('woocommerce_after_order_notes', $checkout); ?>
</div>
