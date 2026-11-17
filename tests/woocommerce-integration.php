<?php
/** Run inside an installed WordPress+WooCommerce site: wp eval-file tests/woocommerce-integration.php */
declare(strict_types=1);

function rs_assert($condition, string $message): void {
    if (!$condition) { throw new RuntimeException($message); }
}
rs_assert(function_exists('WC'), 'WooCommerce is not active');
wc_load_cart();
update_option('woocommerce_bacs_settings', ['enabled' => 'yes', 'title' => 'Bank transfer']);
$gateways = WC()->payment_gateways()->payment_gateways();
rs_assert(isset($gateways['bacs']), 'Bank transfer gateway is unavailable');
$gateways['bacs']->enabled = 'yes';
$product = new WC_Product_Simple();
$product->set_name('Integration test product');
$product->set_regular_price('100000');
$product->set_status('publish');
$product->set_manage_stock(true);
$product->set_stock_quantity(5);
$product->save();
$key = WC()->cart->add_to_cart($product->get_id(), 2);
rs_assert((bool) $key, 'Could not add product to WooCommerce cart');
WC()->cart->calculate_totals();
$payload = rs_app()->make(App\Providers\WooCommerceServiceProvider::class)->buildCartPayload();
rs_assert($payload['count'] === 2 && count($payload['items']) === 1, 'Cart payload does not match WooCommerce cart');
rs_assert($payload['items'][0]['key'] === $key, 'Cart item key does not match');
$cartView = rs_view('woocommerce.cart', ['dataPage' => 'cart']);
rs_assert(str_contains($cartView, 'cart-page-items') && str_contains($cartView, 'Integration test product'), 'Cart view did not render the real cart');
$user = get_user_by('login', 'admin');
rs_assert((bool) $user, 'Test user not found');
wp_set_current_user($user->ID);

// Reproduce the production failure: a variation in both cart and order history.
// Parent attributes are WC_Product_Attribute objects; variation attributes are strings.
$attribute = new WC_Product_Attribute();
$attribute->set_name('Finish');
$attribute->set_options(['Matte', 'Gloss']);
$attribute->set_visible(true);
$attribute->set_variation(true);
$variable = new WC_Product_Variable();
$variable->set_name('Variable integration product');
$variable->set_status('publish');
$variable->set_attributes([$attribute]);
$variable->save();
$variation = new WC_Product_Variation();
$variation->set_parent_id($variable->get_id());
$variation->set_attributes(['finish' => 'Matte']);
$variation->set_regular_price('50000');
$variation->set_status('publish');
$variation->save();
$variation = wc_get_product($variation->get_id());
rs_assert($variation instanceof WC_Product_Variation, 'Variation did not persist');
$variationAttributes = $variation->get_attributes();
rs_assert(is_string(current($variationAttributes)), 'Expected string variation attribute');
$variationModel = App\Support\ProductPresenter::fromWcProduct($variation);
rs_assert(in_array(['Finish', 'Matte'], $variationModel['specs'], true), 'Selected variation attribute missing from product presenter');
register_taxonomy('pa_rs_test_tone', 'product');
$term = wp_insert_term('Scarlet', 'pa_rs_test_tone', ['slug' => 'red']);
rs_assert(!is_wp_error($term), 'Could not create attribute term');
$taxonomyVariation = clone $variation;
$taxonomyVariation->set_attributes(['pa_rs_test_tone' => 'red']);
$taxonomyModel = App\Support\ProductPresenter::fromWcProduct($taxonomyVariation);
rs_assert(in_array('Scarlet', array_column($taxonomyModel['specs'], 1), true), 'Variation taxonomy slug was not resolved to the term name');
$variableModel = App\Support\ProductPresenter::fromWcProduct(wc_get_product($variable->get_id()));
rs_assert(in_array(['Finish', 'Matte، Gloss'], $variableModel['specs'], true), 'Visible parent product attributes regressed');
$variationKey = WC()->cart->add_to_cart($variable->get_id(), 1, $variation->get_id(), ['attribute_finish' => 'Matte']);
rs_assert((bool) $variationKey, 'Could not add variation to cart');
WC()->cart->calculate_totals();
$variationPayload = rs_app()->make(App\Providers\WooCommerceServiceProvider::class)->buildCartPayload();
rs_assert($variationPayload['count'] === 3 && count($variationPayload['items']) === 2, 'Variation cart payload did not render');
$order = wc_create_order(['customer_id' => $user->ID]);
$order->add_product($variation, 1);
$order->calculate_totals();
$order->save();
$cartWithHistory = rs_view('woocommerce.cart', ['dataPage' => 'cart']);
rs_assert(str_contains($cartWithHistory, 'Variable integration product'), 'Cart containing variation and order history did not render');
$accountView = rs_view('woocommerce.my-account', ['dataPage' => 'account']);
rs_assert(str_contains($accountView, 'account-profile-form') && str_contains($accountView, 'panel-orders'), 'Account dashboard did not render');
rs_assert(str_contains($accountView, 'Variable integration product'), 'Account order containing variation did not render');
$_GET['order_id'] = (string) $order->get_id();
$orderView = rs_view('woocommerce.order-details', ['dataPage' => 'order']);
rs_assert(str_contains($orderView, 'Variable integration product'), 'Order details containing variation did not render');
unset($_GET['order_id']);
// Payment gateway availability depends on a genuine checkout page query.
$checkoutPage = wp_insert_post(['post_title' => 'Checkout', 'post_name' => 'checkout-test', 'post_status' => 'publish', 'post_type' => 'page']);
update_option('woocommerce_checkout_page_id', $checkoutPage);
global $wp_query;
$wp_query = new WP_Query(['page_id' => $checkoutPage]);
rs_assert(is_checkout(), 'Checkout query was not initialized');
$checkoutView = rs_view('woocommerce.checkout-native', ['dataPage' => 'checkout']);
rs_assert(str_contains($checkoutView, 'rs-native-checkout'), 'Native checkout with variation did not render');
$checkout = WC()->checkout();
ob_start();
wc_get_template('checkout/form-checkout.php', ['checkout' => $checkout]);
$html = (string) ob_get_clean();
foreach (['rs-native-checkout', 'data-rs-checkout-panel="address"', 'data-rs-checkout-panel="review"', 'billing_first_name', 'woocommerce-checkout-review-order', 'woocommerce-process-checkout-nonce', 'place_order'] as $part) {
    rs_assert(str_contains($html, $part), 'Missing checkout component: ' . $part);
}
rs_assert(str_contains($html, 'name="payment_method"'), 'WooCommerce payment gateways not rendered: ' . implode(',', array_keys(WC()->payment_gateways()->get_available_payment_gateways())) . '; review: ' . substr(wp_strip_all_tags($html), -500));
WC()->cart->empty_cart();
$order->delete(true);
$variation->delete(true);
$variable->delete(true);
$product->delete(true);
echo "WooCommerce cart and native checkout integration passed.\n";
