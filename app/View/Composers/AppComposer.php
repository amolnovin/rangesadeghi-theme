<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Core\ViewComposer;
use App\Providers\WooCommerceServiceProvider;
use App\Support\DemoCatalog;
use App\Support\Persian;
use App\Support\ProductPresenter;

final class AppComposer extends ViewComposer
{
    public static function views(): array
    {
        return ['layouts.app', 'front-page', 'pages.contact', 'pages.stores', 'woocommerce.checkout', 'woocommerce.my-account', 'woocommerce.order-details', 'woocommerce.single-product'];
    }

    public function with(array $data): array
    {
        $general = (array) rs_option('general', []);
        $wishlist = ProductPresenter::currentUserWishlist();

        $cartCount = 0;
        $cartPayload = ['count' => 0, 'count_fa' => '۰', 'subtotal_fa' => '۰ تومان', 'items' => []];

        if (rs_is_wc() && function_exists('WC') && WC()->cart) {
            /** @var WooCommerceServiceProvider $wcProvider */
            $wcProvider = $this->app->make(WooCommerceServiceProvider::class);
            $cartPayload = $wcProvider->buildCartPayload();
            $cartCount = (int) ($cartPayload['count'] ?? 0);
        }

        // دسته‌های مگامنو و منوی موبایل (از ووکامرس یا فالبک دمو)
        $megaCategories = $this->resolveHeaderCategories();

        $popularRaw = (string) ($general['popular_searches'] ?? 'مولتی سورفیس ریچ,تار عنکبوتی ساندورا,اکریلیک سوداکو,تکسچر کارن');
        $popularSearches = array_values(array_filter(array_map('trim', explode(',', $popularRaw))));

        $phoneClean = preg_replace('/\D+/', '', Persian::toEn((string) ($general['phone'] ?? '09113294688'))) ?: '09113294688';

        return [
            'general' => $general,
            'phoneDisplay' => rs_fa((string) ($general['phone_display'] ?? $general['phone'] ?? '۰۹۱۱۳۲۹۴۶۸۸')),
            'phoneTel' => $phoneClean,
            'workingHoursFa' => rs_fa((string) ($general['work_hours'] ?? 'شنبه تا پنج‌شنبه، ۶:۳۰–۲۰:۳۰')),
            'addressFa' => rs_fa((string) ($general['address'] ?? 'مازندران، آمل، خیابان طالب آملی، دریای ۳۰/۱، پلاک ۴۴۴')),
            'addressShortFa' => rs_fa((string) ($general['address_short'] ?? 'آمل، خیابان طالب آملی، نبش دریای ۳۰/۱ — پلاک ۴۴۴')),
            'megaCategories' => $megaCategories,
            'mobileCategories' => $this->resolveMobileCategories(),
            'popularSearches' => $popularSearches,
            'cartCount' => $cartCount,
            'cartCountFa' => rs_fa($cartCount),
            'cartPayload' => $cartPayload,
            'wishlistIds' => $wishlist,
            'wishlistCount' => count($wishlist),
            'wishlistCountFa' => rs_fa(count($wishlist)),
            'currentNav' => $data['activeNav'] ?? $this->detectCurrentNav(),
            'dataPageAttr' => $data['dataPage'] ?? $this->detectDataPage(),
            'isLoggedIn' => is_user_logged_in(),
            'currentUserName' => is_user_logged_in() ? wp_get_current_user()->display_name : '',
        ];
    }

