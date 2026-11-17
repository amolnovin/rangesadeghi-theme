<?php
/** The actual WooCommerce terms field, placed with the final review. */
defined('ABSPATH') || exit;
$termsUrl = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('terms') : '';
if (!$termsUrl) $termsUrl = rs_page_url('contact');
$returnUrl = rs_page_url('contact');
do_action('woocommerce_checkout_before_terms_and_conditions');
?>
<div class="woocommerce-terms-and-conditions-wrapper">
    <?php do_action('woocommerce_checkout_terms_and_conditions'); ?>
    <label class="co-check co-terms" for="terms">
        <input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="terms" id="terms" value="1" <?php checked(apply_filters('woocommerce_terms_is_checked_default', isset($_POST['terms'])), true); ?> />
        <span class="co-check-box"><svg><use href="#i-check"/></svg></span>
        <span><a href="<?php echo esc_url($termsUrl); ?>" target="_blank" rel="noopener">قوانین و مقررات</a> و <a href="<?php echo esc_url($returnUrl); ?>" target="_blank" rel="noopener">شرایط بازگشت کالا</a> فروشگاه رنگ صادقی را خوانده‌ام و می‌پذیرم. <span class="required">*</span></span>
    </label>
    <input type="hidden" name="terms-field" value="1" />
</div>
<?php do_action('woocommerce_checkout_after_terms_and_conditions'); ?>
