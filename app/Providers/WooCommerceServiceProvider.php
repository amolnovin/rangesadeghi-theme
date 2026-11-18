<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\ServiceProvider;
use App\Support\DemoCatalog;
use App\Support\Persian;
use App\Support\ProductPresenter;
use WC_Order;
use WP_Query;

final class WooCommerceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // اعلام سازگاری رسمی با HPOS و بلوک‌های ووکامرس
        add_action('before_woocommerce_init', [$this, 'declareCompatibility']);

        // تعداد محصولات در صفحه آرشیو فروشگاه
        add_filter('loop_shop_per_page', static fn(): int => max(3, (int) rs_option('shop.per_page', 9)), 20);
        add_filter('woocommerce_Query_vars', static fn($v) => $v);

        // اعمال فیلترهای URL روی کوئری اصلی فروشگاه ووکامرس
        add_action('pre_get_posts', [$this, 'applyShopQueryFilters']);

        // کارمزد بسته‌بندی هدیه در تسویه حساب ووکامرس
        add_action('woocommerce_cart_calculate_fees', [$this, 'applyGiftWrapFee']);
        add_filter('woocommerce_add_to_cart_validation', [$this, 'validateProductConsent'], 10, 3);
        add_filter('woocommerce_add_cart_item_data', [$this, 'storeProductConsent'], 10, 2);
        add_action('woocommerce_check_cart_items', [$this, 'validateCartConsents']);
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'saveProductConsentToOrder'], 10, 4);

        // ذخیره متای اختصاصی تسویه حساب روی سفارش ووکامرس (سازگار با HPOS)
        add_action('woocommerce_checkout_create_order', [$this, 'saveCustomOrderMeta'], 10, 2);
        // Validate after WooCommerce updates the destination, rates and chosen methods.
        add_action('woocommerce_after_checkout_validation', [$this, 'validateDeliveryPreference']);
        add_action('woocommerce_checkout_process', [$this, 'validateCheckoutRecipient']);
        add_filter('woocommerce_checkout_fields', [$this, 'checkoutOptionalPostcode'], 999);
        add_filter('woocommerce_checkout_posted_data', [$this, 'checkoutPostcodeFallback'], 20);
        add_filter('woocommerce_validate_postcode', [$this, 'allowCheckoutPostcodeFallback'], 10, 3);
        add_filter('woocommerce_format_postcode', [$this, 'preserveCheckoutPostcodeFallback'], 10, 2);
        add_filter('woocommerce_shipping_methods', [$this, 'registerThemeShippingMethods']);
        add_filter('woocommerce_payment_gateways', [$this, 'registerCardTransferGateway']);
        add_action('woocommerce_after_checkout_validation', [$this, 'validatePolicyAcceptance'], 10, 2);
        add_action('woocommerce_after_shipping_rate', [$this, 'shippingRateDetails'], 10, 2);

        // Keep the sidebar in sync with WooCommerce's checkout AJAX updates.
        add_filter('woocommerce_update_order_review_fragments', [$this, 'checkoutSummaryFragment']);

        // Shipping rates and free-shipping eligibility are controlled by WooCommerce shipping zones.

        // اندپوینت‌های AJAX اختصاصی و فوق‌سبک فروشگاه، سبد خرید، علاقه‌مندی و حساب کاربری
        $publicAjax = [
            'rs_cart_add' => [$this, 'ajaxCartAdd'],
            'rs_cart_update' => [$this, 'ajaxCartUpdate'],
            'rs_cart_coupon' => [$this, 'ajaxCartCoupon'],
            'rs_cart_state' => [$this, 'ajaxCartState'],
            'rs_wishlist_toggle' => [$this, 'ajaxWishlistToggle'],
            'rs_wishlist_items' => [$this, 'ajaxWishlistItems'],
            'rs_shop_filter' => [$this, 'ajaxShopFilter'],
            'rs_checkout_gift' => [$this, 'ajaxCheckoutGift'],
        ];

        foreach ($publicAjax as $action => $handler) {
            add_action("wp_ajax_{$action}", $handler);
            add_action("wp_ajax_nopriv_{$action}", $handler);
        }

        $authAjax = [
            'rs_address_save' => [$this, 'ajaxAddressSave'],
            'rs_checkout_address_save' => [$this, 'ajaxCheckoutAddressSave'],
            'rs_address_delete' => [$this, 'ajaxAddressDelete'],
            'rs_profile_save' => [$this, 'ajaxProfileSave'],
            'rs_order_cancel' => [$this, 'ajaxOrderCancel'],
            'rs_order_reorder' => [$this, 'ajaxOrderReorder'],
        ];

        foreach ($authAjax as $action => $handler) {
            add_action("wp_ajax_{$action}", $handler);
        }
    }

    public function checkoutOptionalPostcode(array $fields): array
    {
        foreach (['billing', 'shipping'] as $section) {
            if (isset($fields[$section][$section . '_postcode'])) $fields[$section][$section . '_postcode']['required'] = false;
        }
        return $fields;
    }

    /** Use a known placeholder rather than an empty postcode, including for guest checkouts. */
    public function checkoutPostcodeFallback(array $data): array
    {
        foreach (['billing_postcode', 'shipping_postcode'] as $key) {
            if (isset($data[$key]) && trim((string) $data[$key]) === '') $data[$key] = '0000000000';
        }
        return $data;
    }

    public function allowCheckoutPostcodeFallback(bool $valid, string $postcode, string $country): bool
    {
        if ($postcode === '0000000000' && ((function_exists('is_checkout') && is_checkout()) || defined('WOOCOMMERCE_CHECKOUT'))) return true;
        return $valid;
    }

    public function preserveCheckoutPostcodeFallback(string $postcode, string $country): string
    {
        if (preg_replace('/[\s-]/', '', $postcode) === '0000000000' && ((function_exists('is_checkout') && is_checkout()) || defined('WOOCOMMERCE_CHECKOUT'))) return '0000000000';
        return $postcode;
    }

    /** Resolve both WooCommerce state codes and legacy saved state names. */
    public function resolveCheckoutState(string $country, string $state): string
    {
        $states = WC()->countries->get_states($country);
        if (!is_array($states) || !$states) return $state;
        if (isset($states[$state])) return $state;
        foreach ($states as $code => $label) {
            if (Persian::normalize((string) $label) === Persian::normalize($state)) return (string) $code;
        }
        return '';
    }

    public function registerCardTransferGateway(array $gateways): array
    {
        $gateways[] = \App\WooCommerce\Payments\CardTransfer::class;
        return $gateways;
    }

    public function validatePolicyAcceptance(array $data, \WP_Error $errors): void
    {
        if (empty($data['woocommerce_checkout_update_totals']) && empty($data['terms']) && !$errors->get_error_messages('terms')) {
            $errors->add('terms', 'برای ثبت سفارش، قوانین و شرایط بازگشت کالا را بپذیرید.', ['id' => 'terms']);
        }
    }

    public function registerThemeShippingMethods(array $methods): array
    {
        $methods['rs_store_courier'] = \App\WooCommerce\Shipping\StoreCourier::class;
        $methods['rs_store_pickup'] = \App\WooCommerce\Shipping\StorePickup::class;
        return $methods;
    }

    public function shippingRateDetails($rate, $index): void
    {
        $id = (string) $rate->get_id();
        $kind = (string) $rate->get_method_id();
        $map = (string) rs_option('shop.shipping_eta_map', '');
        $eta = '';
        foreach (preg_split('/\r\n|\r|\n/', $map) ?: [] as $line) {
            $parts = explode('=', $line, 2);
            if (count($parts) === 2 && trim($parts[0]) === $id) { $eta = trim($parts[1]); break; }
            if (count($parts) === 2 && trim($parts[0]) === $kind) $eta = trim($parts[1]);
        }
        if ($eta === '') $eta = trim((string) rs_option('shop.shipping_eta_default', ''));
        echo '<span class="rs-rate-info" data-rs-rate-kind="' . esc_attr($kind) . '" data-rs-rate-eta="' . esc_attr($eta) . '" hidden></span>';
    }

    public function checkoutSummaryFragment(array $fragments): array
    {
        ob_start();
        wc_get_template('checkout/rs-summary.php');
        $fragments['.rs-checkout-summary-fragment'] = (string) ob_get_clean();
        return $fragments;
    }

    public function declareCompatibility(): void
    {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', $this->app->basePath('functions.php'), true);
        }
    }

    public function applyShopQueryFilters(WP_Query $query): void
    {
        if (is_admin() || !$query->is_main_query() || !rs_is_wc()) {
            return;
        }

        if (!is_shop() && !is_product_taxonomy() && !($query->is_search() && $query->get('post_type') === 'product')) {
            return;
        }

        $taxQuery = (array) $query->get('tax_query');
        $metaQuery = (array) $query->get('meta_query');

        // دسته‌بندی از پارامتر cat یا product_cat
        $cats = $this->parseCsvParam('cat');
        if ($cats !== []) {
            $taxQuery[] = [
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $cats,
            ];
        }

        // فیلتر برند
        $brands = $this->parseCsvParam('brand');
        if ($brands !== []) {
            $brandTax = taxonomy_exists('product_brand') ? 'product_brand' : (taxonomy_exists('pa_brand') ? 'pa_brand' : '');
            if ($brandTax !== '') {
                $taxQuery[] = [
                    'taxonomy' => $brandTax,
                    'field' => 'slug',
                    'terms' => $brands,
                ];
            } else {
                $metaQuery[] = [
                    'key' => '_rs_brand_slug',
                    'value' => $brands,
                    'compare' => 'IN',
                ];
            }
        }

        // محدوده قیمت به تومان
        $minPrice = (int) preg_replace('/\D+/', '', Persian::toEn((string) ($_GET['min_price'] ?? '')));
        $maxPrice = (int) preg_replace('/\D+/', '', Persian::toEn((string) ($_GET['max_price'] ?? '')));
        $multiplier = (function_exists('get_woocommerce_currency') && get_woocommerce_currency() === 'IRR') ? 10 : 1;

        if ($minPrice > 0) {
            $metaQuery[] = [
                'key' => '_price',
                'value' => $minPrice * $multiplier,
                'compare' => '>=',
                'type' => 'NUMERIC',
            ];
        }
        if ($maxPrice > 0) {
            $metaQuery[] = [
                'key' => '_price',
                'value' => $maxPrice * $multiplier,
                'compare' => '<=',
                'type' => 'NUMERIC',
            ];
        }

        // فقط تخفیف‌دار
        if (!empty($_GET['sale']) && $_GET['sale'] === '1' && function_exists('wc_get_product_ids_on_sale')) {
            $saleIds = wc_get_product_ids_on_sale();
            $query->set('post__in', !empty($saleIds) ? $saleIds : [0]);
        }

        // فقط موجود در انبار
        if (!empty($_GET['in_stock']) && $_GET['in_stock'] === '1') {
            $metaQuery[] = [
                'key' => '_stock_status',
                'value' => 'instock',
            ];
        }

        // مرتب‌سازی منطبق با گزینه‌های قالب HTML
        $sort = sanitize_key((string) ($_GET['rs_sort'] ?? rs_option('shop.default_sort', 'default')));
        switch ($sort) {
            case 'newest':
                $query->set('orderby', 'date');
                $query->set('order', 'DESC');
                break;
            case 'popular':
                $query->set('meta_key', '_wc_average_rating');
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'DESC');
                break;
            case 'bestseller':
                $query->set('meta_key', 'total_sales');
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'DESC');
                break;
            case 'cheap':
                $query->set('meta_key', '_price');
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'ASC');
                break;
            case 'expensive':
                $query->set('meta_key', '_price');
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'DESC');
                break;
            case 'name':
                $query->set('orderby', 'title');
                $query->set('order', 'ASC');
                break;
        }

        if ($taxQuery !== []) {
            $query->set('tax_query', $taxQuery);
        }
        if ($metaQuery !== []) {
            $query->set('meta_query', $metaQuery);
        }
    }

    public function applyGiftWrapFee(\WC_Cart $cart): void
    {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        if (!(bool) rs_option('shop.gift_wrap_enabled', true)) {
            return;
        }

        $enabled = false;
        if (WC()->session && WC()->session->get('rs_gift_wrap')) {
            $enabled = true;
        }
        if (isset($_POST['post_data'])) {
            parse_str((string) wp_unslash($_POST['post_data']), $parsed);
            $enabled = !empty($parsed['rs_gift_wrap']);
            if (WC()->session) WC()->session->set('rs_gift_wrap', $enabled);
        } elseif (isset($_POST['rs_gift_wrap'])) {
            $enabled = !empty($_POST['rs_gift_wrap']);
            if (WC()->session) WC()->session->set('rs_gift_wrap', $enabled);
        }

        if ($enabled) {
            $costToman = max(0, (int) rs_option('shop.gift_wrap_cost', 35000));
            $fee = (function_exists('get_woocommerce_currency') && get_woocommerce_currency() === 'IRR') ? $costToman * 10 : $costToman;
            if ($fee > 0) {
                $cart->add_fee('بسته‌بندی هدیه همراه کارت پیام', (float) $fee);
            }
        }
    }

    /** Selected, actually available theme shipping methods for all packages. */
    private function scheduledShippingMethods(): array
    {
        if (!WC()->cart || !WC()->cart->needs_shipping()) return [];
        $chosen = isset($_POST['shipping_method']) && is_array($_POST['shipping_method']) ? wc_clean(wp_unslash($_POST['shipping_method'])) : (WC()->session ? WC()->session->get('chosen_shipping_methods', []) : []);
        $methods = [];
        foreach (WC()->shipping()->get_packages() as $index => $package) {
            $rate = $package['rates'][$chosen[$index] ?? ''] ?? null;
            if ($rate && in_array($rate->get_method_id(), ['rs_store_courier', 'rs_store_pickup'], true)) $methods[] = $rate->get_method_id();
        }
        return $methods;
    }

    private function usesScheduledShipping(): bool
    {
        return $this->scheduledShippingMethods() !== [];
    }

    public function validateDeliveryPreference(): void
    {
        $methods = $this->scheduledShippingMethods();
        if (!$methods) return;
        $day = sanitize_text_field((string) wp_unslash($_POST['rs_delivery_day'] ?? ''));
        $slot = sanitize_text_field((string) wp_unslash($_POST['rs_delivery_slot'] ?? ''));
        $slots = array_filter(array_map('trim', explode(',', (string) rs_option('shop.delivery_slots', ''))));
        $today = new \DateTimeImmutable('today', wp_timezone());
        $days = max(1, min(30, (int) rs_option('shop.delivery_days_ahead', 6)));
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $day, wp_timezone());
        $valid = $date && $date->format('Y-m-d') === $day && $date >= $today && in_array($slot, $slots, true);
        if ($valid && in_array('rs_store_courier', $methods, true) && $date >= $today->modify('+' . $days . ' days')) $valid = false;
        if ($valid && in_array('rs_store_pickup', $methods, true)) {
            $cursor = $today; $openDays = 0;
            while ($openDays < $days) { if ($cursor->format('N') !== '5') $openDays++; $cursor = $cursor->modify('+1 day'); }
            if ($date->format('N') === '5' || $date >= $cursor) $valid = false;
        }
        if (!$valid) wc_add_notice('لطفاً روز و بازه معتبر تحویل را انتخاب کنید؛ تحویل حضوری جمعه‌ها انجام نمی‌شود.', 'error');
    }

    public function validateCheckoutRecipient(): void
    {
        if (empty($_POST['rs_recipient_present']) || !WC()->cart || !WC()->cart->needs_shipping() || !empty($_POST['rs_receiver_self'])) return;
        $name = sanitize_text_field((string) wp_unslash($_POST['rs_receiver_name'] ?? ''));
        $phone = Persian::toEn(sanitize_text_field((string) wp_unslash($_POST['rs_receiver_phone'] ?? '')));
        if ($name === '' || strlen(preg_replace('/\D+/', '', $phone)) < 7 || !\WC_Validation::is_phone($phone)) {
            wc_add_notice('نام و شماره تماس معتبر گیرنده را وارد کنید.', 'error');
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public function saveCustomOrderMeta(WC_Order $order, array $data): void
    {
        $day = sanitize_text_field((string) wp_unslash($_POST['rs_delivery_day'] ?? ''));
        $slot = sanitize_text_field((string) wp_unslash($_POST['rs_delivery_slot'] ?? ''));
        $gift = rs_option('shop.gift_wrap_enabled', true) && !empty($_POST['rs_gift_wrap']) ? 'yes' : 'no';
        $invoice = rs_option('shop.official_invoice_enabled', true) && !empty($_POST['rs_official_invoice']) ? 'yes' : 'no';
        $rcName = sanitize_text_field((string) wp_unslash($_POST['rs_receiver_name'] ?? ''));
        $rcPhone = Persian::toEn(sanitize_text_field((string) wp_unslash($_POST['rs_receiver_phone'] ?? '')));

        if ($this->usesScheduledShipping() && $day !== '') {
            $order->update_meta_data('_rs_delivery_day', $day);
        }
        if ($this->usesScheduledShipping() && $slot !== '') {
            $order->update_meta_data('_rs_delivery_slot', $slot);
        }
        if (!$this->usesScheduledShipping()) { $order->delete_meta_data('_rs_delivery_day'); $order->delete_meta_data('_rs_delivery_slot'); }
        $order->update_meta_data('_rs_gift_wrap', $gift);
        $order->update_meta_data('_rs_official_invoice', $invoice);

        // The recipient choice is independent of billing and of WooCommerce's shipping-address toggle.
        $recipientSelf = !empty($_POST['rs_recipient_present']) && !empty($_POST['rs_receiver_self']);
        if ($recipientSelf) {
            if (WC()->cart && WC()->cart->needs_shipping()) {
                $order->set_shipping_first_name($order->get_billing_first_name());
                $order->set_shipping_last_name($order->get_billing_last_name());
                if (is_callable([$order, 'set_shipping_phone'])) $order->set_shipping_phone($order->get_billing_phone());
            }
            $order->delete_meta_data('_rs_receiver_name');
            $order->delete_meta_data('_rs_receiver_phone');
        } else {
            if ($rcName !== '') {
                $order->update_meta_data('_rs_receiver_name', $rcName);
                $order->set_shipping_first_name($rcName);
                if (!empty($_POST['rs_recipient_present'])) $order->set_shipping_last_name('');
            }
            if ($rcPhone !== '') {
                $order->update_meta_data('_rs_receiver_phone', $rcPhone);
                if (!empty($_POST['rs_recipient_present']) && is_callable([$order, 'set_shipping_phone'])) $order->set_shipping_phone($rcPhone);
            }
        }
    }

    /**
     * @param array<string, mixed> $rates
     * @param array<string, mixed> $package
     * @return array<string, mixed>
     */
    public function filterShippingRatesForThreshold(array $rates, array $package): array
    {
        if (!rs_is_wc() || !WC()->cart) {
            return $rates;
        }

        $threshold = (int) rs_option('shop.free_shipping_threshold', 10000000);
        if ($threshold <= 0) {
            return $rates;
        }

        $subtotalToman = Persian::toToman(WC()->cart->get_subtotal());
        if ($subtotalToman >= $threshold) {
            foreach ($rates as $rateId => $rate) {
                if (is_object($rate) && method_exists($rate, 'get_method_id') && $rate->get_method_id() !== 'tipax') {
                    $rate->set_cost(0);
                }
            }
        }

        return $rates;
    }

    public function productConsentText(int $productId): string
    {
        $text = trim((string) rs_option('product.consent_text', ''));
        $raw = \App\Support\Persian::toEn((string) rs_option('product.consent_category_ids', ''));
        $ids = array_filter(array_map('absint', preg_split('/[\s,،]+/u', trim($raw))));
        if ($text === '' || !$ids || !function_exists('has_term')) return '';
        $product = wc_get_product($productId);
        if ($product && $product->is_type('variation')) $productId = $product->get_parent_id();
        foreach ($ids as $id) {
            $children = get_term_children($id, 'product_cat');
            $terms = array_merge([$id], is_array($children) ? $children : []);
            if (has_term($terms, 'product_cat', $productId)) return $text;
        }
        return '';
    }

    public function validateProductConsent($valid, $productId, $quantity): bool
    {
        if ($valid && $this->productConsentText((int) $productId) !== '' && ($_POST['rs_product_consent'] ?? '') !== 'yes') {
            wc_add_notice('برای خرید این محصول ابتدا متن تأیید را در صفحه محصول بپذیرید.', 'error');
            return false;
        }
        return (bool) $valid;
    }

    public function storeProductConsent(array $data, $productId): array
    {
        $text = $this->productConsentText((int) $productId);
        if ($text !== '' && ($_POST['rs_product_consent'] ?? '') === 'yes') $data['rs_product_consent'] = $text;
        return $data;
    }

    public function validateCartConsents(): void
    {
        if (!WC()->cart) return;
        foreach (WC()->cart->get_cart() as $item) {
            if ($this->productConsentText((int) ($item['product_id'] ?? 0)) !== '' && empty($item['rs_product_consent'])) {
                wc_add_notice('تأیید شرایط یکی از محصولات سبد خرید ثبت نشده است؛ آن را حذف و از صفحه محصول دوباره اضافه کنید.', 'error');
                return;
            }
        }
    }

    public function saveProductConsentToOrder($item, $cartItemKey, $values, $order): void
    {
        if (!empty($values['rs_product_consent'])) $item->add_meta_data('تأیید مشتری', sanitize_text_field((string) $values['rs_product_consent']), true);
    }

    public function ajaxCartAdd(): void
    {
        check_ajax_referer('rs_public_nonce', 'nonce');
        if (!rs_is_wc() || !WC()->cart) {
            wp_send_json_error(['message' => 'افزونه ووکامرس فعال نیست؛ در حالت پیش‌نمایش سبد لوکال استفاده می‌شود.', 'fallback_local' => true]);
        }

        $rawId = sanitize_text_field((string) wp_unslash($_POST['product_id'] ?? ''));
        $qty = max(1, (int) ($_POST['quantity'] ?? 1));
        $variationId = max(0, (int) ($_POST['variation_id'] ?? 0));
        $variation = isset($_POST['variation']) && is_array($_POST['variation'])
            ? array_map('sanitize_text_field', wp_unslash($_POST['variation']))
            : [];

        if (!ctype_digit($rawId) || (int) $rawId <= 0) {
            wp_send_json_error(['message' => 'این محصول دمو است؛ برای افزودن واقعی، درون‌ریزی دمو را از پنل قالب بزنید.', 'fallback_local' => true]);
        }

        $productId = (int) $rawId;
        $product = wc_get_product($productId);
        if (!$product || !$product->is_purchasable() || !$product->is_in_stock()) {
            wp_send_json_error(['message' => 'این محصول در حال حاضر قابل خرید نیست.']);
        }

        if (!apply_filters('woocommerce_add_to_cart_validation', true, $productId, $qty, $variationId, $variation)) {
            $notices = wc_get_notices('error'); wc_clear_notices();
            wp_send_json_error(['message' => wp_strip_all_tags((string) ($notices[0]['notice'] ?? 'ابتدا شرایط محصول را تأیید کنید.'))]);
        }
        $added = WC()->cart->add_to_cart($productId, $qty, $variationId, $variation);
        if (!$added) {
            wp_send_json_error(['message' => 'امکان افزودن محصول به سبد وجود نداشت (گزینه‌های متغیر را انتخاب کنید).']);
        }

        WC()->cart->calculate_totals();
        wp_send_json_success(array_merge(
            $this->buildCartPayload(),
            ['message' => sprintf('«%s» به سبد خرید اضافه شد', $product->get_name())]
        ));
    }

    public function ajaxCartUpdate(): void
    {
        check_ajax_referer('rs_public_nonce', 'nonce');

        if (!rs_is_wc() || !WC()->cart) {
            wp_send_json_error(['fallback_local' => true]);
        }

        $action = sanitize_key((string) ($_POST['cart_action'] ?? 'set'));
        $key = sanitize_text_field((string) wp_unslash($_POST['cart_key'] ?? ''));

        if ($action === 'clear') {
            WC()->cart->empty_cart();
            wp_send_json_success(array_merge($this->buildCartPayload(), ['message' => 'سبد خرید خالی شد']));
        }

        $cartContents = WC()->cart->get_cart();
        if (!isset($cartContents[$key])) {
            // جستجو بر اساس شناسه محصول اگر cart_key پاس نشده بود
            foreach ($cartContents as $k => $item) {
                if ((string) $item['product_id'] === $key || (string) $item['variation_id'] === $key) {
                    $key = $k;
                    break;
                }
            }
        }

        if (!isset($cartContents[$key])) {
            wp_send_json_error(['message' => 'ردیف سبد خرید یافت نشد.']);
        }

        $currentQty = (int) $cartContents[$key]['quantity'];
        $product = $cartContents[$key]['data'];
        $requestedQty = match ($action) {
            'plus' => $currentQty + 1,
            'minus' => max(0, $currentQty - 1),
            'remove' => 0,
            'set' => max(0, (int) ($_POST['quantity'] ?? 1)),
            default => -1,
        };
        if ($requestedQty < 0 || ($product->is_sold_individually() && $requestedQty > 1)
            || ($product->get_max_purchase_quantity() >= 0 && $requestedQty > $product->get_max_purchase_quantity())
            || !apply_filters('woocommerce_update_cart_validation', true, $key, $cartContents[$key], $requestedQty)) {
            wp_send_json_error(['message' => 'تعداد انتخاب‌شده مجاز نیست یا موجودی کافی وجود ندارد.']);
        }
        if ($action === 'plus') {
            WC()->cart->set_quantity($key, $currentQty + 1, true);
        } elseif ($action === 'minus') {
            if ($currentQty <= 1) {
                WC()->cart->remove_cart_item($key);
            } else {
                WC()->cart->set_quantity($key, $currentQty - 1, true);
            }
        } elseif ($action === 'remove') {
            WC()->cart->remove_cart_item($key);
        } elseif ($action === 'set') {
            $newQty = max(0, (int) ($_POST['quantity'] ?? 1));
            if ($newQty === 0) {
                WC()->cart->remove_cart_item($key);
            } else {
                WC()->cart->set_quantity($key, $newQty, true);
            }
        }

        WC()->cart->calculate_totals();
        wp_send_json_success($this->buildCartPayload());
    }

    public function ajaxCartCoupon(): void
    {
        check_ajax_referer('rs_public_nonce', 'nonce');

        if (!rs_is_wc() || !WC()->cart) {
            wp_send_json_error(['message' => 'سبد خرید فعال نیست.']);
        }

        $code = sanitize_text_field(Persian::toEn((string) wp_unslash($_POST['coupon'] ?? '')));
        $remove = !empty($_POST['remove']);

        if ($remove && $code !== '') {
            WC()->cart->remove_coupon($code);
            WC()->cart->calculate_totals();
            wp_send_json_success(array_merge($this->buildCartPayload(), ['message' => 'کد تخفیف حذف شد.']));
        }

        if ($code === '') {
            wp_send_json_error(['message' => 'کد تخفیف را وارد کنید.']);
        }

        if (WC()->cart->has_discount($code)) {
            wp_send_json_error(['message' => 'این کد تخفیف قبلاً روی سفارش شما اعمال شده است.']);
        }

        $applied = WC()->cart->apply_coupon($code);
        wc_clear_notices();

        if (!$applied) {
            wp_send_json_error(['message' => 'این کد تخفیف معتبر نیست یا منقضی شده است.']);
        }

        WC()->cart->calculate_totals();
        wp_send_json_success(array_merge(
            $this->buildCartPayload(),
            ['message' => sprintf('کد تخفیف «%s» با موفقیت اعمال شد.', strtoupper($code))]
        ));
    }

    public function ajaxCartState(): void
    {
        if (!rs_is_wc() || !WC()->cart) {
            wp_send_json_success(['count' => 0, 'count_fa' => '۰', 'items' => []]);
        }
        wp_send_json_success($this->buildCartPayload());
    }

    public function ajaxCheckoutGift(): void
    {
        check_ajax_referer('rs_public_nonce', 'nonce');
        if (rs_is_wc() && WC()->session) {
            WC()->session->set('rs_gift_wrap', !empty($_POST['enabled']));
            if (WC()->cart) {
                WC()->cart->calculate_totals();
            }
        }
        wp_send_json_success($this->buildCartPayload());
    }

    public function ajaxWishlistToggle(): void
    {
        check_ajax_referer('rs_public_nonce', 'nonce');
        $id = sanitize_text_field((string) wp_unslash($_POST['product_id'] ?? ''));
        if ($id === '') {
            wp_send_json_error(['message' => 'شناسه محصول نامعتبر است.']);
        }

        $list = ProductPresenter::currentUserWishlist();
        if (in_array($id, $list, true)) {
            $list = array_values(array_diff($list, [$id]));
            $added = false;
            $msg = 'محصول از علاقه‌مندی‌ها حذف شد';
        } else {
            $list[] = $id;
            $list = array_values(array_unique($list));
            $added = true;
            $msg = 'محصول به علاقه‌مندی‌ها اضافه شد';
        }

        if (is_user_logged_in()) {
            update_user_meta(get_current_user_id(), '_rs_wishlist', $list);
        }

        wp_send_json_success([
            'added' => $added,
            'wishlist' => $list,
            'count' => count($list),
            'count_fa' => rs_fa(count($list)),
            'message' => $msg,
        ]);
    }

    public function ajaxWishlistItems(): void
    {
        $ids = isset($_POST['ids']) && is_array($_POST['ids'])
            ? array_map('sanitize_text_field', wp_unslash($_POST['ids']))
            : ProductPresenter::currentUserWishlist();

        $items = [];
        foreach ($ids as $id) {
            if (rs_is_wc() && ctype_digit((string) $id)) {
                $product = wc_get_product((int) $id);
                if ($product) {
                    $vm = ProductPresenter::fromWcProduct($product);
                    $items[] = [
                        'id' => $vm['id'],
                        'title' => $vm['title'],
                        'url' => $vm['url'],
                        'image' => $vm['image'],
                        'price' => rs_price($vm['price']) . ' تومان',
                        'card_html' => rs_view('partials.product-card', ['product' => $vm, 'wishlistIds' => $ids]),
                    ];
                    continue;
                }
            }

            foreach (DemoCatalog::products() as $demo) {
                if ((string) $demo['id'] === (string) $id) {
                    $vm = ProductPresenter::fromDemoArray($demo);
                    $items[] = [
                        'id' => $vm['id'],
                        'title' => rs_fa($vm['title']),
                        'url' => $vm['url'],
                        'image' => $vm['image'],
                        'price' => rs_price($vm['price']) . ' تومان',
                        'card_html' => rs_view('partials.product-card', ['product' => $vm, 'wishlistIds' => $ids]),
                    ];
                    break;
                }
            }
        }

        wp_send_json_success(['items' => $items, 'count' => count($items), 'count_fa' => rs_fa(count($items))]);
    }

    /**
     * فیلتر سریع AJAX صفحه فروشگاه بدون رفرش
     */
    public function ajaxShopFilter(): void
    {
        /** @var \App\View\Composers\ShopComposer $composer */
        $composer = $this->app->make(\App\View\Composers\ShopComposer::class);
        $data = $composer->buildShopData($_GET);
        $wishlistIds = ProductPresenter::currentUserWishlist();
        $isList = ($data['view'] ?? 'grid') === 'list';

        $cardsHtml = '';
        foreach ($data['products'] as $product) {
            $cardsHtml .= rs_view('partials.product-card', [
                'product' => $product,
                'listMode' => $isList,
                'wishlistIds' => $wishlistIds,
            ]);
        }

        if ($cardsHtml === '') {
            $cardsHtml = '<div class="empty-state" style="grid-column:1/-1"><svg><use href="#i-search"/></svg><h3>محصولی با این فیلترها پیدا نشد</h3><p>می‌توانید فیلترها را تغییر دهید یا همه آن‌ها را پاک کنید.</p><button class="btn btn-primary btn-sm" type="button" data-clear="all">پاک کردن فیلترها</button></div>';
        }

        wp_send_json_success([
            'cards_html' => $cardsHtml,
            'showing_fa' => rs_fa(count($data['products'])),
            'total_fa' => rs_fa($data['total']),
            'page' => $data['page'],
            'total_pages' => $data['total_pages'],
        ]);
    }

    /** Store an address in the same account list without changing the customer's billing profile. */
    public function saveCheckoutAddress(int $userId, array $input): array|\WP_Error
    {
        if ($userId <= 0) return new \WP_Error('login_required', 'برای ذخیره آدرس وارد حساب کاربری شوید.');
        $value = static fn(string $key): string => sanitize_text_field((string) ($input[$key] ?? ''));
        $title = $value('title');
        $name = $value('name');
        $phone = Persian::toEn($value('phone'));
        $country = $value('country') ?: (string) (get_user_meta($userId, 'billing_country', true) ?: WC()->countries->get_base_country());
        if (!isset(WC()->countries->get_allowed_countries()[$country])) return new \WP_Error('invalid_country', 'کشور انتخاب‌شده معتبر نیست.');
        $province = $this->resolveCheckoutState($country, $value('province'));
        $states = WC()->countries->get_states($country);
        $provinceLabel = is_array($states) ? (string) ($states[$province] ?? $province) : $province;
        $city = $value('city');
        $postal = Persian::toEn($value('postal'));
        $address = sanitize_textarea_field((string) ($input['address'] ?? ''));
        if (mb_strlen($title) < 2 || $name === '' || strlen(preg_replace('/\D+/', '', $phone)) < 7 || !\WC_Validation::is_phone($phone) || $province === '' || mb_strlen($city) < 2 || mb_strlen($address) < 8 || ($postal !== '' && !preg_match('/^\d+$/', $postal))) {
            return new \WP_Error('invalid_address', 'عنوان، نام، شماره تماس معتبر، استان، شهر و نشانی کامل را وارد کنید.');
        }
        $saved = get_user_meta($userId, '_rs_saved_addresses', true);
        $saved = is_array($saved) ? array_values($saved) : [];
        if (!$saved && ($billingAddress = (string) get_user_meta($userId, 'billing_address_1', true)) !== '') {
            $user = get_userdata($userId);
            $saved[] = ['id' => 'billing-default', 'title' => 'آدرس پیش‌فرض', 'name' => trim((string) get_user_meta($userId, 'billing_first_name', true) . ' ' . (string) get_user_meta($userId, 'billing_last_name', true)) ?: ($user ? $user->display_name : ''), 'phone' => (string) get_user_meta($userId, 'billing_phone', true), 'province' => (string) get_user_meta($userId, 'billing_state', true), 'city' => (string) get_user_meta($userId, 'billing_city', true), 'postal' => (string) get_user_meta($userId, 'billing_postcode', true), 'address' => $billingAddress, 'address2' => (string) get_user_meta($userId, 'billing_address_2', true), 'isDefault' => true];
        }
        if (count($saved) >= 5) return new \WP_Error('address_limit', 'حداکثر ۵ آدرس ذخیره می‌شود؛ ابتدا یکی را در حساب کاربری حذف کنید.');
        $newItem = ['id' => 'addr-' . wp_generate_uuid4(), 'title' => $title, 'name' => $name, 'phone' => $phone, 'province' => $province, 'province_label' => $provinceLabel, 'country' => $country, 'city' => $city, 'postal' => $postal, 'address' => $address, 'isDefault' => $saved === []];
        $saved[] = $newItem;
        update_user_meta($userId, '_rs_saved_addresses', $saved);
        return $newItem;
    }

    public function ajaxCheckoutAddressSave(): void
    {
        check_ajax_referer('rs_public_nonce', 'nonce');
        $result = $this->saveCheckoutAddress(get_current_user_id(), wp_unslash($_POST));
        if (is_wp_error($result)) wp_send_json_error(['message' => $result->get_error_message()]);
        wp_send_json_success(['address' => $result, 'message' => 'آدرس جدید ثبت و انتخاب شد.']);
    }

    public function ajaxAddressSave(): void
    {
        check_ajax_referer('rs_public_nonce', 'nonce');

        $userId = get_current_user_id();
        $title = sanitize_text_field((string) wp_unslash($_POST['title'] ?? 'منزل'));
        $name = sanitize_text_field((string) wp_unslash($_POST['name'] ?? ''));
        $phone = Persian::toEn(sanitize_text_field((string) wp_unslash($_POST['phone'] ?? '')));
        $province = sanitize_text_field((string) wp_unslash($_POST['province'] ?? 'مازندران'));
        $city = sanitize_text_field((string) wp_unslash($_POST['city'] ?? ''));
        $postal = Persian::toEn(sanitize_text_field((string) wp_unslash($_POST['postal'] ?? '')));
        $address = sanitize_textarea_field((string) wp_unslash($_POST['address'] ?? ''));

        if (mb_strlen($title) < 2 || mb_strlen($city) < 2 || mb_strlen($address) < 8) {
            wp_send_json_error(['message' => 'لطفاً عنوان، شهر و نشانی کامل را وارد کنید.']);
        }

        $saved = get_user_meta($userId, '_rs_saved_addresses', true);
        if (!is_array($saved)) {
            $saved = [];
        }

        $newItem = [
            'id' => 'addr-' . time(),
            'title' => $title,
            'name' => $name !== '' ? $name : wp_get_current_user()->display_name,
            'phone' => preg_replace('/\D+/', '', $phone) ?: '',
            'province' => $province,
            'city' => $city,
            'postal' => preg_replace('/\D+/', '', $postal) ?: '',
            'address' => $address,
            'isDefault' => $saved === [],
        ];

        $saved[] = $newItem;
        update_user_meta($userId, '_rs_saved_addresses', array_slice($saved, -5));

        // همگام‌سازی با آدرس ووکامرس کاربر
        update_user_meta($userId, 'billing_first_name', $newItem['name']);
        update_user_meta($userId, 'billing_phone', $newItem['phone']);
        update_user_meta($userId, 'billing_state', $newItem['province']);
        update_user_meta($userId, 'billing_city', $newItem['city']);
        update_user_meta($userId, 'billing_postcode', $newItem['postal']);
        update_user_meta($userId, 'billing_address_1', $newItem['address']);

        wp_send_json_success(['address' => $newItem, 'message' => 'آدرس جدید با موفقیت ذخیره شد.']);
    }

    public function ajaxAddressDelete(): void
    {
        check_ajax_referer('rs_public_nonce', 'nonce');
        $userId = get_current_user_id();
        $addrId = sanitize_text_field((string) wp_unslash($_POST['address_id'] ?? ''));

        $saved = get_user_meta($userId, '_rs_saved_addresses', true);
        if (is_array($saved)) {
            $saved = array_values(array_filter($saved, static fn(array $a): bool => ($a['id'] ?? '') !== $addrId));
            update_user_meta($userId, '_rs_saved_addresses', $saved);
        }

        wp_send_json_success(['message' => 'آدرس حذف شد.']);
    }

    public function ajaxProfileSave(): void
    {
        check_ajax_referer('rs_public_nonce', 'nonce');

        $userId = get_current_user_id();
        $name = sanitize_text_field((string) wp_unslash($_POST['fullname'] ?? ''));
        $phone = Persian::toEn(sanitize_text_field((string) wp_unslash($_POST['phone'] ?? '')));
        $email = sanitize_email((string) wp_unslash($_POST['email'] ?? ''));
        $nationalCode = Persian::toEn(sanitize_text_field((string) wp_unslash($_POST['national_code'] ?? '')));
        $birthDate = sanitize_text_field((string) wp_unslash($_POST['birth_date'] ?? ''));
        $job = sanitize_text_field((string) wp_unslash($_POST['job'] ?? ''));

        $updateArgs = ['ID' => $userId];
        if ($name !== '') {
            $updateArgs['display_name'] = $name;
            $updateArgs['first_name'] = $name;
            update_user_meta($userId, 'billing_first_name', $name);
        }
        if (is_email($email)) {
            $updateArgs['user_email'] = $email;
            update_user_meta($userId, 'billing_email', $email);
        }
        wp_update_user($updateArgs);

        if ($phone !== '') {
            update_user_meta($userId, 'billing_phone', $phone);
        }
        update_user_meta($userId, '_rs_national_code', $nationalCode);
        update_user_meta($userId, '_rs_birth_date', $birthDate);
        update_user_meta($userId, '_rs_job_title', $job);

        wp_send_json_success(['message' => 'اطلاعات حساب کاربری شما با موفقیت ذخیره شد.']);
    }

    public function ajaxOrderCancel(): void
    {
        check_ajax_referer('rs_public_nonce', 'nonce');
        if (!rs_is_wc()) {
            wp_send_json_error(['message' => 'ووکامرس فعال نیست.']);
        }

        $orderId = (int) ($_POST['order_id'] ?? 0);
        $order = wc_get_order($orderId);
        if (!$order || (int) $order->get_customer_id() !== get_current_user_id()) {
            wp_send_json_error(['message' => 'سفارش یافت نشد یا دسترسی ندارید.']);
        }

        if (!in_array($order->get_status(), ['pending', 'on-hold'], true)) {
            wp_send_json_error(['message' => 'این سفارش در مرحله‌ای است که امکان لغو خودکار ندارد؛ با پشتیبانی تماس بگیرید.']);
        }

        $order->update_status('cancelled', 'لغو سفارش توسط مشتری از پنل کاربری.');
        wp_send_json_success(['message' => 'سفارش لغو شد. برای پیگیری بازگشت وجه با فروشگاه تماس بگیرید.']);
    }

    public function ajaxOrderReorder(): void
    {
        check_ajax_referer('rs_public_nonce', 'nonce');
        if (!rs_is_wc() || !WC()->cart) {
            wp_send_json_error(['message' => 'سبد خرید در دسترس نیست.']);
        }

        $orderId = (int) ($_POST['order_id'] ?? 0);
        $order = wc_get_order($orderId);
        if (!$order || (int) $order->get_customer_id() !== get_current_user_id()) {
            wp_send_json_error(['message' => 'سفارش معتبر نیست.']);
        }

        $addedCount = 0;
        foreach ($order->get_items() as $item) {
            if ($item instanceof \WC_Order_Item_Product) {
                $pid = $item->get_product_id();
                $vid = $item->get_variation_id();
                $qty = max(1, $item->get_quantity());
                if (WC()->cart->add_to_cart($pid, $qty, $vid)) {
                    $addedCount++;
                }
            }
        }

        WC()->cart->calculate_totals();
        wp_send_json_success(array_merge(
            $this->buildCartPayload(),
            [
                'message' => $addedCount > 0 ? 'کالاهای این سفارش مجدداً به سبد خرید اضافه شدند.' : 'موجودی کالاهای این سفارش به پایان رسیده است.',
                'cart_url' => rs_page_url('cart'),
            ]
        ));
    }

    /**
     * ساخت خروجی استاندارد سبد خرید برای همگام‌سازی زنده دراور و صفحه سبد خرید
     *
     * @return array<string, mixed>
     */
    public function buildCartPayload(): array
    {
        if (!rs_is_wc() || !WC()->cart) {
            return ['count' => 0, 'count_fa' => '۰', 'items' => []];
        }

        $cart = WC()->cart;
        $count = (int) $cart->get_cart_contents_count();
        $subtotal = 0;
        $originalTotal = 0;
        $items = [];

        foreach ($cart->get_cart() as $cartKey => $cartItem) {
            /** @var \WC_Product $product */
            $product = $cartItem['data'];
            if (!$product) {
                continue;
            }
            $qty = (int) $cartItem['quantity'];
            $unitPrice = Persian::toToman($product->get_price());
            $unitRegular = Persian::toToman($product->get_regular_price() ?: $product->get_price());
            $lineTotal = $unitPrice * $qty;
            $lineOld = ($unitRegular > $unitPrice) ? ($unitRegular * $qty) : 0;

            $subtotal += $lineTotal;
            $originalTotal += ($unitRegular > $unitPrice ? $unitRegular : $unitPrice) * $qty;

            $vm = ProductPresenter::fromWcProduct($product);
            $items[] = [
                'key' => $cartKey,
                'id' => (string) $product->get_id(),
                'parent_id' => (string) ($cartItem['product_id'] ?? $product->get_id()),
                'title' => $product->get_name(),
                'url' => $product->get_permalink(),
                'image' => $vm['image'],
                'sku_fa' => rs_fa($vm['sku']),
                'cat' => $vm['cat'],
                'in_stock' => $vm['in_stock'],
                'discount' => $vm['discount'],
                'discount_fa' => rs_fa($vm['discount']),
                'quantity' => $qty,
                'quantity_fa' => rs_fa($qty),
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
                'line_total_fa' => rs_price($lineTotal),
                'line_old_fa' => $lineOld > 0 ? (rs_price($lineOld) . ' تومان') : '',
            ];
        }

        $productDiscount = max(0, $originalTotal - $subtotal);
        $couponDiscount = Persian::toToman($cart->get_discount_total());
        $shippingTotal = Persian::toToman($cart->get_shipping_total());
        $totalPayable = Persian::toToman($cart->get_total('edit'));

        $freeLimit = max(1, (int) rs_option('shop.free_shipping_threshold', 10000000));
        $remainingFree = max(0, $freeLimit - $subtotal);
        $freePercent = min(100, (int) round(($subtotal / $freeLimit) * 100));

        return [
            'count' => $count,
            'count_fa' => rs_fa($count),
            'subtotal' => $subtotal,
            'subtotal_fa' => rs_price($subtotal) . ' تومان',
            'original_fa' => rs_price($originalTotal) . ' تومان',
            'discount' => $productDiscount + $couponDiscount,
            'discount_fa' => ($productDiscount + $couponDiscount) > 0
                ? ('− ' . rs_price($productDiscount + $couponDiscount) . ' تومان')
                : '۰ تومان',
            'coupon_discount' => $couponDiscount,
            'coupon_discount_fa' => $couponDiscount > 0 ? ('− ' . rs_price($couponDiscount) . ' تومان') : '۰ تومان',
            'coupons' => array_values($cart->get_applied_coupons()),
            'shipping_fa' => $shippingTotal > 0 ? (rs_price($shippingTotal) . ' تومان') : 'در تسویه حساب مشخص می‌شود',
            'total' => $totalPayable,
            'total_fa' => rs_price($totalPayable) . ' تومان',
            'free_remaining' => $remainingFree,
            'free_remaining_fa' => rs_price($remainingFree),
            'free_percent' => $freePercent,
            'items' => $items,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function parseCsvParam(string $key): array
    {
        if (!isset($_GET[$key])) {
            return [];
        }
        $raw = wp_unslash($_GET[$key]);
        if (is_array($raw)) {
            return array_values(array_filter(array_map('sanitize_title', $raw)));
        }
        return array_values(array_filter(array_map('sanitize_title', explode(',', (string) $raw))));
    }
}
