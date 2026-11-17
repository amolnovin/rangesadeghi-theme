<?php
/** Checkout progress shared by the checkout and order-received pages. */
defined('ABSPATH') || exit;
$steps = [
    'cart' => ['سبد خرید', 'بررسی کالاها', 'i-bag'],
    'address' => ['آدرس تحویل', 'گیرنده و نشانی', 'i-pin'],
    'shipping' => ['شیوه ارسال', 'روش و زمان تحویل', 'i-truck'],
    'payment' => ['پرداخت', 'روش پرداخت و تخفیف', 'i-card'],
    'done' => ['تکمیل سفارش', 'ثبت نهایی', 'i-check'],
];
$step = isset($step, $steps[$step]) ? $step : 'address';
$current = array_search($step, array_keys($steps), true);
?>
<ol class="co-steps rs-checkout-steps<?php echo $step === 'done' ? ' is-complete' : ''; ?>" style="--p:<?php echo esc_attr((string) ($current / 4)); ?>" aria-label="مراحل خرید">
    <?php foreach ($steps as $key => $item) : ?>
        <?php $index = array_search($key, array_keys($steps), true); ?>
        <li class="<?php echo $index < $current ? 'is-done' : ($index === $current ? 'is-active' : ''); ?>" data-rs-indicator="<?php echo esc_attr($key); ?>" <?php echo $index === $current ? 'aria-current="step"' : ''; ?>>
            <?php if ($key === 'cart') : ?>
                <a class="co-step-btn" href="<?php echo esc_url(wc_get_cart_url()); ?>"><i><svg><use href="#<?php echo esc_attr($item[2]); ?>"/></svg></i><span><b><?php echo esc_html($item[0]); ?></b><small><?php echo esc_html($item[1]); ?></small></span></a>
            <?php else : ?>
                <span class="co-step-btn"><i><svg><use href="#<?php echo esc_attr($item[2]); ?>"/></svg></i><span><b><?php echo esc_html($item[0]); ?></b><small><?php echo esc_html($item[1]); ?></small></span></span>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ol>
