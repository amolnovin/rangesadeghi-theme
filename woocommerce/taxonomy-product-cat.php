<?php
/**
 * The Template for displaying products in a product category.
 *
 * @package WooCommerce\Templates
 * @version 4.7.0
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

rs_render('woocommerce.archive-product', ['dataPage' => 'shop', 'activeNav' => 'shop']);
