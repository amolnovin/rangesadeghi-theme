<?php

declare(strict_types=1);

namespace App\Support;

use WC_Product;
use WC_Product_Variable;

/**
 * تبدیل‌کننده یکپارچه محصول ووکامرس (WC_Product) یا محصول دمو به ساختار View-Model استاندارد قالب
 */
final class ProductPresenter
{
    /**
     * تبدیل WC_Product واقعی به آرایه View-Model منطبق با کارت محصول و تک‌صفحه محصول
     *
     * @return array<string, mixed>
     */
    public static function fromWcProduct(WC_Product $product): array
    {
        $id = $product->get_id();
        $regular = Persian::toToman($product->get_regular_price() ?: $product->get_price());
        $current = Persian::toToman($product->get_price());

        if ($product instanceof WC_Product_Variable) {
            $minRegular = Persian::toToman($product->get_variation_regular_price('min', true));
            $minCurrent = Persian::toToman($product->get_variation_price('min', true));
            if ($minCurrent > 0) {
                $current = $minCurrent;
                $regular = $minRegular > 0 ? $minRegular : $minCurrent;
            }
        }

        $old = ($product->is_on_sale() && $regular > $current) ? $regular : 0;
        $discount = ($old > 0 && $current > 0)
            ? (int) round((($old - $current) / $old) * 100)
            : 0;

        // تصویر اصلی + گالری
        $thumbId = (int) $product->get_image_id();
        $mainImage = $thumbId > 0
            ? (string) wp_get_attachment_image_url($thumbId, 'woocommerce_single')
            : (string) get_post_meta($id, '_rs_demo_image', true);
        if ($mainImage === '') {
            $mainImage = rs_img('sale-kardak.webp');
        } else {
            $mainImage = rs_img($mainImage);
        }

        $gallery = [$mainImage];
        foreach ($product->get_gallery_image_ids() as $gid) {
            $gUrl = wp_get_attachment_image_url((int) $gid, 'woocommerce_single');
            if ($gUrl) {
                $gallery[] = (string) $gUrl;
            }
        }
        $demoGallery = get_post_meta($id, '_rs_demo_gallery', true);
        if (count($gallery) === 1 && is_array($demoGallery)) {
            foreach ($demoGallery as $gItem) {
                $url = rs_img((string) $gItem);
                if (!in_array($url, $gallery, true)) {
                    $gallery[] = $url;
                }
            }
        }

        // دسته‌بندی‌ها
        $terms = get_the_terms($id, 'product_cat');
        $catNames = [];
        $catSlugs = [];
        $primaryCatUrl = rs_page_url('shop');
        if (is_array($terms)) {
            foreach ($terms as $idx => $term) {
                if ($term->slug === 'uncategorized') {
                    continue;
                }
                $catNames[] = $term->name;
                $catSlugs[] = urldecode((string) $term->slug);
                if ($idx === 0) {
                    $link = get_term_link($term);
                    if (!is_wp_error($link)) {
                        $primaryCatUrl = (string) $link;
                    }
                }
            }
        }

        // برند محصول (تاکسونومی product_brand یا ویژگی pa_brand یا متا)
        $brandTitle = '';
        $brandSlug = '';
        $brandUrl = rs_page_url('shop');
        foreach (['product_brand', 'pa_brand'] as $brandTax) {
            if (taxonomy_exists($brandTax)) {
                $bTerms = get_the_terms($id, $brandTax);
                if (is_array($bTerms) && !empty($bTerms[0])) {
                    $brandTitle = $bTerms[0]->name;
                    $brandSlug = urldecode((string) $bTerms[0]->slug);
                    $bLink = get_term_link($bTerms[0]);
                    if (!is_wp_error($bLink)) {
                        $brandUrl = (string) $bLink;
                    }
                    break;
                }
            }
        }
        if ($brandTitle === '') {
            $brandMeta = (string) get_post_meta($id, '_rs_brand_name', true);
            $brandSlug = (string) get_post_meta($id, '_rs_brand_slug', true);
            $brandTitle = $brandMeta !== '' ? $brandMeta : 'رنگ صادقی';
        }

        // موجودی و تازگی
        $inStock = $product->is_in_stock();
        $stockQty = $product->managing_stock() ? max(0, (int) $product->get_stock_quantity()) : ($inStock ? 20 : 0);
        $created = $product->get_date_created();
        $createdTs = $created ? $created->getTimestamp() : time();
        $newDays = max(1, (int) rs_option('shop.new_badge_days', 70));
        $lowThreshold = max(1, (int) rs_option('shop.low_stock_threshold', 5));

        $isNew = ((time() - $createdTs) < ($newDays * DAY_IN_SECONDS));
        $isLow = $inStock && $product->managing_stock() && $stockQty > 0 && $stockQty <= $lowThreshold;

        // جدول مشخصات فنی
        $specs = [];
        if ($product->has_weight()) {
            $specs[] = ['وزن', rs_fa((string) $product->get_weight()) . ' ' . get_option('woocommerce_weight_unit', 'kg')];
        }
        if ($product->has_dimensions()) {
            $specs[] = ['ابعاد', rs_fa(wc_format_dimensions($product->get_dimensions(false)))];
        }
        foreach ($product->get_attributes() as $attribute) {
            if (!$attribute->get_visible()) {
                continue;
            }
            $label = wc_attribute_label($attribute->get_name());
            $values = $attribute->is_taxonomy()
                ? wc_get_product_terms($id, $attribute->get_name(), ['fields' => 'names'])
                : $attribute->get_options();
            if (!empty($values)) {
                $specs[] = [$label, implode('، ', $values)];
            }
        }
        $demoSpecs = get_post_meta($id, '_rs_demo_specs', true);
        if ($specs === [] && is_array($demoSpecs)) {
            $specs = $demoSpecs;
        }

        return [
            'id' => (string) $id,
            'wc_id' => $id,
            'is_wc' => true,
            'type' => $product->get_type(),
            'title' => $product->get_name(),
            'url' => $product->get_permalink(),
            'cat' => $catNames !== [] ? implode('، ', array_slice($catNames, 0, 2)) : 'فروشگاه رنگ صادقی',
            'cat_url' => $primaryCatUrl,
            'cats' => $catSlugs,
            'brand' => $brandSlug,
            'brand_title' => $brandTitle,
            'brand_url' => $brandUrl,
            'price' => $current,
            'old' => $old,
            'discount' => $discount,
            'rating' => (float) ($product->get_average_rating() ?: 4.8),
            'reviews' => (int) $product->get_review_count(),
            'image' => $mainImage,
            'gallery' => $gallery,
            'sku' => $product->get_sku() ?: ('RS-' . (1000 + $id)),
            'in_stock' => $inStock,
            'stock' => $stockQty,
            'managing_stock' => $product->managing_stock(),
            'is_new' => $isNew,
            'is_low' => $isLow,
            'date' => gmdate('Y-m-d', $createdTs),
            'sold' => (int) $product->get_total_sales(),
            'short' => wp_strip_all_tags($product->get_short_description() ?: wp_trim_words($product->get_description(), 28)),
            'description' => apply_filters('the_content', $product->get_description() ?: $product->get_short_description()),
            'specs' => $specs,
        ];
    }

