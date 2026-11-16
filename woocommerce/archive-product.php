<?php
/**
 * The Template for displaying product archives, including the main shop page.
 *
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

rs_render('woocommerce.archive-product', ['dataPage' => 'shop', 'activeNav' => 'shop']);
