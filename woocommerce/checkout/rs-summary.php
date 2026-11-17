<?php
/** Live checkout summary. Replaced together with WooCommerce checkout fragments. */
defined('ABSPATH') || exit;
$cart = WC()->cart;
if (!$cart) return;
?>
<div class="rs-checkout-summary-fragment">
    <h2>خلاصه سفارش <small>(<?php echo esc_html(rs_fa($cart->get_cart_contents_count())); ?> کالا)</small></h2>
    <details class="co-items" open>
        <summary><span>کالاهای سفارش</span><svg><use href="#i-chevron-down"/></svg></summary>
        <div class="co-items-list">
            <?php foreach ($cart->get_cart() as $key => $item) : ?>
                <?php
                $product = apply_filters('woocommerce_cart_item_product', $item['data'], $item, $key);
                if (!$product || !$product->exists() || $item['quantity'] <= 0 || !apply_filters('woocommerce_checkout_cart_item_visible', true, $item, $key)) continue;
                ?>
                <div class="co-item">
                    <span class="co-thumb"><?php echo wp_kses_post($product->get_image('woocommerce_thumbnail')); ?><em><?php echo esc_html(rs_fa($item['quantity'])); ?></em></span>
                    <div><b><?php echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $product->get_name(), $item, $key)); ?></b><small><?php echo wp_kses_post(WC()->cart->get_product_subtotal($product, $item['quantity'])); ?></small><?php echo wp_kses_post(wc_get_formatted_cart_item_data($item)); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </details>
    <div class="summary-row"><span>قیمت کالاها</span><b><?php wc_cart_totals_subtotal_html(); ?></b></div>
    <?php foreach ($cart->get_coupons() as $code => $coupon) : ?>
        <div class="summary-row is-discount"><span><?php wc_cart_totals_coupon_label($coupon); ?></span><b><?php wc_cart_totals_coupon_html($coupon); ?></b></div>
    <?php endforeach; ?>
    <?php if ($cart->needs_shipping()) : ?>
        <div class="summary-row"><span>هزینه ارسال</span><b><?php echo wp_kses_post(WC()->customer && WC()->customer->has_calculated_shipping() ? $cart->get_cart_shipping_total() : 'پس از انتخاب آدرس'); ?></b></div>
    <?php endif; ?>
    <?php foreach ($cart->get_fees() as $fee) : ?>
        <div class="summary-row"><span><?php echo esc_html($fee->name); ?></span><b><?php wc_cart_totals_fee_html($fee); ?></b></div>
    <?php endforeach; ?>
    <?php if (wc_tax_enabled() && !$cart->display_prices_including_tax()) : ?>
        <div class="summary-row"><span><?php echo esc_html(WC()->countries->tax_or_vat()); ?></span><b><?php wc_cart_totals_taxes_total_html(); ?></b></div>
    <?php endif; ?>
    <div class="summary-total"><span>مبلغ قابل پرداخت</span><b class="rs-checkout-total"><?php echo wp_kses_post($cart->get_total()); ?></b></div>
</div>
