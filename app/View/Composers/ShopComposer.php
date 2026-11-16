<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Core\ViewComposer;
use App\Support\DemoCatalog;
use App\Support\Persian;
use App\Support\ProductPresenter;
use WP_Query;

final class ShopComposer extends ViewComposer
{
    public static function views(): array
    {
        return ['woocommerce.archive-product'];
    }

    public function with(array $data): array
    {
        return array_merge($this->buildShopData($_GET), [
            'wishlistIds' => ProductPresenter::currentUserWishlist(),
        ]);
    }

    /**
     * ساخت داده‌های کامل صفحه فروشگاه (برای رندر SSR و پاسخ فیلتر AJAX)
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function buildShopData(array $params): array
    {
        $shopCfg = (array) rs_option('shop', []);
        $perPage = max(3, (int) ($shopCfg['per_page'] ?? 9));
        $page = max(1, (int) ($params['paged'] ?? (get_query_var('paged') ?: 1)));
        $sort = sanitize_key((string) ($params['rs_sort'] ?? ($shopCfg['default_sort'] ?? 'default')));
        $view = sanitize_key((string) ($params['view'] ?? ($shopCfg['default_view'] ?? 'grid')));
        if (!in_array($view, ['grid', 'list'], true)) {
            $view = 'grid';
        }

        $searchQuery = sanitize_text_field((string) ($params['q'] ?? ($params['s'] ?? get_search_query())));
        $selectedCats = $this->csvArray($params['cat'] ?? []);
        if (rs_is_wc() && function_exists('is_product_category') && is_product_category()) {
            $term = get_queried_object();
            if ($term && isset($term->slug) && !in_array($term->slug, $selectedCats, true)) {
                $selectedCats[] = (string) $term->slug;
            }
        }
        $selectedBrands = $this->csvArray($params['brand'] ?? []);
        if (rs_is_wc() && is_tax('product_brand')) {
            $term = get_queried_object();
            if ($term && isset($term->slug) && !in_array($term->slug, $selectedBrands, true)) {
                $selectedBrands[] = (string) $term->slug;
            }
        }

        $minPrice = (int) preg_replace('/\D+/', '', Persian::toEn((string) ($params['min_price'] ?? '')));
        $maxPrice = (int) preg_replace('/\D+/', '', Persian::toEn((string) ($params['max_price'] ?? '')));
        $onlySale = !empty($params['sale']) && (string) $params['sale'] === '1';
        $onlyStock = !empty($params['in_stock']) && (string) $params['in_stock'] === '1';

        $products = [];
        $total = 0;
        $totalPages = 1;

        $wcPublished = (rs_is_wc() && post_type_exists('product'))
            ? (int) (wp_count_posts('product')->publish ?? 0)
            : 0;

        if ($wcPublished > 0) {
            $args = [
                'post_type' => 'product',
                'post_status' => 'publish',
                'posts_per_page' => $perPage,
                'paged' => $page,
            ];
            if ($searchQuery !== '') {
                $args['s'] = Persian::toEn($searchQuery);
            }

            $taxQuery = [];
            $metaQuery = [];

            if ($selectedCats !== []) {
                $taxQuery[] = [
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => $selectedCats,
                ];
            }
            if ($selectedBrands !== []) {
                if (taxonomy_exists('product_brand')) {
                    $taxQuery[] = [
                        'taxonomy' => 'product_brand',
                        'field' => 'slug',
                        'terms' => $selectedBrands,
                    ];
                } else {
                    $metaQuery[] = [
                        'key' => '_rs_brand_slug',
                        'value' => $selectedBrands,
                        'compare' => 'IN',
                    ];
                }
            }

            $mult = (function_exists('get_woocommerce_currency') && get_woocommerce_currency() === 'IRR') ? 10 : 1;
            if ($minPrice > 0) {
                $metaQuery[] = ['key' => '_price', 'value' => $minPrice * $mult, 'compare' => '>=', 'type' => 'NUMERIC'];
            }
            if ($maxPrice > 0) {
                $metaQuery[] = ['key' => '_price', 'value' => $maxPrice * $mult, 'compare' => '<=', 'type' => 'NUMERIC'];
            }
            if ($onlyStock) {
                $metaQuery[] = ['key' => '_stock_status', 'value' => 'instock'];
            }
            if ($onlySale && function_exists('wc_get_product_ids_on_sale')) {
                $saleIds = wc_get_product_ids_on_sale();
                $args['post__in'] = !empty($saleIds) ? $saleIds : [0];
            }

            switch ($sort) {
                case 'newest':
                    $args['orderby'] = 'date';
                    $args['order'] = 'DESC';
                    break;
                case 'popular':
                    $args['meta_key'] = '_wc_average_rating';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'DESC';
                    break;
                case 'bestseller':
                    $args['meta_key'] = 'total_sales';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'DESC';
                    break;
                case 'cheap':
                    $args['meta_key'] = '_price';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'ASC';
                    break;
                case 'expensive':
                    $args['meta_key'] = '_price';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'DESC';
                    break;
                case 'name':
                    $args['orderby'] = 'title';
                    $args['order'] = 'ASC';
                    break;
            }

            if ($taxQuery !== []) {
                $args['tax_query'] = $taxQuery;
            }
            if ($metaQuery !== []) {
                $args['meta_query'] = $metaQuery;
            }

            $query = new WP_Query($args);
            $total = (int) $query->found_posts;
            $totalPages = max(1, (int) $query->max_num_pages);

            foreach ($query->posts as $post) {
                $p = wc_get_product($post->ID);
                if ($p) {
                    $products[] = ProductPresenter::fromWcProduct($p);
                }
            }
        } else {
            // فالبک کاتالوگ دمو در صورت خالی بودن اولیه فروشگاه
            $catSlugToName = [];
            foreach (DemoCatalog::categories() as $c) {
                $catSlugToName[$c['slug']] = $c['name'];
            }
            $wantedCatNames = array_values(array_filter(array_map(static fn(string $s): string => $catSlugToName[$s] ?? $s, $selectedCats)));

            $filtered = array_values(array_filter(DemoCatalog::products(), static function (array $item) use ($searchQuery, $wantedCatNames, $selectedBrands, $minPrice, $maxPrice, $onlySale, $onlyStock): bool {
                if ($searchQuery !== '') {
                    $q = Persian::normalize($searchQuery);
                    $hay = Persian::normalize($item['title'] . ' ' . $item['cat'] . ' ' . $item['short']);
                    if (!str_contains($hay, $q)) {
                        return false;
                    }
                }
                if ($wantedCatNames !== [] && !in_array($item['cat'], $wantedCatNames, true)) {
                    return false;
                }
                if ($selectedBrands !== [] && !in_array($item['brand'], $selectedBrands, true)) {
                    return false;
                }
                if ($minPrice > 0 && (int) $item['price'] < $minPrice) {
                    return false;
                }
                if ($maxPrice > 0 && (int) $item['price'] > $maxPrice) {
                    return false;
                }
                if ($onlySale && ((int) ($item['old'] ?? 0)) <= (int) $item['price']) {
                    return false;
                }
                if ($onlyStock && (int) $item['stock'] <= 0) {
                    return false;
                }
                return true;
            }));

            usort($filtered, static function (array $a, array $b) use ($sort): int {
                return match ($sort) {
                    'newest' => strcmp((string) $b['date'], (string) $a['date']),
                    'popular' => ($b['rating'] <=> $a['rating']) ?: ($b['reviews'] <=> $a['reviews']),
                    'bestseller' => ((int) $b['sold']) <=> ((int) $a['sold']),
                    'cheap' => ((int) $a['price']) <=> ((int) $b['price']),
                    'expensive' => ((int) $b['price']) <=> ((int) $a['price']),
                    'name' => strcmp((string) $a['title'], (string) $b['title']),
                    default => 0,
                };
            });

            $total = count($filtered);
            $totalPages = max(1, (int) ceil($total / $perPage));
            $page = min($page, $totalPages);
            foreach (array_slice($filtered, ($page - 1) * $perPage, $perPage) as $demo) {
                $products[] = ProductPresenter::fromDemoArray($demo);
            }
        }

        return [
            'shopCfg' => $shopCfg,
            'products' => $products,
            'total' => $total,
            'totalFa' => rs_fa($total),
            'showingFa' => rs_fa(count($products)),
            'page' => $page,
            'total_pages' => $totalPages,
            'sort' => $sort,
            'view' => $view,
            'searchQuery' => $searchQuery,
            'selectedCats' => $selectedCats,
            'selectedBrands' => $selectedBrands,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'onlySale' => $onlySale,
            'onlyStock' => $onlyStock,
            'filterCategories' => $this->resolveFilterCategories($wcPublished > 0),
            'filterBrands' => $this->resolveFilterBrands($wcPublished > 0),
            'archiveHeading' => $this->resolveArchiveHeading($searchQuery),
        ];
    }

    /**
     * @return array<int, array{slug: string, name: string, count: int, img: string}>
     */
    private function resolveFilterCategories(bool $useWc): array
    {
        if ($useWc && taxonomy_exists('product_cat')) {
            $terms = get_terms([
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
                'exclude' => [(int) get_option('default_product_cat', 0)],
            ]);
            if (is_array($terms) && $terms !== []) {
                $out = [];
                foreach ($terms as $t) {
                    $imgMeta = (string) get_term_meta($t->term_id, '_rs_cat_image', true);
                    $thumbId = (int) get_term_meta($t->term_id, 'thumbnail_id', true);
                    $thumbUrl = $thumbId > 0 ? (string) wp_get_attachment_image_url($thumbId, 'medium') : '';
                    $out[] = [
                        'slug' => $t->slug,
                        'name' => $t->name,
                        'count' => (int) $t->count,
                        'img' => $thumbUrl !== '' ? $thumbUrl : rs_img($imgMeta ?: 'cat-craft.webp'),
                    ];
                }
                return $out;
            }
        }

        $counts = [];
        foreach (DemoCatalog::products() as $p) {
            $counts[$p['cat']] = ($counts[$p['cat']] ?? 0) + 1;
        }

        $fallback = [];
        foreach (DemoCatalog::categories() as $c) {
            $fallback[] = [
                'slug' => $c['slug'],
                'name' => $c['name'],
                'count' => $counts[$c['name']] ?? 0,
                'img' => rs_img($c['img']),
            ];
        }
        return $fallback;
    }

