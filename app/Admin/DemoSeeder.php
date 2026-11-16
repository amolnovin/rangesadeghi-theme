<?php

declare(strict_types=1);

namespace App\Admin;

use App\Support\DemoCatalog;
use App\Support\Persian;
use WC_Product_Simple;

/**
 * درون‌ریز یک‌کلیکی صفحات، دسته‌بندی‌ها، برندها، ۳۲ محصول نمونه ووکامرس و ۸ مقاله آموزشی
 * تا مدیر سایت بلافاصله پس از نصب قالب، کل فروشگاه را مشابه نسخه HTML ببیند و ویرایش کند.
 */
final class DemoSeeder
{
    /**
     * @return array{pages: int, categories: int, brands: int, products: int, posts: int}
     */
    public function seedAll(): array
    {
        $counts = [
            'pages' => $this->seedPages(),
            'categories' => 0,
            'brands' => 0,
            'products' => 0,
            'posts' => $this->seedPosts(),
        ];

        if (rs_is_wc()) {
            $catMap = $this->seedProductCategories();
            $brandMap = $this->seedProductBrands();
            $counts['categories'] = count($catMap);
            $counts['brands'] = count($brandMap);
            $counts['products'] = $this->seedProducts($catMap, $brandMap);
        }

        flush_rewrite_rules();
        delete_transient('rs_frontpage_cache');

        return $counts;
    }

    private function seedPages(): int
    {
        $pages = [
            'home' => ['title' => 'صفحه اصلی', 'template' => ''],
            'blog' => ['title' => 'وبلاگ آموزشی', 'template' => ''],
            'categories' => ['title' => 'دسته‌بندی محصولات', 'template' => 'template-categories.php'],
            'about' => ['title' => 'درباره رنگ صادقی', 'template' => 'template-about.php'],
            'contact' => ['title' => 'تماس با ما', 'template' => 'template-contact.php'],
            'stores' => ['title' => 'فروشگاه حضوری آمل', 'template' => 'template-stores.php'],
            'order-details' => ['title' => 'جزئیات و پیگیری سفارش', 'template' => 'template-order-tracking.php'],
        ];

        $created = 0;
        $homePageId = 0;
        $blogPageId = 0;

        foreach ($pages as $slug => $info) {
            $existing = get_page_by_path($slug);
            if ($existing) {
                $pageId = (int) $existing->ID;
            } else {
                $pageId = (int) wp_insert_post([
                    'post_title' => $info['title'],
                    'post_name' => $slug,
                    'post_status' => 'publish',
                    'post_type' => 'page',
                ]);
                if ($pageId > 0) {
                    $created++;
                }
            }

            if ($pageId > 0 && $info['template'] !== '') {
                update_post_meta($pageId, '_wp_page_template', $info['template']);
            }
            if ($slug === 'home') {
                $homePageId = $pageId;
            } elseif ($slug === 'blog') {
                $blogPageId = $pageId;
            }
        }

        if ($homePageId > 0 && get_option('show_on_front') !== 'page') {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $homePageId);
            if ($blogPageId > 0) {
                update_option('page_for_posts', $blogPageId);
            }
        }

