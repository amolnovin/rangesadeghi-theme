<?php
/**
 * The Template for displaying all single products.
 *
 * @package WooCommerce\Templates
 * @version 1.6.4
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

rs_render('woocommerce.single-product', ['dataPage' => 'product', 'activeNav' => 'shop']);
