<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

if (rs_is_wc() && function_exists('is_product') && is_product()) {
    rs_render('woocommerce.single-product', ['dataPage' => 'product', 'activeNav' => 'shop']);
    return;
}

rs_render('blog.single', ['dataPage' => 'article', 'activeNav' => 'blog']);
