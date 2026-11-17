<?php
/** Custom gateway card; retains WooCommerce gateway fields and selectors. @version 3.5.0 */
defined('ABSPATH') || exit;
?>
<li class="wc_payment_method payment_method_<?php echo esc_attr($gateway->id); ?> rs-payment-option">
    <input id="payment_method_<?php echo esc_attr($gateway->id); ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr($gateway->id); ?>" <?php checked($gateway->chosen, true); ?> data-order_button_text="<?php echo esc_attr($gateway->order_button_text); ?>" />
    <label class="rs-payment-label" for="payment_method_<?php echo esc_attr($gateway->id); ?>"><span class="co-radio" aria-hidden="true"></span><span class="co-option-icon"><svg><use href="#i-card"/></svg></span><span class="co-option-body"><b><?php echo wp_kses_post($gateway->get_title()); ?></b></span><span class="rs-gateway-icon"><?php echo wp_kses_post($gateway->get_icon()); ?></span></label>
    <?php if ($gateway->has_fields() || $gateway->get_description()) : ?><div class="payment_box payment_method_<?php echo esc_attr($gateway->id); ?>" <?php if (!$gateway->chosen) : ?>style="display:none;"<?php endif; ?>><?php $gateway->payment_fields(); ?></div><?php endif; ?>
</li>