    /**
     * @return array<int, array{slug: string, title: string, count: int}>
     */
    private function resolveFilterBrands(bool $useWc): array
    {
        if ($useWc && taxonomy_exists('product_brand')) {
            $terms = get_terms(['taxonomy' => 'product_brand', 'hide_empty' => false]);
            if (is_array($terms) && $terms !== []) {
                $out = [];
                foreach ($terms as $t) {
                    $out[] = ['slug' => $t->slug, 'title' => $t->name, 'count' => (int) $t->count];
                }
                return $out;
            }
        }

        $counts = [];
        foreach (DemoCatalog::products() as $p) {
            $counts[$p['brand']] = ($counts[$p['brand']] ?? 0) + 1;
        }

        $fallback = [];
        foreach (DemoCatalog::brands() as $b) {
            $fallback[] = [
                'slug' => $b['slug'],
                'title' => $b['title'],
                'count' => $counts[$b['slug']] ?? 0,
            ];
        }
        return $fallback;
    }

    private function resolveArchiveHeading(string $searchQuery): string
    {
        if ($searchQuery !== '') {
            return 'نتایج جستجو برای «' . $searchQuery . '»';
        }
        if (rs_is_wc() && (is_product_category() || is_product_tag() || is_tax('product_brand'))) {
            $term = get_queried_object();
            if ($term && isset($term->name)) {
                return (string) $term->name;
            }
        }
        return 'فروشگاه محصولات رنگ صادقی';
    }

    /**
     * @return array<int, string>
     */
    private function csvArray(mixed $val): array
    {
        if (is_array($val)) {
            return array_values(array_filter(array_map('sanitize_title', $val)));
        }
        if (!is_string($val) || trim($val) === '') {
            return [];
        }
        return array_values(array_filter(array_map('sanitize_title', explode(',', $val))));
    }
}
