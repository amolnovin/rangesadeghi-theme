<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

if (!empty($_GET['demo_product'])) {
    rs_render('woocommerce.single-product', ['dataPage' => 'product', 'activeNav' => 'shop']);
    return;
}

if (is_front_page()) {
    rs_render('front-page', ['activeNav' => 'home']);
    return;
}

if (is_singular('post')) {
    rs_render('blog.single', ['dataPage' => 'article', 'activeNav' => 'blog']);
    return;
}

if (is_singular('page')) {
    require __DIR__ . '/page.php';
    return;
}

rs_render('blog.index', ['dataPage' => 'blog', 'activeNav' => 'blog']);
