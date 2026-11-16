<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

if (rs_is_wc()) {
    if (is_cart()) {
        rs_render('woocommerce.cart', ['dataPage' => 'cart', 'activeNav' => 'cart']);
        return;
    }

    if (is_checkout()) {
        // Preserve WooCommerce's shipping, payment and order-received hooks.
        rs_render('woocommerce.checkout-native', ['dataPage' => 'checkout', 'activeNav' => 'cart']);
        return;
    }

    if (is_account_page()) {
        if (is_wc_endpoint_url('view-order')) {
            rs_render('woocommerce.order-details', ['dataPage' => 'order', 'activeNav' => 'account']);
        } else {
            rs_render('woocommerce.my-account', ['dataPage' => 'account', 'activeNav' => 'account']);
        }
        return;
    }
}

// تشخیص خودکار برگه‌های اختصاصی بر اساس نامک (slug) حتی اگر قالب برگه دستی انتخاب نشده باشد
if (is_page('categories')) {
    rs_render('pages.categories', ['dataPage' => 'categories', 'activeNav' => 'categories']);
    return;
}
if (is_page('about')) {
    rs_render('pages.about', ['dataPage' => 'about', 'activeNav' => 'about']);
    return;
}
if (is_page('contact')) {
    rs_render('pages.contact', ['dataPage' => 'contact', 'activeNav' => 'contact']);
    return;
}
if (is_page('stores')) {
    rs_render('pages.stores', ['dataPage' => 'stores', 'activeNav' => 'stores']);
    return;
}
if (is_page('order-details')) {
    rs_render('woocommerce.order-details', ['dataPage' => 'order', 'activeNav' => 'account']);
    return;
}

rs_render('pages.page');
