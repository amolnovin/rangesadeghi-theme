<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\ServiceProvider;
use App\Support\Persian;
use App\Support\ProductPresenter;
use WP_Post;

/**
 * موتور کامل سئو پیشرفته + متاباکس سئو اختصاصی + مولد استاندارد اسکیماهای JSON-LD (Schema.org)
 */
final class SeoSchemaServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // متاباکس سئو روی نوشته‌ها، برگه‌ها و محصولات ووکامرس
        add_action('add_meta_boxes', [$this, 'registerSeoMetaBoxes']);
        add_action('save_post', [$this, 'saveSeoMetaBox'], 10, 2);

        if (!(bool) rs_option('seo.enabled', true) && !(bool) rs_option('schema.enabled', true)) {
            return;
        }

        add_filter('document_title_separator', [$this, 'filterTitleSeparator'], 20);
        add_filter('document_title_parts', [$this, 'filterTitleParts'], 20);
        add_action('wp_head', [$this, 'renderSeoTagsAndSchemas'], 2);
        add_action('wp_footer', [$this, 'renderCustomFooterScripts'], 99);
    }

    /**
     * تشخیص وجود افزونه‌های سئو شخص ثالث برای همزیستی بدون تداخل متاتگ‌ها
     */
    public function hasExternalSeoPlugin(): bool
    {
        if (!(bool) rs_option('seo.coexist_with_plugins', true)) {
            return false;
        }

        return defined('WPSEO_VERSION')
            || defined('RANK_MATH_VERSION')
            || defined('SEOPRESS_VERSION')
            || defined('AIOSEO_VERSION');
    }

    public function filterTitleSeparator(string $sep): string
    {
        if ($this->hasExternalSeoPlugin()) {
            return $sep;
        }
        return (string) rs_option('seo.title_separator', '|') ?: '|';
    }

    /**
     * @param array<string, string> $parts
     * @return array<string, string>
     */
    public function filterTitleParts(array $parts): array
    {
        if ($this->hasExternalSeoPlugin()) {
            return $parts;
        }

        if (is_front_page()) {
            $customHome = trim((string) rs_option('seo.home_title', ''));
            if ($customHome !== '') {
                return ['title' => $customHome];
            }
        }

        if (is_singular()) {
            $postId = (int) get_queried_object_id();
            $metaTitle = trim((string) get_post_meta($postId, '_rs_seo_title', true));
            if ($metaTitle !== '') {
                $parts['title'] = $metaTitle;
            }
        }

        return $parts;
    }

    public function renderSeoTagsAndSchemas(): void
    {
        $externalSeo = $this->hasExternalSeoPlugin();

        if ((bool) rs_option('seo.enabled', true) && !$externalSeo) {
            $this->outputMetaTags();
        }

        // کدهای تأیید وبمسترها و اسکریپت‌های هدر همیشه طبق تنظیمات چاپ شوند
        $this->outputVerificationAndHeadScripts();

        if ((bool) rs_option('schema.enabled', true) && !$externalSeo) {
            $this->outputJsonLdSchemas();
        }
    }

    public function renderCustomFooterScripts(): void
    {
        $scripts = trim((string) rs_option('seo.footer_scripts', ''));
        if ($scripts !== '') {
            echo "\n" . $scripts . "\n";
        }
    }

    private function outputMetaTags(): void
    {
        $description = $this->resolveDescription();
        $canonical = $this->resolveCanonicalUrl();
        $robots = $this->resolveRobotsDirective();
        $ogTitle = wp_get_document_title();
        $ogImage = $this->resolveShareImage();
        $ogType = (rs_is_wc() && function_exists('is_product') && is_product())
            ? 'product'
            : (is_singular('post') ? 'article' : 'website');

        if ($description !== '') {
            echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
        }

        if ($robots !== '') {
            echo '<meta name="robots" content="' . esc_attr($robots) . '">' . "\n";
        }

        if ((bool) rs_option('seo.canonical_enabled', true) && $canonical !== '') {
            remove_action('wp_head', 'rel_canonical');
            echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";
        }

        if ((bool) rs_option('seo.opengraph_enabled', true)) {
            echo '<meta property="og:locale" content="fa_IR">' . "\n";
            echo '<meta property="og:type" content="' . esc_attr($ogType) . '">' . "\n";
            echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
            echo '<meta property="og:title" content="' . esc_attr($ogTitle) . '">' . "\n";
            if ($description !== '') {
                echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
            }
            if ($canonical !== '') {
                echo '<meta property="og:url" content="' . esc_url($canonical) . '">' . "\n";
            }
            if ($ogImage !== '') {
                echo '<meta property="og:image" content="' . esc_url($ogImage) . '">' . "\n";
            }

            if ($ogType === 'product' && rs_is_wc()) {
                $product = wc_get_product(get_queried_object_id());
                if ($product) {
                    $priceIrr = Persian::toRial($product->get_price());
                    echo '<meta property="product:price:amount" content="' . esc_attr((string) $priceIrr) . '">' . "\n";
                    echo '<meta property="product:price:currency" content="IRR">' . "\n";
                    echo '<meta property="product:availability" content="' . ($product->is_in_stock() ? 'in stock' : 'out of stock') . '">' . "\n";
                }
            }
        }

        if ((bool) rs_option('seo.twitter_card_enabled', true)) {
            echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
            echo '<meta name="twitter:title" content="' . esc_attr($ogTitle) . '">' . "\n";
            if ($description !== '') {
                echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
            }
            if ($ogImage !== '') {
                echo '<meta name="twitter:image" content="' . esc_url($ogImage) . '">' . "\n";
            }
        }
    }

    private function outputVerificationAndHeadScripts(): void
    {
        $google = trim((string) rs_option('seo.google_verification', ''));
        $bing = trim((string) rs_option('seo.bing_verification', ''));
        $yandex = trim((string) rs_option('seo.yandex_verification', ''));

        if ($google !== '') {
            echo '<meta name="google-site-verification" content="' . esc_attr($google) . '">' . "\n";
        }
        if ($bing !== '') {
            echo '<meta name="msvalidate.01" content="' . esc_attr($bing) . '">' . "\n";
        }
        if ($yandex !== '') {
            echo '<meta name="yandex-verification" content="' . esc_attr($yandex) . '">' . "\n";
        }

        $headScripts = trim((string) rs_option('seo.head_scripts', ''));
        if ($headScripts !== '') {
            echo $headScripts . "\n";
        }
    }

    private function outputJsonLdSchemas(): void
    {
        $graph = [];

        // ۱) اسکیمای سازمان و فروشگاه حضوری (Organization + PaintStore/LocalBusiness)
        if ((bool) rs_option('schema.organization_enabled', true) || (bool) rs_option('schema.local_business_enabled', true)) {
            $graph[] = $this->buildBusinessSchema();
        }

        // ۲) اسکیمای وب‌سایت و جعبه جستجو (WebSite + SearchAction)
        if ((bool) rs_option('schema.website_search_enabled', true)) {
            $graph[] = [
                '@type' => 'WebSite',
                '@id' => home_url('/#website'),
                'url' => home_url('/'),
                'name' => (string) rs_option('schema.business_name', get_bloginfo('name')),
                'inLanguage' => 'fa-IR',
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => [
                        '@type' => 'EntryPoint',
                        'urlTemplate' => home_url('/?s={search_term_string}&post_type=product'),
                    ],
                    'query-input' => 'required name=search_term_string',
                ],
            ];
        }

        // ۳) مسیر صفحه (BreadcrumbList) در صفحات داخلی
        if (!is_front_page() && (bool) rs_option('schema.breadcrumb_enabled', true)) {
            $breadcrumb = $this->buildBreadcrumbSchema();
            if ($breadcrumb !== null) {
                $graph[] = $breadcrumb;
            }
        }

        // ۴) اسکیمای غنی محصول ووکامرس (Product + Offer + MerchantReturnPolicy + AggregateRating + Review)
        if (rs_is_wc() && function_exists('is_product') && is_product() && (bool) rs_option('schema.product_schema_enabled', true)) {
            $productSchema = $this->buildProductSchema();
            if ($productSchema !== null) {
                $graph[] = $productSchema;
            }
        }

        // ۵) اسکیمای مقاله مجله (BlogPosting)
        if (is_singular('post') && (bool) rs_option('schema.article_schema_enabled', true)) {
            $articleSchema = $this->buildArticleSchema();
            if ($articleSchema !== null) {
                $graph[] = $articleSchema;
            }
        }

        // ۶) اسکیمای سوالات پرتکرار (FAQPage) در صفحه تماس با ما یا برگه‌های دارای FAQ
        if ((bool) rs_option('schema.faq_schema_enabled', true)) {
            $faqSchema = $this->buildFaqSchema();
            if ($faqSchema !== null) {
                $graph[] = $faqSchema;
            }
        }

        // ۷) اسکیمای لیست محصولات آرشیو (ItemList)
        if (rs_is_wc() && (is_shop() || is_product_taxonomy()) && (bool) rs_option('schema.itemlist_schema_enabled', true)) {
            $listSchema = $this->buildArchiveItemListSchema();
            if ($listSchema !== null) {
                $graph[] = $listSchema;
            }
        }

        if ($graph === []) {
            return;
        }

        $payload = [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];

        echo '<script type="application/ld+json">' . wp_json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
    }

    /**
     * @return array<string, mixed>
     */
    private function buildBusinessSchema(): array
    {
        $isLocal = (bool) rs_option('schema.local_business_enabled', true);
        $type = $isLocal ? (string) rs_option('schema.business_type', 'HomeAndConstructionBusiness') : 'Organization';
        $socials = array_values(array_filter([
            (string) rs_option('general.instagram', ''),
            (string) rs_option('general.whatsapp', ''),
            (string) rs_option('general.telegram', ''),
        ]));

        $schema = [
            '@type' => $type,
            '@id' => home_url('/#organization'),
            'name' => (string) rs_option('schema.business_name', 'فروشگاه رنگ صادقی'),
            'url' => home_url('/'),
            'logo' => rs_img((string) rs_option('general.logo_desktop', 'assets/images/logo.png')),
            'image' => rs_img((string) rs_option('seo.default_og_image', 'assets/images/hero-pottery.webp')),
            'telephone' => '+98' . ltrim(Persian::toEn((string) rs_option('general.phone', '09113294688')), '0+'),
            'email' => (string) rs_option('general.email', 'rangsadeghi@gmail.com'),
            'sameAs' => $socials,
        ];

        if ($isLocal) {
            $days = array_values(array_filter(array_map('trim', explode(',', (string) rs_option('schema.opening_days', 'Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday')))));
            $schema['priceRange'] = (string) rs_option('schema.price_range', '$$');
            $schema['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => (string) rs_option('general.address', ''),
                'addressLocality' => 'آمل',
                'addressRegion' => 'مازندران',
                'postalCode' => Persian::toEn((string) rs_option('schema.postal_code', '4614853721')),
                'addressCountry' => 'IR',
            ];
            $lat = (string) rs_option('schema.geo_lat', '36.4696');
            $lng = (string) rs_option('schema.geo_lng', '52.3507');
            if ($lat !== '' && $lng !== '') {
                $schema['geo'] = [
                    '@type' => 'GeoCoordinates',
                    'latitude' => (float) $lat,
                    'longitude' => (float) $lng,
                ];
            }
            $schema['openingHoursSpecification'] = [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => $days,
                'opens' => (string) rs_option('schema.opens', '06:30'),
                'closes' => (string) rs_option('schema.closes', '20:30'),
            ];
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildBreadcrumbSchema(): ?array
    {
        $items = [
            ['name' => 'خانه', 'url' => home_url('/')],
        ];

        if (rs_is_wc() && function_exists('is_product') && is_product()) {
            $items[] = ['name' => 'فروشگاه', 'url' => rs_page_url('shop')];
            $terms = get_the_terms(get_queried_object_id(), 'product_cat');
            if (is_array($terms) && !empty($terms[0])) {
                $tLink = get_term_link($terms[0]);
                if (!is_wp_error($tLink)) {
                    $items[] = ['name' => $terms[0]->name, 'url' => (string) $tLink];
                }
            }
            $items[] = ['name' => get_the_title(), 'url' => (string) get_permalink()];
        } elseif (rs_is_wc() && function_exists('is_shop') && is_shop()) {
            $items[] = ['name' => 'فروشگاه', 'url' => rs_page_url('shop')];
        } elseif (rs_is_wc() && function_exists('is_product_taxonomy') && is_product_taxonomy()) {
            $term = get_queried_object();
            $items[] = ['name' => 'فروشگاه', 'url' => rs_page_url('shop')];
            if ($term && isset($term->name)) {
                $tLink = get_term_link($term);
                $items[] = ['name' => $term->name, 'url' => !is_wp_error($tLink) ? (string) $tLink : rs_page_url('shop')];
            }
        } elseif (is_singular('post')) {
            $items[] = ['name' => 'وبلاگ', 'url' => rs_page_url('blog')];
            $items[] = ['name' => get_the_title(), 'url' => (string) get_permalink()];
        } elseif (is_home() || is_category() || is_tag()) {
            $items[] = ['name' => 'وبلاگ', 'url' => rs_page_url('blog')];
        } elseif (is_singular('page')) {
            $items[] = ['name' => get_the_title(), 'url' => (string) get_permalink()];
        }

        if (count($items) < 2) {
            return null;
        }

        $listElements = [];
        foreach ($items as $index => $crumb) {
            $listElements[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $crumb['name'],
                'item' => $crumb['url'],
            ];
        }

        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => $listElements,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildProductSchema(): ?array
    {
        $product = wc_get_product(get_queried_object_id());
        if (!$product) {
            return null;
        }

        $vm = ProductPresenter::fromWcProduct($product);
        $useIrr = (bool) rs_option('schema.product_currency_irr', true);
        $priceValue = $useIrr ? Persian::toRial($product->get_price()) : $vm['price'];
        $currency = $useIrr ? 'IRR' : 'IRT';
        $returnDays = max(1, (int) rs_option('schema.return_policy_days', 7));

        $schema = [
            '@type' => 'Product',
            '@id' => $vm['url'] . '#product',
            'name' => $vm['title'],
            'image' => $vm['gallery'],
            'description' => $vm['short'] !== '' ? $vm['short'] : wp_strip_all_tags((string) $vm['description']),
            'sku' => Persian::toEn((string) $vm['sku']),
            'brand' => [
                '@type' => 'Brand',
                'name' => $vm['brand_title'],
            ],
            'offers' => [
                '@type' => 'Offer',
                'url' => $vm['url'],
                'priceCurrency' => $currency,
                'price' => (string) $priceValue,
                'priceValidUntil' => gmdate('Y-m-d', time() + 90 * DAY_IN_SECONDS),
                'itemCondition' => 'https://schema.org/NewCondition',
                'availability' => $vm['in_stock'] ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'seller' => ['@id' => home_url('/#organization')],
                'hasMerchantReturnPolicy' => [
                    '@type' => 'MerchantReturnPolicy',
                    'applicableCountry' => 'IR',
                    'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
                    'merchantReturnDays' => $returnDays,
                    'returnMethod' => 'https://schema.org/ReturnByMail',
                ],
            ],
        ];

        $reviewCount = (int) $product->get_review_count();
        $avgRating = (float) $product->get_average_rating();
        if ($reviewCount > 0 && $avgRating > 0) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => round($avgRating, 2),
                'reviewCount' => $reviewCount,
                'bestRating' => 5,
                'worstRating' => 1,
            ];

            $comments = get_comments([
                'post_id' => $product->get_id(),
                'status' => 'approve',
                'type' => 'review',
                'number' => 5,
            ]);
            $reviews = [];
            foreach ($comments as $comment) {
                $rate = (int) get_comment_meta((int) $comment->comment_ID, 'rating', true);
                $reviews[] = [
                    '@type' => 'Review',
                    'author' => ['@type' => 'Person', 'name' => $comment->comment_author ?: 'خریدار'],
                    'datePublished' => gmdate('Y-m-d', strtotime($comment->comment_date_gmt ?: 'now')),
                    'reviewBody' => wp_strip_all_tags($comment->comment_content),
                    'reviewRating' => [
                        '@type' => 'Rating',
                        'ratingValue' => max(1, min(5, $rate ?: 5)),
                        'bestRating' => 5,
                    ],
                ];
            }
            if ($reviews !== []) {
                $schema['review'] = $reviews;
            }
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildArticleSchema(): ?array
    {
        $post = get_post(get_queried_object_id());
        if (!$post instanceof WP_Post) {
            return null;
        }

        $thumb = get_the_post_thumbnail_url($post, 'full') ?: rs_img('blog/patina.webp');
        return [
            '@type' => 'BlogPosting',
            '@id' => get_permalink($post) . '#article',
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => get_permalink($post)],
            'headline' => get_the_title($post),
            'description' => $this->resolveDescription(),
            'image' => [$thumb],
            'datePublished' => get_the_date('c', $post),
            'dateModified' => get_the_modified_date('c', $post),
            'inLanguage' => 'fa-IR',
            'author' => [
                '@type' => 'Person',
                'name' => get_the_author_meta('display_name', (int) $post->post_author) ?: 'تیم محتوای رنگ صادقی',
            ],
            'publisher' => ['@id' => home_url('/#organization')],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildFaqSchema(): ?array
    {
        $faqs = [];

        if (is_page_template('template-contact.php') || is_page('contact')) {
            $faqs = [
                ['q' => 'آیا امکان خرید حضوری از فروشگاه وجود دارد؟', 'a' => 'بله؛ فروشگاه رنگ صادقی در آمل، خیابان طالب آملی، نبش دریا ۳۰/۱ از شنبه تا پنج‌شنبه بین ساعت ۶:۳۰ تا ۲۰:۳۰ پذیرای شماست.'],
                ['q' => 'هزینه و زمان ارسال چقدر است؟', 'a' => 'ارسال به سراسر کشور با پست پیشتاز و باربری انجام می‌شود؛ زمان تحویل بین ۲ تا ۵ روز کاری است. برای سفارش‌های بالای ۱۰ میلیون تومان ارسال رایگان است.'],
                ['q' => 'برای انتخاب رنگ یا متریال مشاوره می‌دهید؟', 'a' => 'بله؛ کارشناسان ما بر اساس نوع سطح، متراژ و شرایط اجرا، متریال و مقدار مورد نیاز را پیشنهاد می‌دهند.'],
                ['q' => 'امکان مرجوعی کالا وجود دارد؟', 'a' => 'کالاهای سالم و پلمب‌نشده تا ۷ روز پس از تحویل قابل بازگشت هستند.'],
            ];
        } elseif (is_singular()) {
            $customFaq = get_post_meta((int) get_queried_object_id(), '_rs_faq_items', true);
            if (is_array($customFaq)) {
                $faqs = $customFaq;
            }
        }

        if ($faqs === []) {
            return null;
        }

        $entities = [];
        foreach ($faqs as $row) {
            if (empty($row['q']) || empty($row['a'])) {
                continue;
            }
            $entities[] = [
                '@type' => 'Question',
                'name' => $row['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $row['a'],
                ],
            ];
        }

        return $entities !== [] ? ['@type' => 'FAQPage', 'mainEntity' => $entities] : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildArchiveItemListSchema(): ?array
    {
        global $wp_query;
        if (empty($wp_query->posts)) {
            return null;
        }

        $elements = [];
        foreach (array_slice($wp_query->posts, 0, 12) as $idx => $post) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $idx + 1,
                'url' => get_permalink($post->ID),
                'name' => get_the_title($post->ID),
            ];
        }

        return $elements !== [] ? ['@type' => 'ItemList', 'itemListElement' => $elements] : null;
    }

    private function resolveDescription(): string
    {
        if (is_front_page()) {
            return (string) rs_option('seo.home_description', '');
        }

        if (is_singular()) {
            $postId = (int) get_queried_object_id();
            $custom = trim((string) get_post_meta($postId, '_rs_seo_desc', true));
            if ($custom !== '') {
                return $custom;
            }
            $excerpt = wp_strip_all_tags(get_the_excerpt($postId));
            if ($excerpt !== '') {
                return mb_substr($excerpt, 0, 165);
            }
        }

        if (rs_is_wc() && function_exists('is_shop') && is_shop()) {
            return (string) rs_option('seo.shop_description', '');
        }

        if (is_category() || is_tag() || is_tax()) {
            $desc = wp_strip_all_tags(term_description());
            if ($desc !== '') {
                return mb_substr($desc, 0, 165);
            }
        }

        return (string) (get_bloginfo('description') ?: rs_option('seo.home_description', ''));
    }

    private function resolveCanonicalUrl(): string
    {
        if (is_singular()) {
            $postId = (int) get_queried_object_id();
            $custom = trim((string) get_post_meta($postId, '_rs_seo_canonical', true));
            if ($custom !== '') {
                return $custom;
            }
            return (string) get_permalink($postId);
        }

        if (is_front_page()) {
            return home_url('/');
        }

        if (rs_is_wc() && function_exists('is_shop') && is_shop()) {
            return rs_page_url('shop');
        }

        if (is_category() || is_tag() || is_tax()) {
            $term = get_queried_object();
            if ($term) {
                $link = get_term_link($term);
                if (!is_wp_error($link)) {
                    return (string) $link;
                }
            }
        }

        global $wp;
        return isset($wp->request) ? home_url(add_query_arg([], $wp->request)) : home_url('/');
    }

    private function resolveRobotsDirective(): string
    {
        if (is_singular()) {
            $noindex = get_post_meta((int) get_queried_object_id(), '_rs_seo_noindex', true);
            if ($noindex === '1') {
                return 'noindex, nofollow';
            }
        }

        if (is_search() && (bool) rs_option('seo.noindex_search', true)) {
            return 'noindex, follow';
        }

        if (rs_is_wc() && (bool) rs_option('seo.noindex_cart_checkout', true)) {
            if ((function_exists('is_cart') && is_cart()) || (function_exists('is_checkout') && is_checkout()) || (function_exists('is_account_page') && is_account_page())) {
                return 'noindex, nofollow';
            }
        }

        if (is_paged() && (bool) rs_option('seo.noindex_paged_archives', false)) {
            return 'noindex, follow';
        }

        return 'index, follow, max-image-preview:large';
    }

    private function resolveShareImage(): string
    {
        if (is_singular() && has_post_thumbnail()) {
            $img = get_the_post_thumbnail_url(null, 'full');
            if ($img) {
                return (string) $img;
            }
        }

        return rs_img((string) rs_option('seo.default_og_image', 'assets/images/hero-pottery.webp'));
    }

    /* ---------------- متاباکس سئوی اختصاصی در ویرایشگر وردپرس ---------------- */
    public function registerSeoMetaBoxes(): void
    {
        foreach (['post', 'page', 'product'] as $postType) {
            if (post_type_exists($postType)) {
                add_meta_box(
                    'rs-seo-metabox',
                    'تنظیمات سئو و اسکیما (رنگ صادقی)',
                    [$this, 'renderSeoMetaBoxHtml'],
                    $postType,
                    'normal',
                    'high'
                );
            }
        }
    }

    public function renderSeoMetaBoxHtml(WP_Post $post): void
    {
        wp_nonce_field('rs_save_seo_meta', 'rs_seo_meta_nonce');
        $title = (string) get_post_meta($post->ID, '_rs_seo_title', true);
        $desc = (string) get_post_meta($post->ID, '_rs_seo_desc', true);
        $canonical = (string) get_post_meta($post->ID, '_rs_seo_canonical', true);
        $noindex = (string) get_post_meta($post->ID, '_rs_seo_noindex', true);
        ?>
        <div style="direction:rtl;text-align:right;display:grid;gap:12px;padding:6px 0">
            <div>
                <label style="display:block;font-weight:600;margin-bottom:4px">عنوان سئو اختصاصی (Title Tag)</label>
                <input type="text" name="rs_seo_title" value="<?php echo esc_attr($title); ?>" style="width:100%" placeholder="خالی بگذارید تا از عنوان صفحه استفاده شود">
            </div>
            <div>
                <label style="display:block;font-weight:600;margin-bottom:4px">توضیحات متا (Meta Description — پیشنهاد: ۱۲۰ تا ۱۶۰ کاراکتر)</label>
                <textarea name="rs_seo_desc" rows="3" style="width:100%" placeholder="خلاصه جذاب برای نمایش در نتایج گوگل…"><?php echo esc_textarea($desc); ?></textarea>
            </div>
            <div>
                <label style="display:block;font-weight:600;margin-bottom:4px">آدرس کانونیکال سفارشی (Canonical URL)</label>
                <input type="url" name="rs_seo_canonical" value="<?php echo esc_attr($canonical); ?>" dir="ltr" style="width:100%" placeholder="https://...">
            </div>
            <label style="display:inline-flex;align-items:center;gap:8px">
                <input type="checkbox" name="rs_seo_noindex" value="1" <?php checked($noindex, '1'); ?>>
                <span>عدم ایندکس این صفحه در موتورهای جستجو (<code>noindex, nofollow</code>)</span>
            </label>
        </div>
        <?php
    }

    public function saveSeoMetaBox(int $postId, WP_Post $post): void
    {
        if (!isset($_POST['rs_seo_meta_nonce']) || !wp_verify_nonce((string) $_POST['rs_seo_meta_nonce'], 'rs_save_seo_meta')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $postId)) {
            return;
        }

        update_post_meta($postId, '_rs_seo_title', sanitize_text_field((string) wp_unslash($_POST['rs_seo_title'] ?? '')));
        update_post_meta($postId, '_rs_seo_desc', sanitize_textarea_field((string) wp_unslash($_POST['rs_seo_desc'] ?? '')));
        update_post_meta($postId, '_rs_seo_canonical', esc_url_raw((string) wp_unslash($_POST['rs_seo_canonical'] ?? '')));
        update_post_meta($postId, '_rs_seo_noindex', !empty($_POST['rs_seo_noindex']) ? '1' : '0');
    }
}
