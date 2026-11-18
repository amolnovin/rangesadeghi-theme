<?php
/** Order received page using real WooCommerce order data and gateway hooks.
 * @version 8.1.0
 * @var WC_Order|false $order
 */
defined('ABSPATH') || exit;
?>
<div class="woocommerce-order rs-order-result">
    <?php if ($order) : ?>
        <?php
        $failed = $order->has_status('failed');
        $paid = $order->is_paid();
        $cardAwaiting = $order->get_payment_method() === 'rs_card_transfer' && $order->has_status('on-hold');
        $completedStep = $paid || $cardAwaiting;
        $orderNumber = (string) $order->get_order_number();
        // Remove only this theme's historical prefix; leave third-party order numbers intact.
        $displayNumber = preg_replace('/^RS\s*-\s*/i', '', $orderNumber) ?? $orderNumber;
        $deliveryDay = (string) $order->get_meta('_rs_delivery_day');
        $deliverySlot = (string) $order->get_meta('_rs_delivery_slot');
        $deliveryTime = implode(' — ', array_filter([$deliveryDay, $deliverySlot]));
        $address = $order->get_formatted_shipping_address();
        $canView = is_user_logged_in() && ((int) $order->get_user_id() === get_current_user_id() || current_user_can('manage_woocommerce'));
        ?>
        <?php wc_get_template('checkout/rs-progress.php', ['step' => $completedStep ? 'done' : 'payment']); ?>
        <section class="co-card co-success" aria-labelledby="rs-order-result-title">
            <?php do_action('woocommerce_before_thankyou', $order->get_id()); ?>
            <?php if ($failed) : ?>
                <h1 id="rs-order-result-title">پرداخت سفارش ناموفق بود</h1>
                <p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php esc_html_e('Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce'); ?></p>
                <div class="co-success-actions woocommerce-thankyou-order-failed-actions">
                    <a class="btn btn-primary button pay" href="<?php echo esc_url($order->get_checkout_payment_url()); ?>"><?php esc_html_e('Pay', 'woocommerce'); ?></a>
                    <?php if (is_user_logged_in()) : ?><a class="btn btn-outline button" href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>"><?php esc_html_e('My account', 'woocommerce'); ?></a><?php endif; ?>
                </div>
            <?php else : ?>
                <?php if ($completedStep) : ?><div class="co-success-icon" aria-hidden="true"><svg viewBox="0 0 56 56"><circle cx="28" cy="28" r="25"/><path d="M16 28l8 8 16-17"/></svg></div><?php endif; ?>
                <h1 id="rs-order-result-title"><?php echo $completedStep ? 'سفارش شما با موفقیت ثبت شد' : 'جزئیات سفارش'; ?></h1>
                <?php wc_get_template('checkout/order-received.php', ['order' => $order]); ?>
                <?php if ($cardAwaiting) : ?><p class="rs-payment-status">پرداخت کارت‌به‌کارت در انتظار واریز و تأیید فروشگاه است.</p><?php elseif (!$paid && $order->needs_payment()) : ?><p class="rs-payment-status">پرداخت این سفارش هنوز تکمیل نشده است.</p><?php endif; ?>
                <p class="rs-order-status">وضعیت سفارش: <?php echo esc_html(wc_get_order_status_name($order->get_status())); ?></p>
                <div class="co-order-id"><span>کد سفارش</span><b><bdi><?php echo esc_html(rs_fa($displayNumber)); ?></bdi></b><button type="button" data-copy="<?php echo esc_attr($displayNumber); ?>" aria-label="کپی کد سفارش"><svg aria-hidden="true"><use href="#i-link"/></svg>کپی</button></div>
                <?php // Gateway output can contain payment instructions or interactive controls. ?>
                <?php do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id()); ?>
                <dl class="co-success-grid woocommerce-order-overview woocommerce-thankyou-order-details order_details">
                    <div class="woocommerce-order-overview__total total"><dt><?php echo $paid ? 'مبلغ پرداخت‌شده' : 'مبلغ سفارش'; ?></dt><dd><?php echo wp_kses_post($order->get_formatted_order_total()); ?></dd></div>
                    <?php if ($order->get_payment_method_title()) : ?><div class="woocommerce-order-overview__payment-method method"><dt>روش پرداخت</dt><dd><?php echo wp_kses_post($order->get_payment_method_title()); ?></dd></div><?php endif; ?>
                    <?php if ($order->get_shipping_method()) : ?><div><dt>شیوه ارسال</dt><dd><?php echo esc_html($order->get_shipping_method()); ?></dd></div><?php endif; ?>
                    <?php if ($order->get_shipping_method()) : ?><div><dt>زمان تحویل</dt><dd><?php echo $deliveryTime !== '' ? esc_html(rs_fa($deliveryTime)) : 'تعیین نشده'; ?></dd></div><?php endif; ?>
                    <?php if ($address !== '') : ?><div class="wide"><dt>آدرس تحویل</dt><dd><?php echo wp_kses_post($address); ?></dd></div><?php endif; ?>
                    <?php if ($order->get_date_created()) : ?><div><dt>تاریخ سفارش</dt><dd><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></dd></div><?php endif; ?>
                    <?php if (is_user_logged_in() && (int) $order->get_user_id() === get_current_user_id() && $order->get_billing_email()) : ?><div><dt>ایمیل</dt><dd><bdi><?php echo esc_html($order->get_billing_email()); ?></bdi></dd></div><?php endif; ?>
                </dl>
                <div class="co-success-thumbs" aria-label="کالاهای سفارش">
                    <?php foreach ($order->get_items() as $item) : $product = $item->get_product(); ?>
                        <span class="co-thumb" title="<?php echo esc_attr($item->get_name()); ?>"><?php echo $product ? wp_kses_post($product->get_image('woocommerce_thumbnail')) : wp_kses_post(wc_placeholder_img('woocommerce_thumbnail')); ?><?php if ($item->get_quantity() > 1) : ?><em><?php echo esc_html(rs_fa($item->get_quantity())); ?></em><?php endif; ?></span>
                    <?php endforeach; ?>
                </div>
                <?php if ($completedStep) : ?><ol class="co-timeline" aria-label="مراحل سفارش">
                    <li class="is-done"><i><svg><use href="#i-check"/></svg></i><span>ثبت سفارش</span></li>
                    <li class="<?php echo $paid ? 'is-done' : ''; ?>"><i><svg><use href="#i-card"/></svg></i><span>تأیید پرداخت</span></li>
                    <li><i><svg><use href="#i-box"/></svg></i><span>آماده‌سازی</span></li>
                    <li><i><svg><use href="#i-truck"/></svg></i><span>ارسال</span></li>
                    <li><i><svg><use href="#i-home"/></svg></i><span>تحویل</span></li>
                </ol><?php endif; ?>
                <div class="co-success-actions">
                    <?php if ($order->needs_payment()) : ?><a class="btn btn-primary" href="<?php echo esc_url($order->get_checkout_payment_url()); ?>"><svg aria-hidden="true"><use href="#i-card"/></svg>پرداخت سفارش</a><?php endif; ?>
                    <?php if ($canView) : ?><a class="btn btn-primary" href="<?php echo esc_url($order->get_view_order_url()); ?>"><svg aria-hidden="true"><use href="#i-box"/></svg>مشاهده سفارش</a><?php endif; ?>
                    <a class="btn btn-outline" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"><svg aria-hidden="true"><use href="#i-bag"/></svg>ادامه خرید</a>
                </div>
            <?php endif; ?>
            <?php if ($failed) do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id()); ?>
            <?php do_action('woocommerce_thankyou', $order->get_id()); ?>
        </section>
    <?php else : ?>
        <section class="co-card co-success"><?php wc_get_template('checkout/order-received.php', ['order' => false]); ?></section>
    <?php endif; ?>
</div>
