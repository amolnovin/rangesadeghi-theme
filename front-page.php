<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

if (!empty($_GET['demo_product'])) {
    rs_render('woocommerce.single-product', ['dataPage' => 'product', 'activeNav' => 'shop']);
    return;
}

rs_render('front-page', ['activeNav' => 'home']);
