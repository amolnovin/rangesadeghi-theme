<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Core\ViewComposer;
use App\Support\DemoCatalog;
use App\Support\Persian;
use App\Support\ProductPresenter;
use WC_Product_Variable;

final class SingleProductComposer extends ViewComposer
{
    public static function views(): array
    {
        return ['woocommerce.single-product'];
    }

    public function with(array $data): array
    {
        $productCfg = (array) rs_option('product', []);
        $vm = null;
        $variations = [];
        $attributes = [];
        $reviews = [];
        $related = [];

        if (rs_is_wc() && function_exists('is_product') && is_product()) {
            $wcProduct = wc_get_product(get_queried_object_id());
            if ($wcProduct) {
                $vm = ProductPresenter::fromWcProduct($wcProduct);

                if ($wcProduct instanceof WC_Product_Variable) {
                    $attributes = $wcProduct->get_variation_attributes();
                    foreach ($wcProduct->get_available_variations() as $var) {
                        $variations[] = [
                            'variation_id' => $var['variation_id'],
                            'attributes' => $var['attributes'],
                            'price' => Persian::toToman($var['display_price'] ?? 0),
                            'price_fa' => rs_price(Persian::toToman($var['display_price'] ?? 0)),
                            'regular' => Persian::toToman($var['display_regular_price'] ?? 0),
                            'is_in_stock' => (bool) ($var['is_in_stock'] ?? true),
                            'image' => $var['image']['src'] ?? $vm['image'],
                            'sku' => $var['sku'] ?? $vm['sku'],
                        ];
                    }
                }

                // دیدگاه‌های واقعی محصول در ووکامرس
                $wpComments = get_comments([
                    'post_id' => $wcProduct->get_id(),
                    'status' => 'approve',
                    'number' => 20,
                ]);
                foreach ($wpComments as $c) {
                    $rate = (int) get_comment_meta((int) $c->comment_ID, 'rating', true);
                    $verified = function_exists('wc_review_is_from_verified_owner') && wc_review_is_from_verified_owner((int) $c->comment_ID);
                    $reviews[] = [
                        'name' => $c->comment_author ?: 'خریدار رنگ صادقی',
                        'date' => Persian::jalaliDate(strtotime($c->comment_date_gmt ?: 'now')),
                        'rating' => max(1, min(5, $rate ?: 5)),
                        'verified' => $verified,
                        'text' => wp_strip_all_tags($c->comment_content),
                        'images' => array_values(array_filter(array_map(static fn($id) => wp_get_attachment_image_url((int) $id, 'medium'), (array) get_comment_meta((int) $c->comment_ID, '_rs_feedback_images', true)))),
                    ];
                }

                // محصولات مرتبط
                $relCount = max(2, (int) ($productCfg['related_count'] ?? 5));
                $relIds = wc_get_related_products($wcProduct->get_id(), $relCount);
                foreach ($relIds as $rid) {
                    $rp = wc_get_product($rid);
                    if ($rp) {
                        $related[] = ProductPresenter::fromWcProduct($rp);
                    }
                }
            }
        }

        // فالبک نمایش کاتالوگ دمو در صورت مشاهده از لینک ?demo_product=...
        if ($vm === null) {
            $demoId = sanitize_text_field((string) ($_GET['demo_product'] ?? 'p1'));
            $demos = DemoCatalog::products();
            $found = $demos[0];
            foreach ($demos as $d) {
                if ($d['id'] === $demoId) {
                    $found = $d;
                    break;
                }
            }
            $vm = ProductPresenter::fromDemoArray($found);
            foreach ($demos as $d) {
                if ($d['cat'] === $found['cat'] && $d['id'] !== $found['id']) {
                    $related[] = ProductPresenter::fromDemoArray($d);
                    if (count($related) >= (int) ($productCfg['related_count'] ?? 5)) {
                        break;
                    }
                }
            }
        }

        return [
            'productCfg' => $productCfg,
            'product' => $vm,
            'attributes' => $attributes,
            'variations' => $variations,
            'productConsent' => rs_is_wc() && !empty($vm['wc_id']) ? $this->app->make(\App\Providers\WooCommerceServiceProvider::class)->productConsentText((int) $vm['wc_id']) : '',
            'reviews' => $reviews,
            'relatedProducts' => $related,
            'wishlistIds' => ProductPresenter::currentUserWishlist(),
            'isWished' => in_array((string) $vm['id'], ProductPresenter::currentUserWishlist(), true),
        ];
    }
}
