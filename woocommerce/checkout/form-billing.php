<?php
/** Custom checkout billing UI; WooCommerce owns field definitions and validation. @version 3.6.0 */
defined('ABSPATH') || exit;
$saved = is_user_logged_in() ? get_user_meta(get_current_user_id(), '_rs_saved_addresses', true) : [];
$saved = is_array($saved) ? $saved : [];
?>
<div class="woocommerce-billing-fields">
    <?php if ($saved) : ?>
        <div class="co-address-list" data-rs-saved-list hidden role="group" aria-label="آدرس‌های ذخیره‌شده">
            <?php foreach ($saved as $address) : if (!is_array($address) || empty($address['address'])) continue; ?>
                <button type="button" class="co-address rs-saved-address" data-address="<?php echo esc_attr(wp_json_encode($address)); ?>">
                    <span class="co-radio" aria-hidden="true"></span>
                    <span class="co-address-body"><b><?php echo esc_html($address['title'] ?? 'آدرس'); ?></b><span class="co-address-text"><?php echo esc_html(implode('، ', array_filter([$address['province'] ?? '', $address['city'] ?? '', $address['address'] ?? '']))); ?></span><span class="co-address-meta"><?php echo esc_html(implode(' — ', array_filter([$address['name'] ?? '', $address['phone'] ?? '']))); ?></span></span>
                </button>
            <?php endforeach; ?>
        </div>
        <p class="rs-address-hint">با انتخاب آدرس، مشخصات زیر را بررسی و در صورت نیاز تکمیل کنید.</p>
    <?php endif; ?>
    <div class="co-address-form">
        <h3><svg><use href="#i-pin"/></svg><?php echo wc_ship_to_billing_address_only() && WC()->cart->needs_shipping() ? esc_html__('Billing &amp; Shipping', 'woocommerce') : esc_html__('Billing details', 'woocommerce'); ?></h3>
        <?php do_action('woocommerce_before_checkout_billing_form', $checkout); ?>
        <div class="woocommerce-billing-fields__field-wrapper rs-fields-grid">
            <?php foreach ($checkout->get_checkout_fields('billing') as $key => $field) woocommerce_form_field($key, $field, $checkout->get_value($key)); ?>
        </div>
        <?php do_action('woocommerce_after_checkout_billing_form', $checkout); ?>
    </div>
</div>
<?php if (!is_user_logged_in() && $checkout->is_registration_enabled()) : ?>
    <div class="woocommerce-account-fields co-address-form">
        <?php if (!$checkout->is_registration_required()) : ?>
            <p class="form-row form-row-wide create-account"><label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox"><input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked(true === $checkout->get_value('createaccount') || true === apply_filters('woocommerce_create_account_default_checked', false)); ?> type="checkbox" name="createaccount" value="1" /> <span><?php esc_html_e('Create an account?', 'woocommerce'); ?></span></label></p>
        <?php endif; ?>
        <?php do_action('woocommerce_before_checkout_registration_form', $checkout); ?>
        <?php if ($checkout->get_checkout_fields('account')) : ?><div class="create-account"><?php foreach ($checkout->get_checkout_fields('account') as $key => $field) woocommerce_form_field($key, $field, $checkout->get_value($key)); ?><div class="clear"></div></div><?php endif; ?>
        <?php do_action('woocommerce_after_checkout_registration_form', $checkout); ?>
    </div>
<?php endif; ?>
