<?php
/** Account addresses and standard WooCommerce billing fields. @version 3.6.0 */
defined('ABSPATH') || exit;
$country = (string) ($checkout->get_value('billing_country') ?: WC()->countries->get_base_country());
$states = WC()->countries->get_states($country);
$states = is_array($states) ? $states : [];
$stateLabel = static function (string $value, string $country): string {
    $states = WC()->countries->get_states($country);
    return is_array($states) ? (string) ($states[$value] ?? $value) : $value;
};
$saved = is_user_logged_in() ? get_user_meta(get_current_user_id(), '_rs_saved_addresses', true) : [];
$saved = is_array($saved) ? array_values(array_filter($saved, static fn($item) => is_array($item) && !empty($item['address']))) : [];
if (!$saved && is_user_logged_in() && $checkout->get_value('billing_address_1')) {
    // The account page exposes the customer's WooCommerce billing address when no custom address exists.
    $saved[] = [
        'id' => 'billing-default', 'title' => 'آدرس پیش‌فرض', 'isDefault' => true,
        'name' => trim($checkout->get_value('billing_first_name') . ' ' . $checkout->get_value('billing_last_name')),
        'phone' => $checkout->get_value('billing_phone'), 'province' => $checkout->get_value('billing_state'),
        'city' => $checkout->get_value('billing_city'), 'postal' => $checkout->get_value('billing_postcode'),
        'address' => $checkout->get_value('billing_address_1'), 'address2' => $checkout->get_value('billing_address_2'),
    ];
}
?>
<div class="woocommerce-billing-fields">
    <?php if (is_user_logged_in()) : ?>
        <div data-rs-address-picker data-rs-requires-address="<?php echo WC()->cart && WC()->cart->needs_shipping() ? '1' : '0'; ?>" data-rs-submitted="<?php echo isset($_POST['rs_address_picker_present']) ? '1' : '0'; ?>" data-rs-selected="<?php echo esc_attr(sanitize_text_field(wp_unslash($_POST['rs_saved_address'] ?? ''))); ?>" hidden>
            <input type="hidden" name="rs_address_picker_present" value="1" />
            <div class="co-address-list" data-rs-saved-list role="radiogroup" aria-label="انتخاب آدرس تحویل">
                <?php foreach ($saved as $index => $address) : ?>
                    <?php $data = array_intersect_key($address, array_flip(['id', 'title', 'name', 'phone', 'province', 'province_label', 'country', 'city', 'postal', 'address', 'address2', 'isDefault'])); ?>
                    <label class="co-address rs-saved-address" data-address="<?php echo esc_attr(wp_json_encode($data)); ?>">
                        <input type="radio" name="rs_saved_address" value="<?php echo esc_attr((string) ($address['id'] ?? $index)); ?>" />
                        <span class="co-radio" aria-hidden="true"></span><span class="co-address-body">
                            <b><?php echo esc_html($address['title'] ?? 'آدرس'); ?><?php if (!empty($address['isDefault'])) : ?><span class="default-chip">پیش‌فرض</span><?php endif; ?></b>
                            <span class="co-address-text"><?php echo esc_html(implode('، ', array_filter([$address['province_label'] ?? $stateLabel((string) ($address['province'] ?? ''), (string) ($address['country'] ?? $country)), $address['city'] ?? '', $address['address'] ?? '']))); ?></span>
                            <span class="co-address-meta"><?php if (!empty($address['name'])) : ?><span><svg><use href="#i-user"/></svg><?php echo esc_html($address['name']); ?></span><?php endif; ?><?php if (!empty($address['phone'])) : ?><span><svg><use href="#i-phone"/></svg><bdi><?php echo esc_html(rs_fa($address['phone'])); ?></bdi></span><?php endif; ?><?php if (!empty($address['postal'])) : ?><span><svg><use href="#i-mail"/></svg>کد پستی <bdi><?php echo esc_html(rs_fa($address['postal'])); ?></bdi></span><?php endif; ?></span>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
            <p class="rs-address-hint" data-rs-address-empty <?php echo $saved ? 'hidden' : ''; ?>>هنوز آدرسی در حساب شما ثبت نشده است.</p>
            <button class="co-add-address" type="button" data-rs-add-address aria-expanded="false" aria-controls="rs-new-address"><span><svg><use href="#i-plus"/></svg></span>افزودن آدرس جدید</button>
            <div class="co-address-form" id="rs-new-address" data-rs-new-address hidden>
                <h3><svg><use href="#i-pin"/></svg>ثبت آدرس جدید</h3>
                <div class="rs-fields-grid">
                    <p class="form-row"><label for="rs-new-title">عنوان آدرس <span class="required">*</span></label><input class="input-text" id="rs-new-title" data-rs-new="title" placeholder="مثلاً منزل یا محل کار" /></p>
                    <p class="form-row"><label for="rs-new-name">نام و نام خانوادگی گیرنده <span class="required">*</span></label><input class="input-text" id="rs-new-name" data-rs-new="name" autocomplete="name" /></p>
                    <p class="form-row"><label for="rs-new-phone">شماره موبایل <span class="required">*</span></label><input class="input-text" id="rs-new-phone" data-rs-new="phone" type="tel" inputmode="tel" dir="ltr" /></p>
                    <p class="form-row"><label for="rs-new-province">استان <span class="required">*</span></label><?php if ($states) : ?><select class="input-text" id="rs-new-province" data-rs-new="province"><option value="">انتخاب استان</option><?php foreach ($states as $code => $name) : ?><option value="<?php echo esc_attr((string) $code); ?>"><?php echo esc_html((string) $name); ?></option><?php endforeach; ?></select><?php else : ?><input class="input-text" id="rs-new-province" data-rs-new="province" /><small>برای کشور فروشگاه فهرست استان در ووکامرس تعریف نشده است.</small><?php endif; ?></p>
                    <input type="hidden" data-rs-new="country" value="<?php echo esc_attr($country); ?>" />
                    <p class="form-row"><label for="rs-new-city">شهر <span class="required">*</span></label><input class="input-text" id="rs-new-city" data-rs-new="city" /></p>
                    <p class="form-row"><label for="rs-new-postal">کد پستی</label><input class="input-text" id="rs-new-postal" data-rs-new="postal" inputmode="numeric" dir="ltr" /></p>
                    <p class="form-row form-row-wide"><label for="rs-new-address-text">نشانی کامل <span class="required">*</span></label><textarea id="rs-new-address-text" data-rs-new="address" rows="3"></textarea></p>
                </div>
                <p class="co-inline-error" data-rs-new-error role="alert" hidden></p>
                <div class="co-form-actions"><button class="btn btn-primary" type="button" data-rs-save-address>ثبت و انتخاب این آدرس</button><button class="btn btn-outline" type="button" data-rs-cancel-address>انصراف</button></div>
            </div>
        </div>
    <?php endif; ?>
    <?php do_action('woocommerce_before_checkout_billing_form', $checkout); ?>
    <div class="<?php echo is_user_logged_in() ? 'rs-order-contact' : 'rs-guest-address'; ?>" data-rs-customer-fields>
        <h3><?php echo is_user_logged_in() ? 'اطلاعات لازم سفارش' : 'اطلاعات و نشانی تحویل'; ?></h3>
        <div class="woocommerce-billing-fields__field-wrapper rs-fields-grid" data-rs-billing-field-home><?php foreach ($checkout->get_checkout_fields('billing') as $key => $field) { $value = $checkout->get_value($key); if ($key === 'billing_email' && !$value && is_user_logged_in()) $value = wp_get_current_user()->user_email; woocommerce_form_field($key, $field, $value); } ?></div>
    </div>
    <?php if (is_user_logged_in()) : ?><div class="rs-contact-completion" data-rs-contact-completion hidden><h3>تکمیل اطلاعات ضروری سفارش</h3><div class="rs-fields-grid" data-rs-contact-fields></div></div><?php endif; ?>
    <?php do_action('woocommerce_after_checkout_billing_form', $checkout); ?>
</div>
<?php if (!is_user_logged_in() && $checkout->is_registration_enabled()) : ?>
    <div class="woocommerce-account-fields co-address-form">
        <?php if (!$checkout->is_registration_required()) : ?><p class="form-row form-row-wide create-account"><label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox"><input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked(true === $checkout->get_value('createaccount') || true === apply_filters('woocommerce_create_account_default_checked', false)); ?> type="checkbox" name="createaccount" value="1" /> <span><?php esc_html_e('Create an account?', 'woocommerce'); ?></span></label></p><?php endif; ?>
        <?php do_action('woocommerce_before_checkout_registration_form', $checkout); ?>
        <?php if ($checkout->get_checkout_fields('account')) : ?><div class="create-account"><?php foreach ($checkout->get_checkout_fields('account') as $key => $field) woocommerce_form_field($key, $field, $checkout->get_value($key)); ?><div class="clear"></div></div><?php endif; ?>
        <?php do_action('woocommerce_after_checkout_registration_form', $checkout); ?>
    </div>
<?php endif; ?>
