<?php
/** Check WooCommerce's hide-out-of-stock setting applies to both query entry points. */
use App\Support\ProductSearch;
$original = get_option('woocommerce_hide_out_of_stock_items');
update_option('woocommerce_hide_out_of_stock_items', 'yes');
$query = ProductSearch::query(['post_type' => 'product', 'post_status' => 'publish'], 'اکریلیک 120');
if ($query->found_posts !== 0) throw new RuntimeException('Out-of-stock product leaked into search when hidden');
update_option('woocommerce_hide_out_of_stock_items', 'no');
$query = ProductSearch::query(['post_type' => 'product', 'post_status' => 'publish'], 'اکریلیک 120');
if ($query->found_posts !== 1) throw new RuntimeException('Out-of-stock product missing when visibility enabled');
update_option('woocommerce_hide_out_of_stock_items', $original);
echo "Search out-of-stock visibility passed.\n";
