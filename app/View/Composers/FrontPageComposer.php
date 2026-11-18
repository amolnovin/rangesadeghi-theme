<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Core\ViewComposer;
use App\Support\DemoCatalog;
use App\Support\Persian;
use App\Support\ProductPresenter;
use WP_Query;

final class FrontPageComposer extends ViewComposer
{
    public static function views(): array
    {
        return ['front-page'];
    }

    public function with(array $data): array
    {
        $cacheEnabled = (bool) rs_option('performance.cache_frontpage_queries', true);
        if ($cacheEnabled) {
            $cached = get_transient('rs_frontpage_cache');
            if (is_array($cached)) {
                $cached['wishlistIds'] = ProductPresenter::currentUserWishlist();
                return $cached;
            }
        }

        $home = (array) rs_option('home', []);
        $demoFallback = (bool) ($home['demo_fallback_when_empty'] ?? true);

        $orderRaw = (string) ($home['section_order'] ?? 'hero,categories,deal,services,projects,bestsellers,brands,palette,newest,blog,closing');
        $sectionOrder = array_values(array_filter(array_map('trim', explode(',', $orderRaw))));

        $dealCount = max(2, (int) ($home['deal_count'] ?? 8));
        $bestCount = max(4, (int) ($home['bestsellers_count'] ?? 8));
        $newCount = max(4, (int) ($home['newest_count'] ?? 8));
        $blogCount = max(2, (int) ($home['blog_count'] ?? 3));

        $payload = [
            'home' => $home,
            'sectionOrder' => $sectionOrder,
            'heroSlides' => (array) ($home['hero_slides'] ?? []),
            'heroSideCards' => (array) ($home['hero_side_cards'] ?? []),
            'homeCategories' => $this->resolveHomeCategories(),
            'dealProducts' => $this->queryDeals($dealCount, (string) ($home['deal_source'] ?? 'on_sale'), (string) ($home['deal_category'] ?? ''), $demoFallback),
            'services' => (array) ($home['services'] ?? []),
            'projects' => (array) ($home['projects'] ?? []),
            'bestsellers' => $this->queryProductsByGroup('best', $bestCount, $demoFallback),
            'brandItems' => $this->resolveBrands(),
            'paletteSwatches' => (array) ($home['palette_swatches'] ?? []),
            'newestProducts' => $this->queryProductsByGroup('new', $newCount, $demoFallback),
            'blogPosts' => $this->queryLatestPosts($blogCount, $demoFallback),
            'wishlistIds' => ProductPresenter::currentUserWishlist(),
        ];

        if ($cacheEnabled) {
            set_transient('rs_frontpage_cache', $payload, 30 * MINUTE_IN_SECONDS);
        }

        return $payload;
    }

