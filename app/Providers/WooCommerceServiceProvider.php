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

        // ذخیره متای اختصاصی تسویه حساب روی سفارش ووکامرس (سازگار با HPOS)
        add_action('woocommerce_checkout_create_order', [$this, 'saveCustomOrderMeta'], 10, 2);
        add_action('woocommerce_checkout_process', [$this, 'validateDeliveryPreference']);

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
            'rs_address_delete' => [$this, 'ajaxAddressDelete'],
            'rs_profile_save' => [$this, 'ajaxProfileSave'],
            'rs_order_cancel' => [$this, 'ajaxOrderCancel'],
            'rs_order_reorder' => [$this, 'ajaxOrderReorder'],
        ];

        foreach ($authAjax as $action => $handler) {
            add_action("wp_ajax_{$action}", $handler);
        }
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

    public function validateDeliveryPreference(): void
    {
        if (!WC()->cart || !WC()->cart->needs_shipping() || !rs_option('shop.delivery_slots_enabled', true)) return;
        $day = sanitize_text_field((string) wp_unslash($_POST['rs_delivery_day'] ?? ''));
        $slot = sanitize_text_field((string) wp_unslash($_POST['rs_delivery_slot'] ?? ''));
        if ($day === '' && $slot === '') return;
        $slots = array_filter(array_map('trim', explode(',', (string) rs_option('shop.delivery_slots', ''))));
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $day);
        if (!$date || $date->format('Y-m-d') !== $day || $day < wp_date('Y-m-d') || !in_array($slot, $slots, true)) {
            wc_add_notice('لطفاً روز و بازه ترجیحی معتبر انتخاب کنید یا هر دو را خالی بگذارید.', 'error');
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

        if (rs_option('shop.delivery_slots_enabled', true) && WC()->cart && WC()->cart->needs_shipping() && $day !== '') {
            $order->update_meta_data('_rs_delivery_day', $day);
        }
        if (rs_option('shop.delivery_slots_enabled', true) && WC()->cart && WC()->cart->needs_shipping() && $slot !== '') {
            $order->update_meta_data('_rs_delivery_slot', $slot);
        }
        $order->update_meta_data('_rs_gift_wrap', $gift);
        $order->update_meta_data('_rs_official_invoice', $invoice);

        if ($rcName !== '') {
            $order->update_meta_data('_rs_receiver_name', $rcName);
            $order->set_shipping_first_name($rcName);
        }
        if ($rcPhone !== '') {
            $order->update_meta_data('_rs_receiver_phone', $rcPhone);
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

        if (!in_array($order->get_status(), ['pending', 'on-hold', 'processing'], true)) {
            wp_send_json_error(['message' => 'این سفارش در مرحله‌ای است که امکان لغو خودکار ندارد؛ با پشتیبانی تماس بگیرید.']);
        }

        $order->update_status('cancelled', 'لغو سفارش توسط مشتری از پنل کاربری.');
        wp_send_json_success(['message' => 'سفارش لغو شد و درخواست بازگشت وجه ثبت گردید.']);
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