        return $created;
    }

    /**
     * @return array<string, int>
     */
    private function seedProductCategories(): array
    {
        $map = [];
        foreach (DemoCatalog::categories() as $cat) {
            $term = get_term_by('slug', $cat['slug'], 'product_cat');
            if (!$term) {
                $inserted = wp_insert_term($cat['name'], 'product_cat', [
                    'slug' => $cat['slug'],
                    'description' => $cat['desc'],
                ]);
                $termId = (!is_wp_error($inserted) && isset($inserted['term_id'])) ? (int) $inserted['term_id'] : 0;
            } else {
                $termId = (int) $term->term_id;
            }

            if ($termId > 0) {
                update_term_meta($termId, '_rs_cat_image', $cat['img']);
                update_term_meta($termId, '_rs_cat_sub', $cat['sub']);
                $map[$cat['name']] = $termId;
            }
        }

        return $map;
    }

    /**
     * @return array<string, int>
     */
    private function seedProductBrands(): array
    {
        if (!taxonomy_exists('product_brand')) {
            return [];
        }

        $map = [];
        foreach (DemoCatalog::brands() as $brand) {
            $term = get_term_by('slug', $brand['slug'], 'product_brand');
            if (!$term) {
                $inserted = wp_insert_term($brand['title'], 'product_brand', ['slug' => $brand['slug']]);
                $termId = (!is_wp_error($inserted) && isset($inserted['term_id'])) ? (int) $inserted['term_id'] : 0;
            } else {
                $termId = (int) $term->term_id;
            }
            if ($termId > 0) {
                $map[$brand['slug']] = $termId;
            }
        }

        return $map;
    }

    /**
     * @param array<string, int> $catMap
     * @param array<string, int> $brandMap
     */
    private function seedProducts(array $catMap, array $brandMap): int
    {
        if (!class_exists(WC_Product_Simple::class)) {
            return 0;
        }

        $multiplier = (function_exists('get_woocommerce_currency') && get_woocommerce_currency() === 'IRR') ? 10 : 1;
        $brandTitles = [];
        foreach (DemoCatalog::brands() as $b) {
            $brandTitles[$b['slug']] = $b['title'];
        }

        $created = 0;
        foreach (DemoCatalog::products() as $item) {
            $sku = Persian::toEn((string) $item['sku']);
            $existingId = wc_get_product_id_by_sku($sku);
            if ($existingId > 0) {
                continue;
            }

            $product = new WC_Product_Simple();
            $product->set_name($item['title']);
            $product->set_status('publish');
            $product->set_sku($sku);

            $salePrice = (int) $item['price'] * $multiplier;
            $regularPrice = ((int) ($item['old'] ?? 0) > (int) $item['price'])
                ? ((int) $item['old'] * $multiplier)
                : $salePrice;

            $product->set_regular_price((string) $regularPrice);
            if ($regularPrice > $salePrice) {
                $product->set_sale_price((string) $salePrice);
            }

            $product->set_short_description($item['short']);
            $product->set_description('<p>' . esc_html($item['short']) . '</p><p>این محصول با تضمین اصالت و بسته‌بندی ایمن از فروشگاه رنگ صادقی آمل به سراسر کشور ارسال می‌شود.</p>');
            $product->set_manage_stock(true);
            $product->set_stock_quantity((int) $item['stock']);
            $product->set_stock_status(((int) $item['stock'] > 0) ? 'instock' : 'outofstock');

            if (!empty($catMap[$item['cat']])) {
                $product->set_category_ids([$catMap[$item['cat']]]);
            }

            $productId = $product->save();
            if ($productId > 0) {
                $created++;
                update_post_meta($productId, '_rs_demo_image', $item['image']);
                update_post_meta($productId, '_rs_demo_specs', $item['specs'] ?? []);
                update_post_meta($productId, '_rs_brand_slug', $item['brand']);
                update_post_meta($productId, '_rs_brand_name', $brandTitles[$item['brand']] ?? $item['brand']);
                update_post_meta($productId, '_wc_average_rating', (float) $item['rating']);
                update_post_meta($productId, '_wc_review_count', (int) $item['reviews']);
                update_post_meta($productId, 'total_sales', (int) $item['sold']);

                if (!empty($brandMap[$item['brand']]) && taxonomy_exists('product_brand')) {
                    wp_set_object_terms($productId, [$brandMap[$item['brand']]], 'product_brand');
                }
            }
        }

        return $created;
    }

    private function seedPosts(): int
    {
        $created = 0;
        foreach (DemoCatalog::posts() as $postItem) {
            $slug = sanitize_title($postItem['id']);
            $exists = get_page_by_path($slug, OBJECT, 'post');
            if ($exists) {
                continue;
            }

            $catTerm = term_exists($postItem['cat'], 'category');
            if (!$catTerm) {
                $catTerm = wp_insert_term($postItem['cat'], 'category');
            }
            $catId = is_array($catTerm) && isset($catTerm['term_id']) ? (int) $catTerm['term_id'] : 0;

            $postId = (int) wp_insert_post([
                'post_title' => $postItem['title'],
                'post_name' => $slug,
                'post_excerpt' => $postItem['excerpt'],
                'post_content' => '<p>' . esc_html($postItem['excerpt']) . '</p><h2>نکات کلیدی اجرا و آماده‌سازی سطح</h2><p>پیش از اجرای هرگونه رنگ یا متریال دکوراتیو، سطح کار باید کاملاً خشک، تمیز و عاری از چربی یا گردوغبار باشد. اجرای یک دست پرایمر مناسب، چسبندگی و دوام نهایی را چندین برابر می‌کند.</p><blockquote>پیشنهاد کارشناسان رنگ صادقی: همیشه پیش از اجرای کامل، متریال را روی بخش کوچکی از سطح تست کنید.</blockquote>',
                'post_status' => 'publish',
                'post_type' => 'post',
                'post_category' => $catId > 0 ? [$catId] : [],
            ]);

            if ($postId > 0) {
                $created++;
                update_post_meta($postId, '_rs_demo_image', $postItem['image']);
                update_post_meta($postId, '_rs_read_time', $postItem['read']);
            }
        }

        return $created;
    }
}
