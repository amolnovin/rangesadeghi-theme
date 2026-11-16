<?php
/** Run inside an installed WordPress+WooCommerce site: wp eval-file tests/woocommerce-integration.php */
declare(strict_types=1);

function rs_assert($condition, string $message): void {
    if (!$condition) { throw new RuntimeException($message); }
}
rs_assert(function_exists('WC'), 'WooCommerce is not active');
wc_load_cart();
update_option('woocommerce_cod_settings', ['enabled' => 'yes', 'title' => 'Cash on delivery']);
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
$accountView = rs_view('woocommerce.my-account', ['dataPage' => 'account']);
rs_assert(str_contains($accountView, 'account-profile-form') && str_contains($accountView, 'panel-orders'), 'Account dashboard did not render');
// Payment gateway availability depends on a genuine checkout page query.
$checkoutPage = wp_insert_post(['post_title' => 'Checkout', 'post_name' => 'checkout-test', 'post_status' => 'publish', 'post_type' => 'page']);
update_option('woocommerce_checkout_page_id', $checkoutPage);
global $wp_query;
$wp_query = new WP_Query(['page_id' => $checkoutPage]);
rs_assert(is_checkout(), 'Checkout query was not initialized');
$checkout = WC()->checkout();
ob_start();
wc_get_template('checkout/form-checkout.php', ['checkout' => $checkout]);
$html = (string) ob_get_clean();
foreach (['rs-native-checkout', 'data-rs-checkout-panel="address"', 'data-rs-checkout-panel="review"', 'billing_first_name', 'woocommerce-checkout-review-order', 'woocommerce-process-checkout-nonce', 'place_order'] as $part) {
    rs_assert(str_contains($html, $part), 'Missing checkout component: ' . $part);
}
rs_assert(str_contains($html, 'name="payment_method"'), 'WooCommerce payment gateways not rendered');
WC()->cart->empty_cart();
$product->delete(true);
echo "WooCommerce cart and native checkout integration passed.\n";
