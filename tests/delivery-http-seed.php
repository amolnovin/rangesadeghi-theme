<?php
/** Create an isolated guest fixture for HTTP tests inside the disposable WP install. */
$product = new WC_Product_Simple();
$product->set_name('Delivery feedback HTTP fixture'); $product->set_regular_price('500'); $product->set_status('publish'); $product->save();
$order = wc_create_order(); $order->add_product($product, 1); $order->set_billing_phone('09123456789'); $order->set_billing_email('fixture@example.test'); $order->calculate_totals(); $order->save();
$options = rs_app()->make('options');
$consentCategory = wp_insert_term('HTTP consent category', 'product_cat');
if (is_wp_error($consentCategory)) throw new RuntimeException('Cannot create HTTP consent category');
wp_set_object_terms($product->get_id(), [(int) $consentCategory['term_id']], 'product_cat');
$options->update(['shop' => ['tracking_shipping' => 'wc-processing'], 'delivery_feedback' => ['enabled' => false], 'product' => ['consent_category_ids' => (string) $consentCategory['term_id'], 'consent_text' => 'HTTP consent']]);
$order->update_status('processing');
$service = rs_app()->make(App\Providers\DeliveryFeedbackServiceProvider::class);
$service->link($order);
echo 'RSHTTP:' . json_encode(['id' => $order->get_id(), 'token' => $order->get_meta('_rs_feedback_token'), 'product' => $product->get_id()]) . "\n";