    /**
     * @return array<int, array{name: string, slug: string, url: string, sub: string, icon: string}>
     */
    private function resolveHeaderCategories(): array
    {
        $icons = [
            'craft-art' => 'i-palette',
            'decorative' => 'i-sparkles',
            'building' => 'i-layers',
            'industrial' => 'i-shield',
            'spray-tools' => 'i-brush',
        ];

        if (rs_is_wc() && taxonomy_exists('product_cat')) {
            $terms = get_terms([
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
                'parent' => 0,
                'number' => 8,
                'exclude' => [(int) get_option('default_product_cat', 0)],
            ]);

            if (is_array($terms) && $terms !== []) {
                $out = [];
                foreach ($terms as $term) {
                    $link = get_term_link($term);
                    $sub = (string) get_term_meta($term->term_id, '_rs_cat_sub', true);
                    $out[] = [
                        'name' => $term->name,
                        'slug' => $term->slug,
                        'url' => !is_wp_error($link) ? (string) $link : add_query_arg('cat', $term->slug, rs_page_url('shop')),
                        'sub' => $sub !== '' ? $sub : mb_substr(wp_strip_all_tags($term->description), 0, 42),
                        'icon' => $icons[$term->slug] ?? 'i-palette',
                    ];
                }
                return $out;
            }
        }

        $fallback = [];
        foreach (DemoCatalog::categories() as $cat) {
            $fallback[] = [
                'name' => $cat['title'],
                'slug' => $cat['slug'],
                'url' => add_query_arg('cat', $cat['slug'], rs_page_url('shop')),
                'sub' => $cat['short_title'] ?? '',
                'icon' => $icons[$cat['slug']] ?? 'i-palette',
            ];
        }
        return $fallback;
    }

    /** All product categories in their WooCommerce hierarchy for the mobile drawer. */
    private function resolveMobileCategories(): array
    {
        if (rs_is_wc() && taxonomy_exists('product_cat')) {
            $terms = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC']);
            if (!is_array($terms)) return [];
            $byParent = [];
            foreach ($terms as $term) {
                $link = get_term_link($term);
                if (is_wp_error($link)) continue;
                $byParent[(int) $term->parent][] = ['id' => (int) $term->term_id, 'name' => $term->name, 'url' => (string) $link];
            }
            $tree = function (int $parent, array $visited = []) use (&$tree, $byParent): array {
                $nodes = [];
                foreach ($byParent[$parent] ?? [] as $node) {
                    if (isset($visited[$node['id']])) continue;
                    $visited[$node['id']] = true;
                    $node['children'] = $tree($node['id'], $visited);
                    $nodes[] = $node;
                    unset($visited[$node['id']]);
                }
                return $nodes;
            };
            return $tree(0);
        }
        $nodes = [];
        foreach (DemoCatalog::categories() as $cat) {
            $url = add_query_arg('cat', $cat['slug'], rs_page_url('shop'));
            $nodes[] = ['name' => $cat['title'], 'url' => $url, 'children' => []];
        }
        return $nodes;
    }

    private function detectCurrentNav(): string
    {
        if (is_front_page()) {
            return 'home';
        }
        if (is_page_template('template-categories.php') || is_page('categories')) {
            return 'categories';
        }
        if (is_page_template('template-about.php') || is_page('about')) {
            return 'about';
        }
        if (is_page_template('template-contact.php') || is_page('contact')) {
            return 'contact';
        }
        if (is_page_template('template-stores.php') || is_page('stores')) {
            return 'stores';
        }
        if (rs_is_wc()) {
            if (is_cart()) {
                return 'cart';
            }
            if (is_account_page()) {
                return 'account';
            }
            if (is_shop() || is_product_taxonomy() || is_product()) {
                return 'shop';
            }
        }
        if (is_home() || is_singular('post') || is_category() || is_tag()) {
            return 'blog';
        }
        return '';
    }

    private function detectDataPage(): string
    {
        if (is_front_page()) {
            return '';
        }
        if (is_page_template('template-categories.php') || is_page('categories')) {
            return 'categories';
        }
        if (is_page_template('template-about.php') || is_page('about')) {
            return 'about';
        }
        if (is_page_template('template-contact.php') || is_page('contact')) {
            return 'contact';
        }
        if (is_page_template('template-stores.php') || is_page('stores')) {
            return 'stores';
        }
        if (is_page_template('template-order-tracking.php') || is_page('order-details')) {
            return 'order';
        }
        if (rs_is_wc()) {
            if (is_product()) {
                return 'product';
            }
            if (is_cart()) {
                return 'cart';
            }
            if (is_checkout() && !is_wc_endpoint_url('order-received')) {
                return 'checkout';
            }
            if (is_wc_endpoint_url('view-order') || is_wc_endpoint_url('order-received')) {
                return 'order';
            }
            if (is_account_page()) {
                return 'account';
            }
            if (is_shop() || is_product_taxonomy()) {
                return 'shop';
            }
        }
        if (is_singular('post')) {
            return 'article';
        }
        if (is_home() || is_category() || is_tag()) {
            return 'blog';
        }
        return 'page';
    }
}
