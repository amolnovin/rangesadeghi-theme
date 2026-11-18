<?php

declare(strict_types=1);

/**
 * مقادیر پیش‌فرض تمامی بخش‌های پنل تنظیمات حرفه‌ای قالب رنگ صادقی
 * دقیقاً منطبق با ۱۰۰٪ جزئیات بصری و متنی قالب HTML مرجع.
 */
return [
    /* ======================================================================
     * ۱. عمومی، هدر، فوتر و تماس
     * ====================================================================== */
    'general' => [
        'logo_desktop' => 'assets/images/logo.png',
        'logo_mobile' => 'assets/images/logo-alt.webp',
        'favicon' => 'assets/images/logo-alt.webp',
        'brand_color' => '#df3639',
        'brand_color_dark' => '#b92427',
        'gold_color' => '#b08948',
        'persian_digits' => true,

        // هدر و نوار خدمات بالا
        'utility_bar_enabled' => true,
        'free_shipping_note' => 'ارسال رایگان سفارش‌های بالای ۱۰ میلیون تومان',
        'sticky_header_enabled' => true,
        'deal_nav_enabled' => true,
        'deal_nav_text' => 'تخفیف‌های امروز',
        'deal_nav_url' => '',
        'mobile_toolbar_enabled' => true,
        'search_placeholder' => 'جستجو در رنگ، پتینه، ابزار و شابلون…',
        'popular_searches' => 'مولتی سورفیس ریچ,تار عنکبوتی ساندورا,اکریلیک سوداکو,تکسچر کارن',

        // باکس پیشنهاد ویژه مگامنو
        'mega_highlight_tag' => 'پیشنهاد هفته',
        'mega_highlight_title' => 'تا ۳۵٪ تخفیف روی رنگ‌های ساختمانی',
        'mega_highlight_btn' => 'مشاهده تخفیف‌ها',

        // اطلاعات تماس و شبکه‌های اجتماعی
        'phone' => '09113294688',
        'phone_display' => '۰۹۱۱۳۲۹۴۶۸۸',
        'email' => 'rangsadeghi@gmail.com',
        'work_hours' => 'شنبه تا پنج‌شنبه، ۶:۳۰–۲۰:۳۰',
        'address' => 'مازندران، آمل، خیابان طالب آملی، دریای ۳۰/۱، پلاک ۴۴۴',
        'address_short' => 'آمل، خیابان طالب آملی، نبش دریای ۳۰/۱ — پلاک ۴۴۴',
        'map_url' => 'https://rangsadeghy.com/store-locations/',
        'map_latitude' => '',
        'map_longitude' => '',
        'instagram' => 'https://www.instagram.com/rangsadeghy',
        'whatsapp' => 'https://wa.me/989113294688',
        'telegram' => 'https://t.me/rangsadeghy',

        // فوتر و مجوزها
        'footer_about' => 'فروشگاه رنگ صادقی با تمرکز تخصصی بر رنگ ساختمانی، رنگ صنعتی، رنگ پتینه، هنری و ابزارآلات دستی و برقی حرفه‌ای فعالیت می‌کند؛ با هدف ارائه مجموعه‌ای کامل از محصولات معتبر برای خریدی امن، سریع و دقیق.',
        'enamad_url' => 'https://trustseal.enamad.ir/?id=574425&Code=e8HImAQ72k1oH8npkUuHCXT0c1OmYjHr',
        'enamad_code' => '',
        'enamad_image' => '',
        'copyright_text' => 'تمامی حقوق فروشگاه رنگ صادقی محفوظ می‌باشد و هرگونه کپی‌برداری مستلزم کسب اجازه‌ی کتبی است.',
        'credit_label' => 'آمل نوین',
        'credit_url' => 'https://amolnovin.ir/',
    ],

    /* ======================================================================
     * ۲. بخش‌های صفحه اصلی (قابل فعال/غیرفعال‌سازی و شخصی‌سازی کامل)
     * ====================================================================== */
    'home' => [
        'demo_fallback_when_empty' => true,
        'section_order' => 'hero,categories,deal,services,projects,bestsellers,brands,palette,newest,blog,closing',

        // ۱) هیرو اسلایدر + دو بنر کناری
        'hero_enabled' => true,
        'hero_autoplay_ms' => 5000,
        'hero_slides' => [
            [
                'desktop' => 'assets/images/hero-pottery.webp',
                'mobile' => 'assets/images/hero-pottery-mobile.webp',
                'alt' => 'زیبایی رو به خاک هدیه کن؛ گل، دوغاب، لعاب و انواع ابزار',
                'link' => '',
            ],
            [
                'desktop' => 'assets/images/hero-stencil.webp',
                'mobile' => 'assets/images/hero-stencil-mobile.webp',
                'alt' => 'نقشینو؛ شابلون با هر طرحی که تو بخوای',
                'link' => '',
            ],
            [
                'desktop' => 'assets/images/hero-sodaco.webp',
                'mobile' => 'assets/images/hero-sodaco-mobile.webp',
                'alt' => 'محصولات سوداکو',
                'link' => '',
            ],
        ],
        'hero_side_cards' => [
            [
                'image' => 'assets/images/blog/metallic.webp',
                'tag' => 'تا ۳۵٪ تخفیف',
                'tag_style' => 'red',
                'title' => 'جشنواره رنگ و پتینه',
                'subtitle' => 'پیشنهادهای محدود این هفته',
                'cat_slug' => '',
                'sale_only' => true,
            ],
            [
                'image' => 'assets/images/blog/tools.webp',
                'tag' => 'ابزار حرفه‌ای',
                'tag_style' => 'gold',
                'title' => 'کاردک، ماله و لیسه',
                'subtitle' => 'مناسب اجرای پتینه و تکسچر',
                'cat_slug' => 'tools',
                'sale_only' => false,
            ],
        ],

        // ۲) ردیف دسته‌بندی‌ها
        'categories_enabled' => true,
        'categories_title' => 'خرید بر اساس دسته‌بندی',
        'category_ids' => '',

        // ۳) پیشنهاد شگفت‌انگیز
        'deal_enabled' => true,
        'deal_title' => 'پیشنهاد شگفت‌انگیز',
        'deal_subtitle' => 'تخفیف‌های ویژه امروز، تا پایان روز',
        'deal_count' => 8,
        'deal_source' => 'on_sale', // on_sale | featured | category
        'deal_category' => '',
        'deal_end_datetime' => '', // خالی = شمارش معکوس خودکار تا پایان هر روز

        // ۴) نوار خدمات
        'services_enabled' => true,
        'services' => [
            ['icon' => 'i-truck', 'title' => 'ارسال سریع و مطمئن', 'subtitle' => 'پیک آمل، پست پیشتاز و تیپاکس'],
            ['icon' => 'i-shield', 'title' => 'ضمانت اصالت کالا', 'subtitle' => 'تأمین مستقیم از برندهای معتبر'],
            ['icon' => 'i-headset', 'title' => 'مشاوره تخصصی رنگ', 'subtitle' => 'کارشناسان حرفه‌ای، شنبه تا پنج‌شنبه'],
            ['icon' => 'i-refresh', 'title' => '۷ روز گارانتی بازگشت', 'subtitle' => 'مرجوعی آسان کالای سالم'],
        ],

        // ۵) خرید بر اساس پروژه
        'projects_enabled' => true,
        'projects_title' => 'خرید بر اساس پروژه',
        'projects' => [
            ['image' => 'assets/images/cat-building-photo.webp', 'title' => 'رنگ‌آمیزی دیوار و نما', 'subtitle' => 'رنگ ساختمانی، آستر و بتونه', 'cat' => 'building', 'tall' => true],
            ['image' => 'assets/images/blog/microcement.webp', 'title' => 'پتینه و میکروسمنت', 'subtitle' => 'تکسچر، ورق و پوشش دکوراتیو', 'cat' => 'patina', 'tall' => false],
            ['image' => 'assets/images/cat-stencil-photo.webp', 'title' => 'دیوارپوش شابلون و تابلو', 'subtitle' => 'شابلون، رنگ و ابزار اجرا', 'cat' => 'stencil', 'tall' => false],
            ['image' => 'assets/images/blog/patina.webp', 'title' => 'بازسازی و ترمیم', 'subtitle' => 'رنگ هنری، رزین و ابزار', 'cat' => 'art', 'tall' => false],
        ],

        // ۶) پرفروش‌ترین محصولات
        'bestsellers_enabled' => true,
        'bestsellers_title' => 'پرفروش‌ترین محصولات',
        'bestsellers_count' => 8,

        // ۷) برندها
        'brands_enabled' => true,
        'brands_count_badge' => '۲۰',
        'brands_desc' => 'این فروشگاه با تمرکز بر ارائه‌ی محصولات برندهای معتبر، ارائه محصولاتی با کیفیت بالا را فراهم کرده است.',

        // ۸) راهنمای پالت رنگ
        'palette_enabled' => true,
        'palette_eyebrow' => 'راهنمای رنگ',
        'palette_title' => 'این هفته با کدام پالت رنگ شروع کنیم؟',
        'palette_desc' => 'پالت‌های منتخب کارشناسان ما برای دیوار نشیمن، اتاق کار و نمای ساختمان؛ با یک کلیک همه رنگ‌ها و ابزار همان پروژه را ببینید.',
        'palette_swatches' => [
            ['hex' => '#c9b8a4', 'name' => 'شنی گرم', 'code' => 'BS-۲۱۰', 'cat' => 'building'],
            ['hex' => '#8d9299', 'name' => 'خاکستری بتنی', 'code' => 'GR-۴۴۰', 'cat' => 'building'],
            ['hex' => '#3d4f57', 'name' => 'آبی نفتی', 'code' => 'NV-۵۷۰', 'cat' => 'patina'],
            ['hex' => '#a5352f', 'name' => 'قرمز آجری', 'code' => 'RD-۳۲۰', 'cat' => 'art'],
            ['hex' => '#7c8564', 'name' => 'زیتونی مات', 'code' => 'OL-۶۱۰', 'cat' => 'patina'],
            ['hex' => '#d8c9a8', 'name' => 'سفید استخوانی', 'code' => 'WH-۱۰۰', 'cat' => 'art'],
        ],

        // ۹) جدیدترین محصولات
        'newest_enabled' => true,
        'newest_title' => 'جدیدترین محصولات',
        'newest_count' => 8,

        // ۱۰) مجله رنگ صادقی
        'blog_enabled' => true,
        'blog_title' => 'از مجله رنگ صادقی',
        'blog_count' => 3,

        // ۱۱) کارت فروشگاه حضوری و خبرنامه
        'closing_enabled' => true,
        'store_card_title' => 'حضوری هم ما را ببینید',
        'news_card_title' => 'تخفیف‌ها را اول از همه بگیرید',
        'news_card_desc' => 'با عضویت در خبرنامه، پیشنهادهای هفتگی و مقاله‌های تخصصی رنگ را دریافت کنید.',
    ],

    /* ======================================================================
     * ۳. تنظیمات فروشگاه، سبد خرید و تسویه حساب ووکامرس
     * ====================================================================== */
    'delivery_feedback' => [
        'enabled' => false,
        'mode' => 'text',
        'pattern' => '',
        'text' => '',
    ],

    'shop' => [
        'tracking_registered' => 'wc-pending',
        'tracking_payment' => 'wc-processing',
        'tracking_preparing' => '',
        'tracking_shipping' => '',
        'per_page' => 9,
        'default_view' => 'grid', // grid | list
        'default_sort' => 'default',
        'ajax_filtering' => true,
        'filter_categories' => true,
        'filter_brands' => true,
        'filter_price' => true,
        'filter_on_sale' => true,
        'filter_in_stock' => true,
        'sidebar_banner_enabled' => true,
        'sidebar_banner_image' => 'assets/images/blog/microcement.webp',
        'sidebar_banner_cat' => 'patina',
        'subcat_tiles_enabled' => true,
        'cta_band_enabled' => true,
        'new_badge_days' => 70,
        'low_stock_threshold' => 5,
        'free_shipping_threshold' => 10000000,
        'courier_enabled' => false,
        'courier_title' => 'پیک فروشگاه',
        'courier_cost' => 0,
        'courier_cities' => '',
        'pickup_enabled' => false,
        'pickup_title' => 'تحویل حضوری از فروشگاه',
        'pickup_cost' => 0,
        'pickup_address' => '',
        'delivery_days_ahead' => 6,
        'shipping_eta_default' => '',
        'shipping_eta_map' => '',
        'delivery_slots_enabled' => true,
        'delivery_slots' => '۹ تا ۱۲,۱۲ تا ۱۵,۱۵ تا ۱۸,۱۸ تا ۲۰:۳۰',
        'gift_wrap_enabled' => true,
        'gift_wrap_cost' => 35000,
        'official_invoice_enabled' => true,
        'card_transfer_number' => '۶۰۳۷ ۹۹۷۵ ۱۲۳۴ ۵۶۷۸',
        'card_transfer_holder' => 'فروشگاه رنگ صادقی — بانک ملی',
    ],

    /* ======================================================================
     * ۴. تنظیمات تک‌صفحه محصول
     * ====================================================================== */
    'product' => [
        'consent_category_ids' => '',
        'consent_text' => '',
        'gallery_zoom' => true,
        'show_sku' => true,
        'show_brand' => true,
        'show_stock_count' => true,
        'show_rating_row' => true,
        'show_trust_badges' => true,
        'warehouse_label' => 'انبار آمل، مازندران',
        'warranty_label' => 'اصالت کالا',
        'tab_description' => true,
        'tab_specs' => true,
        'tab_reviews' => true,
        'tab_shipping' => true,
        'shipping_tab_text' => 'سفارش‌ها در روزهای کاری بین ۲۴ تا ۷۲ ساعت آماده و از انبار آمل ارسال می‌شوند. برای شهرهای مازندران امکان ارسال با پیک در همان روز وجود دارد.',
        'shipping_tab_bullets' => "ارسال به سراسر کشور با پست پیشتاز و باربری.\nهزینه ارسال برای سفارش‌های بالای ۱۰ میلیون تومان رایگان است.\nامکان بازگشت کالای سالم و پلمب‌نشده تا ۷ روز پس از تحویل.",
        'related_enabled' => true,
        'related_count' => 8,
        'mobile_sticky_buy' => true,
    ],

    /* ======================================================================
     * ۵. سئو پیشرفته (SEO Engine)
     * ====================================================================== */
    'seo' => [
        'enabled' => true,
        'coexist_with_plugins' => true, // تشخیص خودکار Yoast / RankMath / SEOPress
        'title_separator' => '|',
        'home_title' => 'فروشگاه رنگ صادقی | خرید رنگ، پتینه، میکروسمنت و ابزار',
        'home_description' => 'فروشگاه رنگ صادقی؛ مرجع تخصصی خرید رنگ ساختمانی، صنعتی، رنگ هنری، پتینه، میکروسمنت، رزین اپوکسی، شابلون و ابزار نقاشی با ضمانت اصالت کالا.',
        'shop_description' => 'فروشگاه رنگ صادقی؛ خرید رنگ ساختمانی، پتینه، میکروسمنت، رنگ هنری و ابزار با فیلتر پیشرفته و ارسال سریع.',
        'default_og_image' => 'assets/images/hero-pottery.webp',
        'canonical_enabled' => true,
        'opengraph_enabled' => true,
        'twitter_card_enabled' => true,
        'noindex_search' => true,
        'noindex_cart_checkout' => true,
        'noindex_paged_archives' => false,
        'google_verification' => '',
        'bing_verification' => '',
        'yandex_verification' => '',
        'head_scripts' => '',
        'footer_scripts' => '',
    ],

    /* ======================================================================
     * ۶. اسکیماهای ساختاریافته (JSON-LD Rich Schemas)
     * ====================================================================== */
    'schema' => [
        'enabled' => true,
        'organization_enabled' => true,
        'local_business_enabled' => true,
        'business_type' => 'HomeAndConstructionBusiness', // PaintStore / HomeAndConstructionBusiness / Store
        'business_name' => 'فروشگاه رنگ صادقی',
        'price_range' => '$$',
        'postal_code' => '4614853721',
        'geo_lat' => '36.4696',
        'geo_lng' => '52.3507',
        'opening_days' => 'Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday',
        'opens' => '06:30',
        'closes' => '20:30',
        'website_search_enabled' => true,
        'breadcrumb_enabled' => true,
        'product_schema_enabled' => true,
        'product_currency_irr' => true, // تبدیل خودکار به IRR طبق استاندارد Google Merchant
        'return_policy_days' => 7,
        'article_schema_enabled' => true,
        'faq_schema_enabled' => true,
        'itemlist_schema_enabled' => true,
    ],

    /* ======================================================================
     * ۷. بهینه‌سازی سرعت سایت و Core Web Vitals
     * ====================================================================== */
    'performance' => [
        'preload_fonts' => true,
        'preload_lcp_image' => true,
        'disable_emojis' => true,
        'disable_embeds' => true,
        'disable_dashicons_guests' => true,
        'dequeue_block_styles' => true,
        'dequeue_wc_styles' => true,
        'disable_cart_fragments' => true,
        'defer_scripts' => true,
        'remove_query_strings' => false,
        'clean_wp_head' => true,
        'disable_xmlrpc' => true,
        'disable_self_pingbacks' => true,
        'heartbeat_mode' => 'slow', // default | slow (60s) | disable_front
        'minify_html' => false,
        'cache_frontpage_queries' => true,
        'preconnect_urls' => '',
    ],

    /* ======================================================================
     * ۸. سیستم بروزرسانی قالب از طریق Patch
     * ====================================================================== */
    'updater' => [
        'manifest_url' => '',
        'license_key' => '',
        'auto_backup_before_patch' => true,
        'verify_checksum' => true,
        'check_wp_updates' => true,
        'keep_backups_count' => 5,
    ],
];
