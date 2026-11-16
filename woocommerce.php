<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

if (is_singular('product')) {
    rs_render('woocommerce.single-product', ['dataPage' => 'product', 'activeNav' => 'shop']);
    return;
}

rs_render('woocommerce.archive-product', ['dataPage' => 'shop', 'activeNav' => 'shop']);
