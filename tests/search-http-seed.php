<?php
/** Create isolated products and an authenticated visitor for front-end search tests. */
$mu = WP_CONTENT_DIR . '/mu-plugins';
if (!is_dir($mu)) mkdir($mu);
file_put_contents($mu . '/rs-search-debug.php', "<?php add_filter('template_include', function (\$template) { header('X-Rs-Template: ' . basename(\$template)); return \$template; }, 9999);
");
$ids = [];
foreach (['رنگ پاستیلی قرمز', 'رنگ پاستیلی آبی', 'رنگ پاستیلی سبز', 'رنگ پاستیلی بنفش', 'کالای یکتای جستجو'] as $title) {
    $product = new WC_Product_Simple();
    $product->set_name($title);
    $product->set_status('publish');
    $product->set_regular_price('100');
    $product->save();
    $ids[] = $product->get_id();
}
$postId = wp_insert_post(['post_title' => 'مقاله جستجوی آزمایشی', 'post_content' => 'محتوای مقاله', 'post_status' => 'publish', 'post_type' => 'post']);
rs_app('options')->update(['shop' => ['per_page' => 3, 'pagination_mode' => 'numbers']]);
$user = get_user_by('login', 'admin');
$cookie = wp_generate_auth_cookie($user->ID, time() + HOUR_IN_SECONDS, 'logged_in');
echo 'RSSEARCH:' . wp_json_encode(['cookie_name' => LOGGED_IN_COOKIE, 'cookie' => $cookie, 'ids' => $ids, 'post_id' => $postId]) . "\n";
