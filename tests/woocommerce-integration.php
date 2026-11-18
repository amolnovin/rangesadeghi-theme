<?php
/** Run inside an installed WordPress+WooCommerce site: wp eval-file tests/woocommerce-integration.php */
declare(strict_types=1);

function rs_assert($condition, string $message): void {
    if (!$condition) { throw new RuntimeException($message); }
}
rs_assert(function_exists('WC'), 'WooCommerce is not active');
wc_load_cart();
update_option('woocommerce_default_country', 'US:CA');
update_option('woocommerce_ship_to_countries', '');
$zone = WC_Shipping_Zones::get_zone(0);
rs_assert((bool) $zone->add_shipping_method('flat_rate'), 'Could not configure a real WooCommerce shipping method');
rs_assert(wc_get_shipping_method_count(true) > 0, 'WooCommerce shipping method not enabled');
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
rs_assert(str_contains($accountView, 'کیف پول در حال حاضر در دسترس نیست.'), 'Unavailable wallet must not display fictional data');
// Exercise the plugin's account endpoint contract without redistributing the licensed plugin.
class Amol_Wallet_Frontend { public static function account_endpoint_content() { echo '<div id="test-wallet-endpoint">Wallet endpoint</div>'; } }
class Amol_Wallet_Points { public static function is_enabled() { return true; } public static function get_points($userId) { return 17; } }
add_action('woocommerce_account_wallet_endpoint', ['Amol_Wallet_Frontend', 'account_endpoint_content']);
$walletView = rs_view('woocommerce.my-account', ['dataPage' => 'account']);
rs_assert(str_contains($walletView, 'test-wallet-endpoint') && str_contains($walletView, '۱۷'), 'Wallet endpoint or real points were not rendered');
remove_action('woocommerce_account_wallet_endpoint', ['Amol_Wallet_Frontend', 'account_endpoint_content']);

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
$savedAddress = ['id' => 'integration-address', 'title' => 'Work', 'name' => 'Real Customer', 'phone' => '1234567890', 'city' => 'Amol', 'province' => 'Mazandaran', 'postal' => '12345', 'address' => 'Test Street'];
update_user_meta($user->ID, '_rs_saved_addresses', [$savedAddress]);
$checkout = WC()->checkout();
ob_start();
wc_get_template('checkout/form-checkout.php', ['checkout' => $checkout]);
$html = (string) ob_get_clean();
foreach (['rs-native-checkout', 'data-rs-checkout-panel="address"', 'data-rs-checkout-panel="review"', 'billing_first_name', 'woocommerce-checkout-review-order', 'woocommerce-process-checkout-nonce', 'place_order', 'rs-checkout-summary-fragment', 'Variable integration product', 'data-rs-saved-list', 'Test Street', 'rs-payment-option', 'data-rs-coupon-card', 'name="rs_delivery_day"', 'rs_official_invoice', 'rs_gift_wrap', 'order_comments', 'rs-receiver-self', 'rs-receiver-name', 'rs-receiver-phone', 'data-rs-new-address', 'name="rs_saved_address"', '#addresses'] as $part) {
    rs_assert(str_contains($html, $part), 'Missing checkout component: ' . $part);
}
rs_assert(strpos($html, 'data-rs-coupon-card') < strpos($html, 'data-rs-final-review'), 'Final review is not after coupon');
rs_assert(str_contains($html, 'name="terms"') && str_contains($html, 'name="terms-field"') && str_contains($html, 'data-rs-place-order'), 'Policy acceptance or nav submit missing');
rs_assert(substr_count($html, 'id="terms"') === 1 && substr_count($html, 'id="place_order"') === 1, 'Duplicate checkout fields');
rs_assert((bool) preg_match('/<select[^>]*id="rs-new-province"[^>]*>/', $html), 'New address state must be a WooCommerce select');
rs_assert(str_contains($html, 'value="CA"') && str_contains($html, 'California'), 'WooCommerce state choices missing');
// Guests still need real editable checkout fields because they have no saved account addresses.
wp_set_current_user(0);
ob_start(); wc_get_template('checkout/form-billing.php', ['checkout' => $checkout]); $guestHtml = (string) ob_get_clean();
rs_assert(str_contains($guestHtml, 'rs-guest-address') && str_contains($guestHtml, 'billing_state') && !str_contains($guestHtml, 'class="rs-order-contact"'), 'Guest checkout lost editable address fields');
wp_set_current_user($user->ID);
rs_assert(str_contains($html, 'name="payment_method"'), 'WooCommerce payment gateways not rendered: ' . implode(',', array_keys(WC()->payment_gateways()->get_available_payment_gateways())) . '; review: ' . substr(wp_strip_all_tags($html), -500));
WC()->customer->set_shipping_country('US');
WC()->customer->set_shipping_state('CA');
WC()->customer->set_shipping_city('Los Angeles');
WC()->customer->set_shipping_postcode('90001');
WC()->customer->set_shipping_address_1('Test street');
WC()->cart->calculate_shipping();
$packages = WC()->shipping()->get_packages();
rs_assert(!empty($packages[0]['rates']), 'Shipping zone did not calculate any real rate');
WC()->session->set('chosen_shipping_methods', [array_key_first($packages[0]['rates'])]);
$fragments = apply_filters('woocommerce_update_order_review_fragments', ['.existing' => 'preserved']);
rs_assert(isset($fragments['.existing'], $fragments['.rs-checkout-summary-fragment']), 'Checkout fragments were not preserved or updated');
rs_assert(str_contains($fragments['.rs-checkout-summary-fragment'], 'Integration test product'), 'Updated summary does not contain cart products');
rs_assert(!str_contains($fragments['.rs-checkout-summary-fragment'], 'پس از انتخاب روش ارسال'), 'Chosen shipping rate missing from summary');
// Policy consent is required even when a client omits WooCommerce's terms-field marker.
$policyProvider = rs_app()->make(App\Providers\WooCommerceServiceProvider::class);
$policyErrors = new WP_Error(); $policyProvider->validatePolicyAcceptance(['terms' => 0], $policyErrors);
rs_assert((bool) $policyErrors->get_error_messages('terms'), 'Policy acceptance was not enforced server-side');
$policyErrors = new WP_Error(); $policyProvider->validatePolicyAcceptance(['terms' => 1], $policyErrors);
rs_assert(!$policyErrors->has_errors(), 'Accepted policies rejected');
// Card transfer is a real, opt-in WooCommerce gateway and never uses sample card data.
$card = new App\WooCommerce\Payments\CardTransfer();
rs_assert(!$card->is_available(), 'Card transfer must be disabled without merchant configuration');
update_option('woocommerce_rs_card_transfer_settings', ['enabled' => 'yes', 'title' => 'Card transfer', 'card_number' => '1234567812345678', 'card_holder' => 'Test Merchant', 'instructions' => 'Contact us']);
$card = new App\WooCommerce\Payments\CardTransfer();
rs_assert($card->is_available(), 'Configured card transfer gateway unavailable');
$gatewayClasses = apply_filters('woocommerce_payment_gateways', []);
rs_assert(in_array(App\WooCommerce\Payments\CardTransfer::class, $gatewayClasses, true), 'Card transfer not registered with WooCommerce');
rs_assert(isset(WC()->payment_gateways()->get_available_payment_gateways()['bacs']), 'Existing enabled payment gateway disappeared');
$freshGateways = new WC_Payment_Gateways();
$activeGateways = $freshGateways->get_available_payment_gateways();
rs_assert(isset($activeGateways['bacs'], $activeGateways['rs_card_transfer']), 'Enabled gateways not all returned by WooCommerce');
ob_start(); wc_get_template('checkout/payment.php', ['available_gateways' => $activeGateways, 'order_button_text' => 'Place order']); $paymentHtml = (string) ob_get_clean();
rs_assert(str_contains($paymentHtml, 'value="bacs"') && str_contains($paymentHtml, 'value="rs_card_transfer"'), 'Enabled gateways were not both rendered');
// Store checkout addresses in the same account list without rewriting billing profile fields.
$provider = rs_app()->make(App\Providers\WooCommerceServiceProvider::class);
$billingBefore = get_user_meta($user->ID, 'billing_first_name', true);
$newAddress = ['title' => 'Studio', 'name' => 'Recipient Person', 'phone' => '۰۹۱۲۳۴۵۶۷۸۹', 'country' => 'US', 'province' => 'CA', 'city' => 'Los Angeles', 'postal' => '۹۰۰۰۱', 'address' => '123 Real Street'];
$invalidAddress = $provider->saveCheckoutAddress($user->ID, array_merge($newAddress, ['phone' => 'bad']));
rs_assert(is_wp_error($invalidAddress), 'Invalid address telephone was accepted');
$rsNew = $provider->saveCheckoutAddress($user->ID, $newAddress);
// State codes supplied by the address form must choose the corresponding shipping zone.
$stateZone = new WC_Shipping_Zone();
$stateZone->set_zone_name('California only'); $stateZone->set_zone_order(1); $stateZone->add_location('US:CA', 'state'); $stateZone->save();
rs_assert((bool) $stateZone->add_shipping_method('flat_rate'), 'Could not create state zone rate');
$californiaPackage = $packages[0]; $californiaPackage['destination']['country'] = 'US'; $californiaPackage['destination']['state'] = $rsNew['province'];
$newYorkPackage = $californiaPackage; $newYorkPackage['destination']['state'] = 'NY';
rs_assert(WC_Shipping_Zones::get_zone_matching_package($californiaPackage)->get_id() === $stateZone->get_id(), 'Selected state did not match its shipping zone');
rs_assert(WC_Shipping_Zones::get_zone_matching_package($newYorkPackage)->get_id() !== $stateZone->get_id(), 'Shipping zone ignored the selected state');
rs_assert(is_array($rsNew) && $rsNew['province'] === 'CA' && $rsNew['province_label'] === 'California' && $rsNew['country'] === 'US', 'WooCommerce state code or label not stored');
rs_assert(is_wp_error($provider->saveCheckoutAddress($user->ID, array_merge($newAddress, ['province' => 'invalid-state']))), 'Invalid state was accepted');
rs_assert($provider->resolveCheckoutState('US', 'California') === 'CA', 'Legacy state label not resolved');
rs_assert(is_array($rsNew) && $rsNew['postal'] === '90001' && $rsNew['phone'] === '09123456789', 'Address save or Persian digit normalization failed');
rs_assert(get_user_meta($user->ID, 'billing_first_name', true) === $billingBefore, 'Saving checkout address unexpectedly changed billing profile');
$addresses = get_user_meta($user->ID, '_rs_saved_addresses', true);
rs_assert(count($addresses) === 2 && $addresses[1]['id'] === $rsNew['id'], 'Checkout address not stored in account list');
$accountAfter = rs_view('woocommerce.my-account', ['dataPage' => 'account']);
rs_assert(str_contains($accountAfter, '123 Real Street'), 'Checkout address not visible in account page');
// A first custom address must not discard the previously visible WooCommerce default.
$originalAddresses = get_user_meta($user->ID, '_rs_saved_addresses', true);
update_user_meta($user->ID, '_rs_saved_addresses', []);
update_user_meta($user->ID, 'billing_address_1', 'Old Default Street');
$firstCustom = $provider->saveCheckoutAddress($user->ID, array_merge($newAddress, ['postal' => '']));
$withDefault = get_user_meta($user->ID, '_rs_saved_addresses', true);
rs_assert(is_array($firstCustom) && !$firstCustom['isDefault'] && count($withDefault) === 2 && $withDefault[0]['address'] === 'Old Default Street', 'First new address replaced the default');
rs_assert(get_user_meta($user->ID, 'billing_address_1', true) === 'Old Default Street', 'First address save modified billing profile');
update_user_meta($user->ID, '_rs_saved_addresses', $originalAddresses);
$unauthorized = $provider->saveCheckoutAddress(0, $newAddress);
rs_assert(is_wp_error($unauthorized), 'Anonymous address storage accepted');
for ($i = 0; $i < 3; $i++) rs_assert(is_array($provider->saveCheckoutAddress($user->ID, $newAddress)), 'Could not populate account address list');
rs_assert(is_wp_error($provider->saveCheckoutAddress($user->ID, $newAddress)), 'Address limit silently deleted older addresses');

