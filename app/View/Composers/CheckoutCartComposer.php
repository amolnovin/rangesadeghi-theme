<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Core\ViewComposer;
use App\Providers\WooCommerceServiceProvider;
use App\Support\DemoCatalog;
use App\Support\Persian;
use App\Support\ProductPresenter;
use WC_Order;
use WP_Query;

final class CheckoutCartComposer extends ViewComposer
{
    public static function views(): array
    {
        return [
            'woocommerce.cart',
            'woocommerce.checkout',
            'woocommerce.order-details',
            'woocommerce.my-account',
        ];
    }

    public function with(array $data): array
    {
        /** @var WooCommerceServiceProvider $wcService */
        $wcService = $this->app->make(WooCommerceServiceProvider::class);
        $cartPayload = $wcService->buildCartPayload();
        $wishlistIds = ProductPresenter::currentUserWishlist();

        return array_merge(
            [
                'cartPayload' => $cartPayload,
                'wishlistIds' => $wishlistIds,
                'crossSells' => $this->resolveCrossSells(),
                'deliveryDays' => $this->buildUpcomingJalaliDays(5),
                'deliverySlots' => array_values(array_filter(array_map('trim', preg_split('/[,|]+/', (string) rs_option('shop.delivery_slots', '۹ تا ۱۲,۱۲ تا ۱۵,۱۵ تا ۱۸,۱۸ تا ۲۰:۳۰')) ?: []))),
                'giftWrapEnabled' => (bool) rs_option('shop.gift_wrap_enabled', true),
                'giftWrapCostFa' => rs_price((int) rs_option('shop.gift_wrap_cost', 35000)),
            ],
            $this->buildAccountAndCheckoutUserContext(),
            $this->buildOrderDetailsContext()
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolveCrossSells(): array
    {
        $items = [];
        if (rs_is_wc() && WC()->cart) {
            $crossIds = WC()->cart->get_cross_sells();
            foreach (array_slice($crossIds, 0, 5) as $id) {
                $p = wc_get_product($id);
                if ($p) {
                    $items[] = ProductPresenter::fromWcProduct($p);
                }
            }
            if ($items === []) {
                $q = new WP_Query([
                    'post_type' => 'product',
                    'post_status' => 'publish',
                    'posts_per_page' => 5,
                    'meta_key' => 'total_sales',
                    'orderby' => 'meta_value_num',
                    'order' => 'DESC',
                    'no_found_rows' => true,
                ]);
                foreach ($q->posts as $post) {
                    $p = wc_get_product($post->ID);
                    if ($p) {
                        $items[] = ProductPresenter::fromWcProduct($p);
                    }
                }
            }
        }

        if ($items === []) {
            foreach (array_slice(DemoCatalog::products(), 0, 5) as $d) {
                $items[] = ProductPresenter::fromDemoArray($d);
            }
        }

        return $items;
    }

    /**
     * ساخت ۵ روز کاری آینده به تقویم جلالی برای انتخاب بازه تحویل
     *
     * @return array<int, array{value: string, weekday: string, date: string}>
     */
    private function buildUpcomingJalaliDays(int $count): array
    {
        $weekdays = [
            0 => 'یک‌شنبه',
            1 => 'دوشنبه',
            2 => 'سه‌شنبه',
            3 => 'چهارشنبه',
            4 => 'پنج‌شنبه',
            5 => 'جمعه',
            6 => 'شنبه',
        ];

        $days = [];
        $offset = 1;
        while (count($days) < $count && $offset < 14) {
            $ts = time() + ($offset * DAY_IN_SECONDS);
            $w = (int) gmdate('w', $ts + (int) (3.5 * HOUR_IN_SECONDS));
            $offset++;
            if ($w === 5) { // رد کردن جمعه
                continue;
            }
            $jDate = Persian::jalaliDate($ts);
            // حذف سال برای نمایش جمع‌وجور مثل «۲۲ بهمن»
            $parts = explode(' ', $jDate);
            $shortDate = count($parts) >= 2 ? ($parts[0] . ' ' . $parts[1]) : $jDate;
            $days[] = [
                'value' => $weekdays[$w] . ' ' . $shortDate,
                'weekday' => $weekdays[$w],
                'date' => $shortDate,
            ];
        }

        return $days;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAccountAndCheckoutUserContext(): array
    {
        if (!is_user_logged_in()) {
            return [
                'accountUser' => null,
                'savedAddresses' => [],
                'userOrders' => [],
                'wishlistProducts' => $this->resolveWishlistProducts(),
                'paymentGateways' => $this->resolveAvailableGateways(),
            ];
        }

        $u = wp_get_current_user();
        $uid = (int) $u->ID;
        $phone = (string) get_user_meta($uid, 'billing_phone', true);
        $addresses = get_user_meta($uid, '_rs_saved_addresses', true);
        if (!is_array($addresses) || $addresses === []) {
            $billCity = (string) get_user_meta($uid, 'billing_city', true);
            $billAddr = (string) get_user_meta($uid, 'billing_address_1', true);
            if ($billAddr !== '') {
                $addresses = [[
                    'id' => 'billing-default',
                    'title' => 'آدرس پیش‌فرض',
                    'name' => $u->display_name,
                    'phone' => $phone,
                    'province' => (string) (get_user_meta($uid, 'billing_state', true) ?: 'مازندران'),
                    'city' => $billCity !== '' ? $billCity : 'آمل',
                    'postal' => (string) get_user_meta($uid, 'billing_postcode', true),
                    'address' => $billAddr,
                    'isDefault' => true,
                ]];
            } else {
                $addresses = [];
            }
        }

        $ordersList = [];
        $totalSpent = 0;
        if (rs_is_wc()) {
            $wcOrders = wc_get_orders([
                'customer_id' => $uid,
                'limit' => 20,
                'orderby' => 'date',
                'order' => 'DESC',
            ]);

            foreach ($wcOrders as $ord) {
                if (!$ord instanceof WC_Order) {
                    continue;
                }
                $ordTotal = Persian::toToman($ord->get_total());
                if (!in_array($ord->get_status(), ['cancelled', 'failed', 'refunded'], true)) {
                    $totalSpent += $ordTotal;
                }
                $ordersList[] = $this->presentOrderSummary($ord);
            }
        }

        $initials = mb_substr(trim($u->display_name ?: $u->user_login), 0, 2);

        return [
            'accountUser' => [
                'id' => $uid,
                'name' => $u->display_name ?: $u->user_login,
                'initials' => $initials,
                'email' => $u->user_email,
                'phone' => $phone,
                'phone_fa' => rs_fa($phone),
                'national_code' => (string) get_user_meta($uid, '_rs_national_code', true),
                'birth_date' => (string) get_user_meta($uid, '_rs_birth_date', true),
                'job' => (string) get_user_meta($uid, '_rs_job_title', true),
                'orders_count_fa' => rs_fa(count($ordersList)),
                'total_spent_fa' => rs_price($totalSpent),
                'points_fa' => has_action('woocommerce_account_wallet_endpoint', ['Amol_Wallet_Frontend', 'account_endpoint_content']) && class_exists('Amol_Wallet_Points') && \Amol_Wallet_Points::is_enabled() ? rs_fa(\Amol_Wallet_Points::get_points($uid)) : '—',
                'logout_url' => wp_logout_url(home_url('/')),
            ],
            'savedAddresses' => $addresses,
            'userOrders' => $ordersList,
            'wishlistProducts' => $this->resolveWishlistProducts(),
            'paymentGateways' => $this->resolveAvailableGateways(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildOrderDetailsContext(): array
    {
        if (!rs_is_wc()) {
            return ['orderDetail' => null];
        }

        $orderId = 0;
        if (function_exists('is_wc_endpoint_url')) {
            if (is_wc_endpoint_url('view-order')) {
                $orderId = (int) get_query_var('view-order');
            } elseif (is_wc_endpoint_url('order-received')) {
                $orderId = (int) get_query_var('order-received');
            }
        }
        if ($orderId === 0 && !empty($_GET['order_id'])) {
            $rawId = preg_replace('/\D+/', '', Persian::toEn((string) $_GET['order_id'])) ?? '';
            $orderId = (int) $rawId;
        }

        if ($orderId <= 0) {
            return ['orderDetail' => null];
        }

        $order = wc_get_order($orderId);
        if (!$order instanceof WC_Order) {
            return ['orderDetail' => null];
        }

        // بررسی دسترسی امنیتی به سفارش (صاحب سفارش، مدیر، یا دارای order key معتبر)
        $orderKey = isset($_GET['key']) ? sanitize_text_field((string) $_GET['key']) : '';
        $isOwner = is_user_logged_in() && ((int) $order->get_customer_id() === get_current_user_id() || current_user_can('manage_woocommerce'));
        $hasValidKey = $orderKey !== '' && hash_equals($order->get_order_key(), $orderKey);

        if (!$isOwner && !$hasValidKey) {
            return ['orderDetail' => null, 'orderAccessDenied' => true];
        }

        return ['orderDetail' => $this->presentOrderSummary($order, true)];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentOrderSummary(WC_Order $order, bool $full = false): array
    {
        $status = $order->get_status();
        $statusMap = [
            'pending' => ['label' => 'در انتظار پرداخت', 'badge' => 'warn', 'tab' => 'current', 'step' => 1],
            'on-hold' => ['label' => 'در انتظار تأیید پرداخت', 'badge' => 'warn', 'tab' => 'current', 'step' => 2],
            'processing' => ['label' => 'در حال آماده‌سازی و ارسال', 'badge' => 'info', 'tab' => 'current', 'step' => 3],
            'completed' => ['label' => wc_get_order_status_name('completed'), 'badge' => 'ok', 'tab' => 'current', 'step' => 2],
            'rs-delivered' => ['label' => 'تحویل شده', 'badge' => 'ok', 'tab' => 'delivered', 'step' => 5],
            'cancelled' => ['label' => 'لغو شده', 'badge' => 'muted', 'tab' => 'canceled', 'step' => 0],
            'refunded' => ['label' => 'مسترد شده', 'badge' => 'muted', 'tab' => 'returned', 'step' => 0],
            'failed' => ['label' => 'پرداخت ناموفق', 'badge' => 'muted', 'tab' => 'canceled', 'step' => 0],
        ];
        $st = $statusMap[$status] ?? ['label' => wc_get_order_status_name($status), 'badge' => 'info', 'tab' => 'current', 'step' => 2];

        $items = [];
        foreach ($order->get_items() as $item) {
            if (!$item instanceof \WC_Order_Item_Product) {
                continue;
            }
            $product = $item->get_product();
            $img = $product ? ProductPresenter::fromWcProduct($product)['image'] : rs_img('cat-craft.webp');
            $sku = $product ? ($product->get_sku() ?: ('RS-' . $product->get_id())) : '';
            $unit = $item->get_quantity() > 0 ? Persian::toToman($item->get_total() / $item->get_quantity()) : 0;

            $items[] = [
                'title' => $item->get_name(),
                'url' => $product ? $product->get_permalink() : rs_page_url('shop'),
                'image' => $img,
                'sku_fa' => rs_fa($sku),
                'qty' => $item->get_quantity(),
                'qty_fa' => rs_fa($item->get_quantity()),
                'unit_fa' => rs_price($unit),
                'total_fa' => rs_price(Persian::toToman($item->get_total())),
                'invoice_total' => wc_price($item->get_total(), ['currency' => $order->get_currency()]),
                'invoice_unit' => wc_price($item->get_quantity() > 0 ? $item->get_total() / $item->get_quantity() : 0, ['currency' => $order->get_currency()]),
            ];
        }

        $createdTs = $order->get_date_created() ? $order->get_date_created()->getTimestamp() : time();
        $detailUrl = add_query_arg(
            ['order_id' => $order->get_id()],
            rs_page_url('order-details')
        );

        $data = [
            'id' => $order->get_id(),
            'number_fa' => rs_fa(preg_replace('/^RS\s*-\s*/i', '', (string) $order->get_order_number()) ?? (string) $order->get_order_number()),
            'status_raw' => $status,
            'status_label' => $st['label'],
            'badge_class' => $st['badge'],
            'tab_group' => $st['tab'],
            'step_index' => $this->orderTrackingStep($order),
            'date_fa' => Persian::jalaliDate($createdTs),
            'total_fa' => rs_price(Persian::toToman($order->get_total())),
            'subtotal_fa' => rs_price(Persian::toToman($order->get_subtotal())),
            'discount_fa' => rs_price(Persian::toToman($order->get_discount_total())),
            'shipping_fa' => Persian::toToman($order->get_shipping_total()) > 0
                ? (rs_price(Persian::toToman($order->get_shipping_total())) . ' تومان')
                : 'رایگان',
            'payment_method' => $order->get_payment_method_title() ?: 'پرداخت اینترنتی',
            'tracking_code' => rs_fa((string) ($order->get_meta('_rs_tracking_code') ?: '')),
            'carrier_name' => (string) ($order->get_meta('_rs_carrier_name') ?: ($order->get_shipping_method() ?: '—')),
            'delivery_day' => rs_fa((string) ($order->get_meta('_rs_delivery_day') ?: '')),
            'delivery_slot' => rs_fa((string) ($order->get_meta('_rs_delivery_slot') ?: '')),
            'items' => $items,
            'items_count_fa' => rs_fa($order->get_item_count()),
            'detail_url' => $detailUrl,
            'can_cancel' => in_array($status, ['pending', 'on-hold'], true),
        ];

        if ($full) {
            $data['receiver_name'] = trim($order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name())
                ?: trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
            $data['receiver_phone_fa'] = rs_fa((string) ($order->get_meta('_rs_receiver_phone') ?: $order->get_billing_phone()));
            $data['address_full'] = rs_fa(implode('، ', array_filter([
                $order->get_billing_state(),
                $order->get_billing_city(),
                $order->get_billing_address_1(),
                $order->get_billing_address_2(),
            ])));
            $data['postcode_fa'] = rs_fa($order->get_billing_postcode());
            $data['invoice_totals'] = $order->get_order_item_totals();
            $data['invoice_billing'] = wp_strip_all_tags(str_replace('<br/>', ', ', $order->get_formatted_billing_address()));
            $data['transaction_id'] = $order->get_transaction_id() ?: '—';
        }

        return $data;
    }

    /** Map a current WooCommerce status to the highest configured milestone. */
    private function orderTrackingStep(WC_Order $order): int
    {
        $status = $order->get_status();
        if (in_array($status, ['cancelled', 'failed', 'refunded'], true)) return 0;
        if ($status === 'rs-delivered') return 5;
        $step = $order->is_paid() ? 2 : 1;
        foreach (['registered', 'payment', 'preparing', 'shipping'] as $index => $stage) {
            $configured = (string) rs_option('shop.tracking_' . $stage, '');
            if ($configured !== '' && $configured === 'wc-' . $status) $step = max($step, $index + 1);
        }
        return $step;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolveWishlistProducts(): array
    {
        $ids = ProductPresenter::currentUserWishlist();
        $out = [];
        foreach ($ids as $id) {
            if (rs_is_wc() && ctype_digit((string) $id)) {
                $p = wc_get_product((int) $id);
                if ($p) {
                    $out[] = ProductPresenter::fromWcProduct($p);
                    continue;
                }
            }
            foreach (DemoCatalog::products() as $d) {
                if ((string) $d['id'] === (string) $id) {
                    $out[] = ProductPresenter::fromDemoArray($d);
                    break;
                }
            }
        }
        return $out;
    }

    /**
     * @return array<int, array{id: string, title: string, desc: string}>
     */
    private function resolveAvailableGateways(): array
    {
        if (rs_is_wc() && WC()->payment_gateways()) {
            $avail = WC()->payment_gateways()->get_available_payment_gateways();
            if (!empty($avail)) {
                $out = [];
                foreach ($avail as $id => $gw) {
                    $out[] = [
                        'id' => (string) $id,
                        'title' => $gw->get_title(),
                        'desc' => wp_strip_all_tags((string) $gw->get_description()),
                    ];
                }
                return $out;
            }
        }

        return [
            ['id' => 'zarinpal', 'title' => 'پرداخت اینترنتی (کلیه کارت‌های عضو شتاب)', 'desc' => 'درگاه امن شاپرک · صدور آنی رسید خرید'],
            ['id' => 'bacs', 'title' => 'کارت به کارت / واریز بانکی', 'desc' => 'ثبت سفارش و ارسال رسید به پشتیبانی واتساپ'],
            ['id' => 'cod', 'title' => 'پرداخت در محل (ویژه شهر آمل)', 'desc' => 'تسویه با دستگاه کارت‌خوان پیک فروشگاه هنگام تحویل'],
        ];
    }
}
