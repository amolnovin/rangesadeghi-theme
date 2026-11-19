<?php
/** Create isolated products and an authenticated visitor for front-end search tests. */
// Fresh WooCommerce installs default to a coming-soon screen for guests.
update_option('woocommerce_coming_soon', 'no');
$ids = [];
foreach (['رنگ پاستیلی قرمز', 'رنگ پاستیلی آبی', 'رنگ پاستیلی سبز', 'رنگ پاستیلی بنفش', 'کالای یکتای جستجو'] as $title) {
    $product = new WC_Product_Simple();
    $product->set_name($title);
    $product->set_status('publish');
    $product->set_regular_price('100');
    $product->save();
    $ids[] = $product->get_id();
}
$variant = new WC_Product_Simple();
$variant->set_name('رنگ اكريليك ١٢٠'); $variant->set_regular_price('150'); $variant->set_status('publish'); $variant->set_stock_status('outofstock'); $variant->save();
for ($i = 0; $i < 9; $i++) {
    $bulk = new WC_Product_Simple(); $bulk->set_name('رنگ انبوه ' . $i); $bulk->set_status('publish'); $bulk->set_regular_price((string) (100 + $i)); $bulk->save();
}
$hidden = new WC_Product_Simple(); $hidden->set_name('رنگ انبوه محرمانه'); $hidden->set_status('publish'); $hidden->set_regular_price('50'); $hidden->set_catalog_visibility('hidden'); $hidden->save();
$catalogOnly = new WC_Product_Simple(); $catalogOnly->set_name('رنگ انبوه کاتالوگ'); $catalogOnly->set_status('publish'); $catalogOnly->set_regular_price('50'); $catalogOnly->set_catalog_visibility('catalog'); $catalogOnly->save();
$draft = new WC_Product_Simple(); $draft->set_name('رنگ انبوه پیش نویس'); $draft->set_status('draft'); $draft->set_regular_price('50'); $draft->save();
$postId = wp_insert_post(['post_title' => 'مقاله جستجوی آزمایشی', 'post_content' => 'محتوای مقاله', 'post_status' => 'publish', 'post_type' => 'post']);
rs_app('options')->update(['shop' => ['per_page' => 3, 'pagination_mode' => 'numbers']]);
$user = get_user_by('login', 'admin');
$cookie = wp_generate_auth_cookie($user->ID, time() + HOUR_IN_SECONDS, 'logged_in');
echo 'RSSEARCH:' . wp_json_encode(['cookie_name' => LOGGED_IN_COOKIE, 'cookie' => $cookie, 'ids' => $ids, 'post_id' => $postId, 'variant_id' => $variant->get_id(), 'hidden_id' => $hidden->get_id(), 'catalog_id' => $catalogOnly->get_id(), 'draft_id' => $draft->get_id()]) . "\n";