    /**
     * تبدیل آیتم آرایه دمو به همان ساختار View-Model یکسان
     *
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    public static function fromDemoArray(array $item): array
    {
        $brands = DemoCatalog::brands();
        $brandTitle = 'رنگ صادقی';
        foreach ($brands as $b) {
            if ($b['slug'] === ($item['brand'] ?? '')) {
                $brandTitle = $b['title'];
                break;
            }
        }

        $firstCat = $item['cats'][0] ?? '';
        $catUrl = rs_page_url('shop', $firstCat !== '' ? ['product_cat' => $firstCat] : []);
        $gallery = array_map(static fn(string $img): string => rs_img($img), $item['gallery'] ?? [$item['image']]);
        $stock = (int) ($item['stock'] ?? 15);
        $discount = (int) ($item['discount'] ?? 0);

        return [
            'id' => (string) $item['id'],
            'wc_id' => 0,
            'is_wc' => false,
            'type' => 'simple',
            'title' => (string) $item['title'],
            'url' => rs_page_url('shop', ['demo_product' => $item['id']]),
            'cat' => (string) ($item['cat'] ?? ''),
            'cat_url' => $catUrl,
            'cats' => (array) ($item['cats'] ?? []),
            'brand' => (string) ($item['brand'] ?? ''),
            'brand_title' => $brandTitle,
            'brand_url' => rs_page_url('shop', !empty($item['brand']) ? ['brand' => $item['brand']] : []),
            'price' => (int) ($item['price'] ?? 0),
            'old' => (int) ($item['old'] ?? 0),
            'discount' => $discount,
            'rating' => (float) ($item['rating'] ?? 4.5),
            'reviews' => (int) ($item['reviews'] ?? 0),
            'image' => rs_img((string) $item['image']),
            'gallery' => $gallery,
            'sku' => (string) ($item['sku'] ?? 'RS-1000'),
            'in_stock' => $stock > 0,
            'stock' => $stock,
            'managing_stock' => true,
            'is_new' => ($item['group'] ?? '') === 'new',
            'is_low' => $stock > 0 && $stock <= 5,
            'date' => (string) ($item['date'] ?? '2026-09-01'),
            'sold' => (int) ($item['sold'] ?? 0),
            'short' => (string) ($item['short'] ?? ''),
            'description' => '<p>' . esc_html((string) ($item['short'] ?? '')) . '</p>',
            'specs' => (array) ($item['specs'] ?? []),
        ];
    }

    /**
     * بررسی وضعیت علاقه‌مندی کاربر فعلی
     *
     * @return array<int, string>
     */
    public static function currentUserWishlist(): array
    {
        if (function_exists('is_user_logged_in') && is_user_logged_in()) {
            $saved = get_user_meta(get_current_user_id(), '_rs_wishlist', true);
            if (is_array($saved)) {
                return array_values(array_unique(array_map('strval', $saved)));
            }
        }

        if (!empty($_COOKIE['rs_wishlist'])) {
            $decoded = json_decode(stripslashes((string) $_COOKIE['rs_wishlist']), true);
            if (is_array($decoded)) {
                return array_values(array_unique(array_map('strval', $decoded)));
            }
        }

        return [];
    }
}