// Validate and save the alternate recipient in an actual WooCommerce checkout order.
wc_clear_notices();
$_POST['rs_recipient_present'] = '1'; $_POST['rs_receiver_name'] = ''; $_POST['rs_receiver_phone'] = 'bad';
$provider->validateCheckoutRecipient();
rs_assert(wc_notice_count('error') > 0, 'Invalid alternate recipient was accepted');
wc_clear_notices();
$_POST = ['woocommerce-process-checkout-nonce' => wp_create_nonce('woocommerce-process_checkout'),
    'billing_first_name' => 'Buyer', 'billing_last_name' => 'Name', 'billing_email' => 'buyer@example.test', 'billing_phone' => '1234567890',
    'billing_country' => 'US', 'billing_state' => 'CA', 'billing_city' => 'Los Angeles', 'billing_postcode' => '90001', 'billing_address_1' => '123 Real Street',
    'payment_method' => 'bacs', 'shipping_method' => [array_key_first($packages[0]['rates'])],
    'rs_recipient_present' => '1', 'rs_receiver_name' => 'Other Recipient', 'rs_receiver_phone' => '۰۹۱۲۳۴۵۶۷۸۹'];
$provider->validateCheckoutRecipient();
rs_assert(wc_notice_count('error') === 0, 'Valid alternate recipient was rejected');
$createdId = $checkout->create_order($checkout->get_posted_data());
rs_assert(!is_wp_error($createdId) && $createdId > 0, 'Could not create WooCommerce checkout order');
$createdOrder = wc_get_order($createdId);
rs_assert($createdOrder->get_billing_first_name() === 'Buyer' && $createdOrder->get_billing_last_name() === 'Name', 'Alternate recipient overwrote billing identity');
rs_assert($createdOrder->get_shipping_address_1() === '123 Real Street' && $createdOrder->get_shipping_city() === 'Los Angeles', 'Selected destination not saved to WooCommerce shipping address');
rs_assert($createdOrder->get_shipping_first_name() === 'Other Recipient' && $createdOrder->get_shipping_last_name() === '' && $createdOrder->get_shipping_phone() === '09123456789', 'Alternate receiver missing from WooCommerce shipping details');
rs_assert($createdOrder->get_meta('_rs_receiver_name') === 'Other Recipient', 'Alternate recipient metadata missing');
$_POST['rs_receiver_self'] = '1';
$createdOrder->set_shipping_first_name('Buyer');
$provider->saveCustomOrderMeta($createdOrder, []);
rs_assert($createdOrder->get_shipping_first_name() === 'Buyer' && $createdOrder->get_meta('_rs_receiver_name') === '', 'Self recipient did not clear stale alternate details');
$createdOrder->delete(true);
// Empty postcodes become the exact placeholder in validated order data, including countries with strict postcode formats.
$_POST['billing_postcode'] = '';
$posted = $checkout->get_posted_data();
rs_assert($posted['billing_postcode'] === '0000000000' && $posted['shipping_postcode'] === '0000000000', 'Missing postcode did not receive placeholder');
rs_assert(wc_format_postcode('0000000000', 'US') === '0000000000' && WC_Validation::is_postcode('0000000000', 'US'), 'Placeholder failed strict country postcode validation');
$errors = new WP_Error();
$validate = new ReflectionMethod(WC_Checkout::class, 'validate_posted_data');
$validate->invokeArgs($checkout, [&$posted, &$errors]);
rs_assert(!$errors->get_error_messages('billing_postcode_validation'), 'WooCommerce rejected the placeholder during checkout validation');
$placeholderId = $checkout->create_order($posted);
$placeholderOrder = wc_get_order($placeholderId);
rs_assert($placeholderOrder && $placeholderOrder->get_billing_postcode() === '0000000000' && $placeholderOrder->get_shipping_postcode() === '0000000000', 'Placeholder did not persist on order');
$placeholderOrder->delete(true);
$_POST = [];
// Optional gift fee uses the posted checkbox value, including an explicit unchecked value.
$provider = rs_app()->make(App\Providers\WooCommerceServiceProvider::class);
WC()->session->set('rs_gift_wrap', false);
$_POST['post_data'] = 'rs_gift_wrap=0&rs_gift_wrap=1';
WC()->cart->calculate_totals();
rs_assert(count(WC()->cart->get_fees()) === 1, 'Gift option did not add the configured WooCommerce fee');
$_POST['post_data'] = 'rs_gift_wrap=0';
WC()->cart->calculate_totals();
rs_assert(count(WC()->cart->get_fees()) === 0 && !WC()->session->get('rs_gift_wrap'), 'Unchecked gift option left a stale fee');
unset($_POST['post_data']);
// Theme methods calculate real rates alongside zone rates, with city restriction.
rs_app('options')->update(['shop' => ['courier_enabled' => true, 'courier_cities' => 'Los Angeles', 'courier_cost' => 120, 'pickup_enabled' => true, 'pickup_cost' => 30, 'delivery_slots_enabled' => true, 'delivery_slots' => 'Morning, Evening', 'delivery_days_ahead' => 6, 'shipping_eta_map' => "flat_rate:42=Three days"]]);
WC_Cache_Helper::get_transient_version('shipping', true);
WC()->session->__unset('shipping_for_package_0');
WC()->cart->calculate_shipping();
$packages = WC()->shipping()->get_packages();
rs_assert(isset($packages[0]['rates']['rs_store_courier'], $packages[0]['rates']['rs_store_pickup']), 'Theme rates not calculated');
rs_assert((float) $packages[0]['rates']['rs_store_courier']->get_cost() === 120.0, 'Courier rate cost not applied');
$courier = new App\WooCommerce\Shipping\StoreCourier();
$elsewhere = $packages[0]; $elsewhere['destination']['city'] = 'Elsewhere';
rs_assert(!$courier->is_available($elsewhere), 'Courier leaked to another city');
rs_assert(empty($checkout->get_checkout_fields('billing')['billing_postcode']['required']), 'Postcode must be optional');
// Mandatory validated day and slot only for theme delivery methods.
$_POST['shipping_method'] = ['rs_store_pickup'];
wc_clear_notices(); $provider->validateDeliveryPreference();
rs_assert(wc_notice_count('error') > 0, 'Missing scheduled time accepted');
wc_clear_notices(); $_POST['rs_delivery_day'] = '2020-01-01'; $_POST['rs_delivery_slot'] = 'Morning'; $provider->validateDeliveryPreference();
rs_assert(wc_notice_count('error') > 0, 'Past scheduled day accepted');
// Friday is rejected for pickup, but remains available for courier.
rs_app('options')->update(['shop' => ['delivery_days_ahead' => 14]]);
$testToday = new DateTimeImmutable('today', wp_timezone());
$friday = $testToday->modify('+' . ((5 - (int) $testToday->format('N') + 7) % 7) . ' days');
$_POST['rs_delivery_day'] = $friday->format('Y-m-d'); $_POST['rs_delivery_slot'] = 'Morning';
wc_clear_notices(); $provider->validateDeliveryPreference(); rs_assert(wc_notice_count('error') > 0, 'Friday pickup accepted');
$_POST['shipping_method'] = ['rs_store_courier']; wc_clear_notices(); $provider->validateDeliveryPreference(); rs_assert(wc_notice_count('error') === 0, 'Friday courier should remain available');
$_POST['shipping_method'] = ['rs_store_pickup']; rs_app('options')->update(['shop' => ['delivery_days_ahead' => 6]]);
$validDay = $testToday->modify('+2 days'); if ($validDay->format('N') === '5') $validDay = $validDay->modify('+1 day');
wc_clear_notices(); $_POST['rs_delivery_day'] = $validDay->format('Y-m-d'); $_POST['rs_delivery_slot'] = 'Morning'; $_POST['rs_official_invoice'] = '1';
$provider->validateDeliveryPreference();
rs_assert(wc_notice_count('error') === 0, 'Valid scheduled time rejected');
$scheduledPost = $_POST;
$_POST += ['woocommerce-process-checkout-nonce' => wp_create_nonce('woocommerce-process_checkout'), 'billing_first_name' => 'Buyer', 'billing_last_name' => 'Name', 'billing_email' => 'buyer@example.test', 'billing_phone' => '1234567890', 'billing_country' => 'US', 'billing_state' => 'CA', 'billing_city' => 'Los Angeles', 'billing_address_1' => '123 Real Street', 'payment_method' => 'bacs'];
WC()->session->set('chosen_shipping_methods', ['rs_store_pickup']);
$scheduledId = $checkout->create_order($checkout->get_posted_data());
rs_assert(!is_wp_error($scheduledId) && $scheduledId > 0, 'Could not create scheduled order');
$scheduledOrder = wc_get_order($scheduledId);
rs_assert($scheduledOrder->get_meta('_rs_delivery_slot') === 'Morning' && $scheduledOrder->get_meta('_rs_delivery_day') === $scheduledPost['rs_delivery_day'], 'Scheduled time did not persist');
rs_assert(str_contains($scheduledOrder->get_shipping_method(), 'تحویل حضوری'), 'Selected pickup method missing from order');
$scheduledOrder->delete(true);
$_POST = $scheduledPost;
$provider->saveCustomOrderMeta($order, []);
rs_assert($order->get_meta('_rs_delivery_day') === $_POST['rs_delivery_day'] && $order->get_meta('_rs_delivery_slot') === 'Morning', 'Order preference metadata not saved');
$_POST['shipping_method'] = [array_key_first(array_filter($packages[0]['rates'], static fn($r) => $r->get_method_id() === 'flat_rate'))];
$provider->saveCustomOrderMeta($order, []);
rs_assert($order->get_meta('_rs_delivery_day') === '', 'Stale time saved for another method');
$_POST = [];
// Render genuine WooCommerce shipping rates and their extension hook.
$hookCalled = false;
add_action('woocommerce_after_shipping_rate', static function () use (&$hookCalled) { $hookCalled = true; }, 10, 2);
$rate = new WC_Shipping_Rate('flat_rate:42', 'Integration shipping', 45000, [], 'flat_rate');
ob_start();
wc_get_template('cart/cart-shipping.php', ['package' => ['destination' => ['country' => 'US']], 'package_name' => 'Shipping', 'available_methods' => [$rate->id => $rate], 'show_package_details' => false, 'show_shipping_calculator' => false, 'package_details' => '', 'index' => 0, 'chosen_method' => $rate->id, 'formatted_destination' => 'Address', 'has_calculated_shipping' => true]);
$shippingHtml = (string) ob_get_clean();
rs_assert(str_contains($shippingHtml, 'Integration shipping') && str_contains($shippingHtml, 'shipping_method[0]') && $hookCalled, 'Real shipping method or its hook missing');
$called = [];
add_action('woocommerce_before_thankyou', static function () use (&$called) { $called[] = 'before'; });
add_action('woocommerce_thankyou', static function () use (&$called) { $called[] = 'thankyou'; });
$order->set_status('pending');
$order->save();
ob_start();
wc_get_template('checkout/thankyou.php', ['order' => $order]);
$received = (string) ob_get_clean();
rs_assert(str_contains($received, 'rs-order-result') && str_contains($received, 'woocommerce-thankyou-order-details'), 'Order received details missing');
rs_assert(in_array('before', $called, true) && in_array('thankyou', $called, true), 'Thankyou hooks not invoked');
rs_assert(!str_contains($received, 'is-complete') && str_contains($received, 'پرداخت این سفارش هنوز تکمیل نشده است'), 'Pending payment shown as completed');
// The custom result card uses real order data and retains extension output within the card.
$order->set_status('processing');
$order->set_payment_method_title('Test gateway');
$order->set_shipping_first_name('Real'); $order->set_shipping_last_name('Customer');
$order->set_shipping_country('US'); $order->set_shipping_state('CA');
$order->set_shipping_city('Los Angeles'); $order->set_shipping_address_1('123 Real Street');
$order->update_meta_data('_rs_delivery_day', '2030-02-01'); $order->update_meta_data('_rs_delivery_slot', 'Morning');
$shipItem = new WC_Order_Item_Shipping(); $shipItem->set_method_id('flat_rate'); $shipItem->set_method_title('Real carrier'); $shipItem->set_total(30); $order->add_item($shipItem); $order->save();
$prefixOrderNumber = static fn($number) => 'RS-' . $number;
add_filter('woocommerce_order_number', $prefixOrderNumber);
add_action('woocommerce_thankyou_test_gateway', static function () { echo '<p id="test-gateway-content">Gateway message</p>'; });
$order->set_payment_method('test_gateway'); $order->save();
ob_start(); wc_get_template('checkout/thankyou.php', ['order' => $order]); $completed = (string) ob_get_clean();
remove_filter('woocommerce_order_number', $prefixOrderNumber);
rs_assert(str_contains($completed, 'data-copy="' . $order->get_id() . '"') && !str_contains($completed, 'data-copy="RS-'), 'Order number prefix not removed from display and copy');
rs_assert(str_contains($completed, 'is-complete') && str_contains($completed, 'Real carrier') && str_contains($completed, 'Morning'), 'Real order shipping details missing from result');
rs_assert(str_contains($completed, 'i-box') && str_contains($completed, 'i-bag') && str_contains($completed, esc_url(rs_page_url('order-tracking', ['order_id' => $order->get_id()]))), 'Result action links or icons missing');
rs_assert(str_contains($completed, 'co-success-thumbs') && str_contains($completed, 'co-timeline'), 'Reference order items or timeline missing');
$gridDom = new DOMDocument(); @$gridDom->loadHTML('<?xml encoding="utf-8" ?>' . $completed); $gridXpath = new DOMXPath($gridDom);
$grid = $gridXpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' co-success-grid ')]")->item(0);
rs_assert($grid && !str_contains($grid->textContent, 'آدرس تحویل') && !str_contains($grid->textContent, 'تاریخ سفارش') && !str_contains($grid->textContent, 'ایمیل'), 'Removed order result fields still shown');
$dom = new DOMDocument(); @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $completed); $xpath = new DOMXPath($dom);
rs_assert($xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' co-success ')]//*[@id='test-gateway-content']")->length === 1, 'Gateway content is outside the result card');
rs_assert($xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' co-success ')]//*[contains(concat(' ', normalize-space(@class), ' '), ' woocommerce-order-details ')]")->length === 1, 'Default order hook output must remain inside the card');
// Account summary and tracking use WooCommerce status settings, not a fabricated prefix.
$_GET['order_id'] = (string) $order->get_id();
$prefixed = static fn($number) => 'RS-' . $number;
add_filter('woocommerce_order_number', $prefixed);
$orderPage = rs_view('woocommerce.order-details', ['dataPage' => 'order']);
remove_filter('woocommerce_order_number', $prefixed);
rs_assert(!str_contains($orderPage, 'RS-RS-') && !str_contains($orderPage, 'جزئیات سفارش <b id="od-code">RS-') && str_contains($orderPage, 'rs-print-invoice'), 'Order details number or dedicated invoice incorrect');
rs_assert(!str_contains($orderPage, 'data-cancel-order="' . $order->get_id() . '"'), 'Processing order must not be cancellable');
$options = rs_app()->make('options');
$options->update(['shop' => ['tracking_shipping' => 'wc-processing']]);
$orderPage = rs_view('woocommerce.order-details', ['dataPage' => 'order']);
rs_assert(str_contains($orderPage, '--rs-progress:75%'), 'Configured shipping status not reflected in tracking');
$order->set_status('on-hold'); $order->save();
$orderPage = rs_view('woocommerce.order-details', ['dataPage' => 'order']);
rs_assert(str_contains($orderPage, '--rs-progress:0%') && str_contains($orderPage, 'data-cancel-order="' . $order->get_id() . '"'), 'On-hold order tracking or cancellation incorrect');
$order->set_status('processing'); $order->save();
unset($_GET['order_id']);
// Custom sequential order identifiers without the theme prefix must remain untouched for guests.
$customOrderNumber = static fn($number) => 'INV-123';
add_filter('woocommerce_order_number', $customOrderNumber);
wp_set_current_user(0);
ob_start(); wc_get_template('checkout/thankyou.php', ['order' => $order]); $guestResult = (string) ob_get_clean();
wp_set_current_user($user->ID);
remove_filter('woocommerce_order_number', $customOrderNumber);
rs_assert(str_contains($guestResult, 'data-copy="INV-123"') && str_contains($guestResult, 'i-bag'), 'Custom order number or shop link missing for guest');
rs_assert(!str_contains($guestResult, esc_url(rs_page_url('order-tracking', ['order_id' => $order->get_id()]))) && !str_contains($guestResult, 'ایمیل'), 'Guest must not receive an inaccessible account link or billing email');


