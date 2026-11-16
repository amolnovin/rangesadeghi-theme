<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

if (rs_is_wc() && (is_shop() || is_product_taxonomy() || get_post_type() === 'product')) {
    rs_render('woocommerce.archive-product', ['dataPage' => 'shop', 'activeNav' => 'shop']);
    return;
}

rs_render('blog.index', ['dataPage' => 'blog', 'activeNav' => 'blog']);