    /**
     * ۵ دسته اصلی ردیف دایره‌ای صفحه اول منطبق با index.html
     *
     * @return array<int, array{name: string, url: string, img: string, count_fa: string}>
     */
    private function resolveHomeCategories(): array
    {
        if (rs_is_wc() && taxonomy_exists('product_cat')) {
            $raw = Persian::toEn((string) rs_option('home.category_ids', ''));
            $ids = array_values(array_unique(array_filter(array_map('absint', preg_split('/[\s,،]+/u', trim($raw))))));
            if ($ids !== []) {
                $selected = [];
                foreach ($ids as $id) {
                    $term = get_term($id, 'product_cat');
                    if (!$term || is_wp_error($term)) continue;
                    $link = get_term_link($term);
                    if (is_wp_error($link)) continue;
                    $thumb = (int) get_term_meta($id, 'thumbnail_id', true);
                    $image = $thumb ? wp_get_attachment_image_url($thumb, 'medium') : false;
                    $selected[] = ['name' => $term->name, 'url' => (string) $link, 'img' => $image ?: rs_img((string) get_term_meta($id, '_rs_cat_image', true) ?: 'cat-building.webp'), 'count_fa' => rs_fa((int) $term->count) . ' کالا'];
                }
                return $selected;
            }
            $terms = get_terms([
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
                'parent' => 0,
                'number' => 5,
                'exclude' => [(int) get_option('default_product_cat', 0)],
            ]);
            if (is_array($terms) && count($terms) > 0) {
                $hasRealProducts = false;
                foreach ($terms as $t) {
                    if ($t->count > 0) {
                        $hasRealProducts = true;
                        break;
                    }
                }
                if ($hasRealProducts) {
                    $out = [];
                    foreach ($terms as $term) {
                        $thumbId = (int) get_term_meta($term->term_id, 'thumbnail_id', true);
                        $thumbUrl = $thumbId > 0 ? (string) wp_get_attachment_image_url($thumbId, 'medium') : '';
                        $metaImg = (string) get_term_meta($term->term_id, '_rs_cat_image', true);
                        $link = get_term_link($term);
                        $out[] = [
                            'name' => $term->name,
                            'url' => !is_wp_error($link) ? (string) $link : add_query_arg('cat', $term->slug, rs_page_url('shop')),
                            'img' => $thumbUrl !== '' ? $thumbUrl : rs_img($metaImg ?: 'cat-building.webp'),
                            'count_fa' => rs_fa((int) $term->count) . ' کالا',
                        ];
                    }
                    return $out;
                }
            }
        }

        $shopUrl = rs_page_url('shop');
        return [
            ['name' => 'رنگ ساختمانی', 'url' => add_query_arg('cat', 'building', $shopUrl), 'img' => rs_img('cat-building.webp'), 'count_fa' => '۱۸ کالا'],
            ['name' => 'رنگ هنری', 'url' => add_query_arg('cat', 'art', $shopUrl), 'img' => rs_img('cat-art.webp'), 'count_fa' => '۲۴ کالا'],
            ['name' => 'پتینه و تکسچر', 'url' => add_query_arg('cat', 'patina', $shopUrl), 'img' => rs_img('cat-patina.webp'), 'count_fa' => '۱۵ کالا'],
            ['name' => 'شابلون و تابلو', 'url' => add_query_arg('cat', 'stencil', $shopUrl), 'img' => rs_img('cat-stencil.webp'), 'count_fa' => '۳۱ کالا'],
            ['name' => 'ابزار و لوازم', 'url' => add_query_arg('cat', 'tools', $shopUrl), 'img' => rs_img('cat-tools.webp'), 'count_fa' => '۲۲ کالا'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function queryDeals(int $limit, string $source, string $categorySlug, bool $demoFallback): array
    {
        $items = [];

        if (rs_is_wc()) {
            $args = [
                'post_type' => 'product',
                'post_status' => 'publish',
                'posts_per_page' => $limit,
                'no_found_rows' => true,
            ];

            if ($source === 'featured') {
                $args['tax_query'] = [[
                    'taxonomy' => 'product_visibility',
                    'field' => 'name',
                    'terms' => 'featured',
                ]];
            } elseif ($source === 'category' && $categorySlug !== '') {
                $args['tax_query'] = [[
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => $categorySlug,
                ]];
            } else {
                $saleIds = function_exists('wc_get_product_ids_on_sale') ? wc_get_product_ids_on_sale() : [];
                if (!empty($saleIds)) {
                    $args['post__in'] = $saleIds;
                } else {
                    $args['meta_key'] = 'total_sales';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'DESC';
                }
            }

            $q = new WP_Query($args);
            foreach ($q->posts as $post) {
                $p = wc_get_product($post->ID);
                if ($p) {
                    $items[] = ProductPresenter::fromWcProduct($p);
                }
            }
        }

        if ($items === [] && $demoFallback) {
            $demos = array_values(array_filter(DemoCatalog::products(), static fn(array $d): bool => ($d['group'] ?? '') === 'sale'));
            foreach (array_slice($demos, 0, $limit) as $d) {
                $items[] = ProductPresenter::fromDemoArray($d);
            }
        }

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function queryProductsByGroup(string $group, int $limit, bool $demoFallback): array
    {
        $items = [];

        if (rs_is_wc()) {
            $args = [
                'post_type' => 'product',
                'post_status' => 'publish',
                'posts_per_page' => $limit,
                'no_found_rows' => true,
            ];
            if ($group === 'best') {
                $args['meta_key'] = 'total_sales';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
            } else {
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
            }

            $q = new WP_Query($args);
            foreach ($q->posts as $post) {
                $p = wc_get_product($post->ID);
                if ($p) {
                    $items[] = ProductPresenter::fromWcProduct($p);
                }
            }
        }

        if ($items === [] && $demoFallback) {
            $demos = array_values(array_filter(DemoCatalog::products(), static fn(array $d): bool => ($d['group'] ?? '') === $group));
            if ($demos === []) {
                $demos = DemoCatalog::products();
            }
            foreach (array_slice($demos, 0, $limit) as $d) {
                $items[] = ProductPresenter::fromDemoArray($d);
            }
        }

        return $items;
    }

    /**
     * @return array<int, array{title: string, image: string, url: string}>
     */
    private function resolveBrands(): array
    {
        $shopUrl = rs_page_url('shop');
        $out = [];
        foreach (DemoCatalog::brands() as $b) {
            $out[] = [
                'title' => $b['title'],
                'image' => rs_img($b['image']),
                'url' => add_query_arg('brand', $b['slug'], $shopUrl),
            ];
        }
        return $out;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function queryLatestPosts(int $limit, bool $demoFallback): array
    {
        $posts = [];
        $q = new WP_Query([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'no_found_rows' => true,
        ]);

        foreach ($q->posts as $post) {
            $cats = get_the_category($post->ID);
            $catName = !empty($cats[0]) ? $cats[0]->name : 'آموزش';
            $demoImg = (string) get_post_meta($post->ID, '_rs_demo_image', true);
            $thumb = get_the_post_thumbnail_url($post->ID, 'medium_large')
                ?: ($demoImg !== '' ? rs_img($demoImg) : rs_img('blog/patina.webp'));
            $readMeta = (string) get_post_meta($post->ID, '_rs_read_time', true);
            $words = str_word_count(wp_strip_all_tags($post->post_content));
            $readLabel = $readMeta !== '' ? $readMeta : (rs_fa(max(3, (int) ceil($words / 180))) . ' دقیقه مطالعه');

            $posts[] = [
                'title' => get_the_title($post),
                'url' => (string) get_permalink($post),
                'cat' => $catName,
                'image' => (string) $thumb,
                'date' => Persian::jalaliDate((int) get_post_time('U', true, $post)),
                'read' => $readLabel,
            ];
        }

        if ($posts === [] && $demoFallback) {
            foreach (array_slice(DemoCatalog::posts(), 0, $limit) as $dp) {
                $posts[] = [
                    'title' => $dp['title'],
                    'url' => rs_page_url('blog'),
                    'cat' => $dp['cat'],
                    'image' => rs_img($dp['image']),
                    'date' => $dp['date'],
                    'read' => $dp['read'],
                ];
            }
        }

        return $posts;
    }
}
