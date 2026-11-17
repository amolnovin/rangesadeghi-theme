<?php
/** Order received page. WooCommerce retains ownership of order and gateway hooks.
 * @version 8.1.0
 * @var WC_Order|false $order
 */
defined('ABSPATH') || exit;
?>
<div class="woocommerce-order rs-order-result">
    <?php if ($order) : ?>
        <?php do_action('woocommerce_before_thankyou', $order->get_id()); ?>
        <?php if ($order->has_status('failed')) : ?>
            <?php wc_get_template('checkout/rs-progress.php', ['step' => 'payment']); ?>
            <section class="co-card co-success">
                <h1>پرداخت سفارش ناموفق بود</h1>
                <p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php esc_html_e('Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce'); ?></p>
                <div class="co-success-actions woocommerce-thankyou-order-failed-actions">
                    <a class="btn btn-primary button pay" href="<?php echo esc_url($order->get_checkout_payment_url()); ?>"><?php esc_html_e('Pay', 'woocommerce'); ?></a>
                    <?php if (is_user_logged_in()) : ?><a class="btn btn-outline button" href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>"><?php esc_html_e('My account', 'woocommerce'); ?></a><?php endif; ?>
                </div>
            </section>
        <?php else : ?>
            <?php wc_get_template('checkout/rs-progress.php', ['step' => ($order->needs_payment() || $order->has_status(['cancelled', 'refunded'])) ? 'payment' : 'done']); ?>
            <section class="co-card co-success">
                <?php if ($order->is_paid()) : ?><div class="co-success-icon" aria-hidden="true"><svg viewBox="0 0 56 56"><circle cx="28" cy="28" r="25"/><path d="M16 28l8 8 16-17"/></svg></div><?php endif; ?>
                <h1><?php echo $order->is_paid() ? 'سفارش شما ثبت شد' : 'جزئیات سفارش'; ?></h1>
                <?php wc_get_template('checkout/order-received.php', ['order' => $order]); ?>
                <p>وضعیت سفارش: <?php echo esc_html(wc_get_order_status_name($order->get_status())); ?></p>
                <ul class="co-success-grid woocommerce-order-overview woocommerce-thankyou-order-details order_details">
                    <li class="woocommerce-order-overview__order order"><span><?php esc_html_e('Order number:', 'woocommerce'); ?></span><strong><?php echo esc_html($order->get_order_number()); ?></strong></li>
                    <li class="woocommerce-order-overview__date date"><span><?php esc_html_e('Date:', 'woocommerce'); ?></span><strong><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></strong></li>
                    <?php if (is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email()) : ?>
                        <li class="woocommerce-order-overview__email email"><span><?php esc_html_e('Email:', 'woocommerce'); ?></span><strong><?php echo esc_html($order->get_billing_email()); ?></strong></li>
                    <?php endif; ?>
                    <li class="woocommerce-order-overview__total total"><span><?php esc_html_e('Total:', 'woocommerce'); ?></span><strong><?php echo wp_kses_post($order->get_formatted_order_total()); ?></strong></li>
                    <?php if ($order->get_payment_method_title()) : ?><li class="woocommerce-order-overview__payment-method method"><span><?php esc_html_e('Payment method:', 'woocommerce'); ?></span><strong><?php echo wp_kses_post($order->get_payment_method_title()); ?></strong></li><?php endif; ?>
                </ul>
                <div class="co-success-actions"><?php if ($order->needs_payment()) : ?><a class="btn btn-primary" href="<?php echo esc_url($order->get_checkout_payment_url()); ?>">پرداخت سفارش</a><?php endif; ?><a class="btn btn-outline" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">ادامه خرید</a><?php if (is_user_logged_in() && $order->get_user_id() === get_current_user_id()) : ?><a class="btn btn-primary" href="<?php echo esc_url($order->get_view_order_url()); ?>">مشاهده سفارش</a><?php endif; ?></div>
            </section>
        <?php endif; ?>
        <?php do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id()); ?>
        <?php do_action('woocommerce_thankyou', $order->get_id()); ?>
    <?php else : ?>
        <section class="co-card co-success"><?php wc_get_template('checkout/order-received.php', ['order' => false]); ?></section>
    <?php endif; ?>
</div>
