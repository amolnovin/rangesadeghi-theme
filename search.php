<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

if (($_GET['post_type'] ?? '') === 'product') {
    rs_render('woocommerce.archive-product', ['dataPage' => 'shop', 'activeNav' => 'shop']);
    return;
}

rs_render('blog.index', ['dataPage' => 'blog', 'activeNav' => 'blog']);