$order->set_status('failed');
$order->save();
ob_start();
wc_get_template('checkout/thankyou.php', ['order' => $order]);
$failed = (string) ob_get_clean();
rs_assert(str_contains($failed, 'woocommerce-thankyou-order-failed') && !str_contains($failed, 'is-complete'), 'Failed order shown as complete');
ob_start();
wc_get_template('checkout/thankyou.php', ['order' => false]);
$noOrder = (string) ob_get_clean();
rs_assert(!str_contains($noOrder, 'is-complete'), 'Missing order shown as complete');
// Offline card payment must never mark an order paid automatically.
$cardOrder = wc_create_order(['customer_id' => $user->ID]);
$cardOrder->add_product($product, 1); $cardOrder->set_payment_method($card); $cardOrder->calculate_totals(); $cardOrder->save();
$result = $card->process_payment($cardOrder->get_id());
$cardOrder = wc_get_order($cardOrder->get_id());
rs_assert($result['result'] === 'success' && $cardOrder->has_status('on-hold') && !$cardOrder->is_paid(), 'Card transfer incorrectly marked payment complete');
ob_start(); wc_get_template('checkout/thankyou.php', ['order' => $cardOrder]); $cardResult = (string) ob_get_clean();
rs_assert(str_contains($cardResult, 'is-complete') && str_contains($cardResult, 'در انتظار واریز و تأیید فروشگاه') && !str_contains($cardResult, 'مبلغ پرداخت‌شده'), 'Card transfer confirmation must not claim payment');
rs_assert(str_contains($cardResult, '1234567812345678'), 'Card instructions not shown in order result');

ob_start(); $card->thankyouPage($cardOrder->get_id()); $cardText = (string) ob_get_clean();
rs_assert(str_contains($cardText, '1234567812345678') && str_contains($cardText, 'Test Merchant'), 'Configured transfer details missing on thank-you page');
$cardOrder->delete(true);
WC()->cart->empty_cart();
$order->delete(true);
$variation->delete(true);
$variable->delete(true);
$product->delete(true);
echo "WooCommerce cart and native checkout integration passed.\n";
