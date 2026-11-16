<?php

declare(strict_types=1);

namespace App\Support;

/**
 * کاتالوگ مرجع قالب HTML (۵ دسته‌بندی، ۹ برند، ۳۲ محصول و ۸ مقاله تخصصی)
 * کاربرد دوگانه:
 * ۱) درون‌ریز دمو با یک کلیک (One-Click Demo Importer) در پنل مدیریت وردپرس/ووکامرس
 * ۲) داده پیش‌نمایش خودکار در صورتی که هنوز محصولی در ووکامرس ثبت نشده باشد تا ظاهر قالب بلافاصله کامل باشد.
 */
final class DemoCatalog
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function categories(): array
    {
        return [
            [
                'slug' => 'building',
                'title' => 'ساختمانی و صنعتی',
                'short_title' => 'رنگ ساختمانی',
                'image' => 'cat-building.webp',
                'banner' => 'cat-building-photo.webp',
                'desc' => 'رنگ ساختمانی و صنعتی، تینر و حلال، چسب کاغذی، بتونه و آستری برای پروژه‌های اجرایی.',
                'children' => ['رنگ ساختمانی', 'رنگ صنعتی', 'تینر و حلال', 'چسب و پرایمر', 'بتونه و آستری'],
            ],
            [
                'slug' => 'art',
                'title' => 'بخش هنری',
                'short_title' => 'رنگ هنری',
                'image' => 'cat-art.webp',
                'banner' => 'blog/metallic.webp',
                'desc' => 'رنگ اکریلیک و روغن هنری، رزین اپوکسی، مواد سفالگری، افکت و وارنیش برای هنرمندان و هنرجویان.',
                'children' => ['رنگ اکریلیک', 'رنگ روغن', 'رزین اپوکسی هنری', 'سفالگری و مجسمه‌سازی', 'افکت و وارنیش'],
            ],
            [
                'slug' => 'patina',
                'title' => 'پتینه',
                'short_title' => 'پتینه و تکسچر',
                'image' => 'cat-patina.webp',
                'banner' => 'blog/patina.webp',
                'desc' => 'تکسچر، میکروسمنت، رنگ‌های دکوراتیو، ورق طلا و تثبیت‌کننده‌ها برای اجرای دیوارهای لوکس و یکپارچه.',
                'children' => ['تکسچرها', 'میکروسمنت', 'رنگ‌های دکوراتیو', 'ورق طلا و نقره', 'تثبیت‌کننده'],
            ],
            [
                'slug' => 'stencil',
                'title' => 'شابلون و تابلو',
                'short_title' => 'شابلون و تابلو',
                'image' => 'cat-stencil.webp',
                'banner' => 'cat-stencil-photo.webp',
                'desc' => 'شابلون ماندالا، داماسک و کالیگرافی، بوم نقاشی و بیس‌های چوبی با طرح‌های اختصاصی.',
                'children' => ['شابلون', 'ماندالا و داماسک', 'بوم نقاشی', 'بیس چوبی', 'حروف کالیگرافی'],
            ],
            [
                'slug' => 'tools',
                'title' => 'ابزارها',
                'short_title' => 'ابزار و لوازم',
                'image' => 'cat-tools.webp',
                'banner' => 'blog/tools.webp',
                'desc' => 'غلطک، قلم‌مو، لیسه و کاردک، ماله، سنباده و ابزارهای برقی حرفه‌ای برای اجرای بی‌نقص.',
                'children' => ['غلطک', 'قلم‌مو', 'لیسه و کاردک', 'ماله', 'سنباده'],
            ],
        ];
    }

    /**
     * @return array<int, array{slug: string, title: string, image: string}>
     */
    public static function brands(): array
    {
        return [
            ['slug' => 'rich', 'title' => 'ریچ', 'image' => 'brand-rich.webp'],
            ['slug' => 'sandora', 'title' => 'ساندورا', 'image' => 'brand-sandora.webp'],
            ['slug' => 'karen', 'title' => 'کارن', 'image' => 'brand-other.webp'],
            ['slug' => 'mako', 'title' => 'ماکو', 'image' => 'brand-mako.webp'],
            ['slug' => 'sodaco', 'title' => 'سوداکو', 'image' => 'brand-monomart.webp'],
            ['slug' => 'pars', 'title' => 'پارس آرتیست', 'image' => 'brand-pars.webp'],
            ['slug' => 'rando', 'title' => 'راندو', 'image' => 'brand-other.webp'],
            ['slug' => 'boomiran', 'title' => 'بومیران', 'image' => 'brand-boomiran.webp'],
            ['slug' => 'vilko', 'title' => 'ویلکو', 'image' => 'brand-other.webp'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function products(): array
    {
        return [
            ['id' => 'p1', 'group' => 'sale', 'title' => 'کاردک استیل ۳ عددی ویلکو', 'cat' => 'ابزارها، لیسه و کاردک', 'cats' => ['tools'], 'brand' => 'vilko', 'price' => 590000, 'old' => 790000, 'discount' => 25, 'rating' => 4.5, 'reviews' => 12, 'image' => 'sale-kardak.webp', 'gallery' => ['sale-kardak.webp', 'promo-kardak.webp', 'popular-roller.webp'], 'sku' => 'RS-1001', 'stock' => 18, 'date' => '2026-09-02', 'sold' => 84, 'short' => 'ست کاردک استیل حرفه‌ای در سه اندازه مختلف، مناسب پتینه‌کاری، میکروسمنت و اجرای تکسچر.', 'specs' => [['جنس', 'استیل ضدزنگ'], ['تعداد در بسته', '۳ عدد'], ['کاربرد', 'پتینه، میکروسمنت، کناف'], ['برند', 'ویلکو'], ['کشور سازنده', 'ایران']]],
            ['id' => 'p2', 'group' => 'sale', 'title' => 'تار عنکبوتی ۵۰۰ گرمی گلد ساندورا', 'cat' => 'رنگ‌های دکوراتیو، عنکبوتی', 'cats' => ['patina'], 'brand' => 'sandora', 'price' => 1220000, 'old' => 1848000, 'discount' => 34, 'rating' => 5.0, 'reviews' => 26, 'image' => 'sale-spider-gold.webp', 'gallery' => ['sale-spider-gold.webp', 'sale-spider.webp'], 'sku' => 'RS-1002', 'stock' => 9, 'date' => '2026-08-21', 'sold' => 132, 'short' => 'رنگ دکوراتیو تار عنکبوتی با افکت متالیک طلایی؛ پوشش‌دهی بالا و اجرای آسان روی دیوار.', 'specs' => [['وزن', '۵۰۰ گرم'], ['رنگ', 'طلایی (Gold)'], ['پوشش‌دهی', '۶ تا ۸ متر مربع'], ['برند', 'ساندورا'], ['پایه', 'آب‌پایه']]],
            ['id' => 'p3', 'group' => 'sale', 'title' => 'تار عنکبوتی ۵۰۰ گرمی ته بنفش ساندورا', 'cat' => 'رنگ‌های دکوراتیو، عنکبوتی', 'cats' => ['patina'], 'brand' => 'sandora', 'price' => 1220000, 'old' => 1848000, 'discount' => 34, 'rating' => 4.5, 'reviews' => 8, 'image' => 'sale-spider.webp', 'gallery' => ['sale-spider.webp', 'sale-spider-gold.webp'], 'sku' => 'RS-1003', 'stock' => 14, 'date' => '2026-08-21', 'sold' => 61, 'short' => 'افکت تار عنکبوتی با ته‌رنگ بنفش؛ انتخابی متفاوت برای دیوار شاخص نشیمن و لابی.', 'specs' => [['وزن', '۵۰۰ گرم'], ['رنگ', 'ته بنفش'], ['پوشش‌دهی', '۶ تا ۸ متر مربع'], ['برند', 'ساندورا'], ['پایه', 'آب‌پایه']]],
            ['id' => 'p4', 'group' => 'sale', 'title' => 'تار عنکبوتی ۵۰۰ گرمی ته آبی ساندورا', 'cat' => 'رنگ‌های دکوراتیو، عنکبوتی', 'cats' => ['patina'], 'brand' => 'sandora', 'price' => 1220000, 'old' => 1848000, 'discount' => 34, 'rating' => 4.0, 'reviews' => 5, 'image' => 'sale-spider.webp', 'gallery' => ['sale-spider.webp', 'sale-spider-gold.webp'], 'sku' => 'RS-1004', 'stock' => 11, 'date' => '2026-08-21', 'sold' => 45, 'short' => 'رنگ دکوراتیو عنکبوتی با ته‌رنگ آبی؛ افکتی مدرن و عمق‌دار روی سطح دیوار.', 'specs' => [['وزن', '۵۰۰ گرم'], ['رنگ', 'ته آبی'], ['پوشش‌دهی', '۶ تا ۸ متر مربع'], ['برند', 'ساندورا'], ['پایه', 'آب‌پایه']]],
            ['id' => 'p5', 'group' => 'sale', 'title' => 'تار عنکبوتی ۵۰۰ گرمی سیلور ساندورا', 'cat' => 'رنگ‌های دکوراتیو، عنکبوتی', 'cats' => ['patina'], 'brand' => 'sandora', 'price' => 1220000, 'old' => 1848000, 'discount' => 34, 'rating' => 4.5, 'reviews' => 9, 'image' => 'sale-spider.webp', 'gallery' => ['sale-spider.webp', 'sale-spider-gold.webp'], 'sku' => 'RS-1005', 'stock' => 16, 'date' => '2026-08-21', 'sold' => 72, 'short' => 'افکت عنکبوتی سیلور با درخشش نقره‌ای؛ هماهنگ با سبک‌های مینیمال و مدرن.', 'specs' => [['وزن', '۵۰۰ گرم'], ['رنگ', 'نقره‌ای (Silver)'], ['پوشش‌دهی', '۶ تا ۸ متر مربع'], ['برند', 'ساندورا'], ['پایه', 'آب‌پایه']]],
            ['id' => 'p6', 'group' => 'sale', 'title' => 'رنگ مولتی سورفیس ریچ کد 2234', 'cat' => 'مولتی سورفیس‌ها', 'cats' => ['art', 'building'], 'brand' => 'rich', 'price' => 690000, 'old' => 830000, 'discount' => 17, 'rating' => 5.0, 'reviews' => 18, 'image' => 'sale-2234.webp', 'gallery' => ['sale-2234.webp', 'sale-2200.webp', 'sale-2172.webp'], 'sku' => 'RS-1006', 'stock' => 24, 'date' => '2026-07-30', 'sold' => 118, 'short' => 'رنگ مولتی سورفیس ریچ با چسبندگی بالا روی چوب، فلز، شیشه و پلاستیک؛ بدون نیاز به آستری.', 'specs' => [['حجم', '۱ کیلوگرم'], ['کد رنگ', '2234'], ['سطوح', 'چوب، فلز، شیشه، پلاستیک'], ['برند', 'ریچ'], ['پوشش', 'نیم‌براق']]],
            ['id' => 'p7', 'group' => 'sale', 'title' => 'رنگ مولتی سورفیس ریچ کد 2200', 'cat' => 'مولتی سورفیس‌ها', 'cats' => ['art', 'building'], 'brand' => 'rich', 'price' => 690000, 'old' => 830000, 'discount' => 17, 'rating' => 4.5, 'reviews' => 7, 'image' => 'sale-2200.webp', 'gallery' => ['sale-2200.webp', 'sale-2234.webp', 'sale-2172.webp'], 'sku' => 'RS-1007', 'stock' => 20, 'date' => '2026-07-30', 'sold' => 66, 'short' => 'مولتی سورفیس با پوشش‌دهی عالی و خشک شدن سریع؛ مناسب کارهای هنری و بازسازی.', 'specs' => [['حجم', '۱ کیلوگرم'], ['کد رنگ', '2200'], ['سطوح', 'چوب، فلز، شیشه'], ['برند', 'ریچ'], ['پوشش', 'نیم‌براق']]],
            ['id' => 'p8', 'group' => 'sale', 'title' => 'رنگ مولتی سورفیس ریچ کد 2172', 'cat' => 'مولتی سورفیس‌ها', 'cats' => ['art', 'building'], 'brand' => 'rich', 'price' => 690000, 'old' => 830000, 'discount' => 17, 'rating' => 4.0, 'reviews' => 4, 'image' => 'sale-2172.webp', 'gallery' => ['sale-2172.webp', 'sale-2200.webp', 'sale-2234.webp'], 'sku' => 'RS-1008', 'stock' => 8, 'date' => '2026-07-30', 'sold' => 39, 'short' => 'رنگ همه‌کاره ریچ برای سطوح سخت؛ مقاوم در برابر رطوبت و خط‌وخش.', 'specs' => [['حجم', '۱ کیلوگرم'], ['کد رنگ', '2172'], ['سطوح', 'چوب، فلز، شیشه'], ['برند', 'ریچ'], ['پوشش', 'نیم‌براق']]],
            ['id' => 'p9', 'group' => 'best', 'title' => 'ماژیک بیسیک دو سر راندو کد 7545', 'cat' => 'ماژیک', 'cats' => ['art'], 'brand' => 'rando', 'price' => 100000, 'rating' => 4.5, 'reviews' => 31, 'image' => 'best-marker-7545.webp', 'gallery' => ['best-marker-7545.webp', 'best-marker-7533.webp'], 'sku' => 'RS-1009', 'stock' => 60, 'date' => '2026-06-11', 'sold' => 340, 'short' => 'ماژیک دو سر با نوک باریک و پهن، مناسب طراحی، اسکچ و کارهای گرافیکی.', 'specs' => [['نوع', 'ماژیک الکلی دو سر'], ['کد رنگ', '7545'], ['برند', 'راندو'], ['کاربرد', 'طراحی و اسکچ']]],
            ['id' => 'p10', 'group' => 'best', 'title' => 'تکسچر سرامیک کارن ۱ کیلویی', 'cat' => 'تکسچرها', 'cats' => ['patina'], 'brand' => 'karen', 'price' => 590000, 'rating' => 5.0, 'reviews' => 42, 'image' => 'best-ceramic.webp', 'gallery' => ['best-ceramic.webp', 'best-ceramic-5.webp'], 'sku' => 'RS-1010', 'stock' => 22, 'date' => '2026-05-18', 'sold' => 410, 'short' => 'تکسچر سرامیک کارن با بافت نرم و پوشش‌دهی بالا؛ پایه‌ی بسیاری از پروژه‌های پتینه.', 'specs' => [['وزن', '۱ کیلوگرم'], ['بافت', 'سرامیک'], ['پوشش‌دهی', '۴ تا ۶ متر مربع'], ['برند', 'کارن'], ['پایه', 'آب‌پایه']]],
            ['id' => 'p11', 'group' => 'best', 'title' => 'تکسچر فاین استون کارن ۱ کیلویی', 'cat' => 'تکسچرها', 'cats' => ['patina'], 'brand' => 'karen', 'price' => 400000, 'rating' => 4.5, 'reviews' => 19, 'image' => 'best-fine-stone.webp', 'gallery' => ['best-fine-stone.webp', 'best-sand-stone.webp'], 'sku' => 'RS-1011', 'stock' => 30, 'date' => '2026-05-18', 'sold' => 275, 'short' => 'تکسچر سنگی ریزبافت برای ایجاد سطحی شبیه سنگ طبیعی؛ مناسب دیوار و ستون.', 'specs' => [['وزن', '۱ کیلوگرم'], ['بافت', 'سنگ ریز'], ['پوشش‌دهی', '۴ تا ۶ متر مربع'], ['برند', 'کارن']]],
            ['id' => 'p12', 'group' => 'best', 'title' => 'غلطک فومی ۱۰ سانت ماکو', 'cat' => 'غلطک', 'cats' => ['tools'], 'brand' => 'mako', 'price' => 370000, 'rating' => 4.0, 'reviews' => 15, 'image' => 'best-roller.webp', 'gallery' => ['best-roller.webp', 'popular-roller.webp'], 'sku' => 'RS-1012', 'stock' => 45, 'date' => '2026-04-02', 'sold' => 520, 'short' => 'غلطک فومی با دسته ارگونومیک برای پوشش‌های یکدست روی سطوح صاف و پتینه.', 'specs' => [['عرض', '۱۰ سانتی‌متر'], ['جنس رول', 'فوم'], ['برند', 'ماکو'], ['کاربرد', 'رنگ و پوشش پتینه']]],
            ['id' => 'p13', 'group' => 'best', 'title' => 'تکسچر سرامیک کارن ۵ کیلویی', 'cat' => 'تکسچرها', 'cats' => ['patina'], 'brand' => 'karen', 'price' => 2250000, 'rating' => 5.0, 'reviews' => 23, 'image' => 'best-ceramic-5.webp', 'gallery' => ['best-ceramic-5.webp', 'best-ceramic.webp'], 'sku' => 'RS-1013', 'stock' => 6, 'date' => '2026-05-18', 'sold' => 148, 'short' => 'بسته اقتصادی ۵ کیلویی تکسچر سرامیک؛ مناسب پروژه‌های بزرگ و اجرای پیمانکاری.', 'specs' => [['وزن', '۵ کیلوگرم'], ['بافت', 'سرامیک'], ['پوشش‌دهی', '۲۰ تا ۳۰ متر مربع'], ['برند', 'کارن']]],
            ['id' => 'p14', 'group' => 'best', 'title' => 'ماژیک بیسیک دو سر راندو کد 7533', 'cat' => 'ماژیک', 'cats' => ['art'], 'brand' => 'rando', 'price' => 100000, 'rating' => 4.5, 'reviews' => 12, 'image' => 'best-marker-7533.webp', 'gallery' => ['best-marker-7533.webp', 'best-marker-7545.webp'], 'sku' => 'RS-1014', 'stock' => 52, 'date' => '2026-06-11', 'sold' => 288, 'short' => 'ماژیک راندو کد 7533 با جوهر پوشاننده و خشک‌شدن سریع.', 'specs' => [['نوع', 'ماژیک الکلی دو سر'], ['کد رنگ', '7533'], ['برند', 'راندو']]],
            ['id' => 'p15', 'group' => 'best', 'title' => 'لاک افکت کارن ۲۵۰ میل', 'cat' => 'افکت‌ها', 'cats' => ['patina', 'art'], 'brand' => 'karen', 'price' => 520000, 'rating' => 4.5, 'reviews' => 16, 'image' => 'best-lacquer.webp', 'gallery' => ['best-lacquer.webp', 'best-fine-stone.webp'], 'sku' => 'RS-1015', 'stock' => 17, 'date' => '2026-03-15', 'sold' => 205, 'short' => 'لاک محافظ افکت با چسبندگی بالا؛ تثبیت‌کننده رنگ و پوشش پتینه در برابر رطوبت.', 'specs' => [['حجم', '۲۵۰ میلی‌لیتر'], ['پوشش', 'براق'], ['برند', 'کارن'], ['نوع', 'تثبیت‌کننده']]],
            ['id' => 'p16', 'group' => 'best', 'title' => 'تکسچر سند استون کارن ۱ کیلویی', 'cat' => 'تکسچرها', 'cats' => ['patina'], 'brand' => 'karen', 'price' => 400000, 'rating' => 4.0, 'reviews' => 11, 'image' => 'best-sand-stone.webp', 'gallery' => ['best-sand-stone.webp', 'best-fine-stone.webp'], 'sku' => 'RS-1016', 'stock' => 26, 'date' => '2026-05-18', 'sold' => 190, 'short' => 'تکسچر سنگ شنی با بافت درشت‌تر؛ مناسب نمای داخلی و ستون‌های دکوراتیو.', 'specs' => [['وزن', '۱ کیلوگرم'], ['بافت', 'سنگ شنی'], ['پوشش‌دهی', '۴ تا ۶ متر مربع'], ['برند', 'کارن']]],
            ['id' => 'p17', 'group' => 'new', 'title' => 'اسفنج مصنوعی دونه ریز ماکو', 'cat' => 'ابزارها، ابزار پتینه', 'cats' => ['tools'], 'brand' => 'mako', 'price' => 750000, 'rating' => 4.0, 'reviews' => 6, 'image' => 'new-sponge.webp', 'gallery' => ['new-sponge.webp', 'cat-patina.webp'], 'sku' => 'RS-1017', 'stock' => 33, 'date' => '2026-10-28', 'sold' => 24, 'short' => 'اسفنج مصنوعی دونه ریز برای تکنیک‌های پتینه، اسفنجی و ایجاد بافت نرم.', 'specs' => [['جنس', 'اسفنج مصنوعی'], ['دانه‌بندی', 'ریز'], ['برند', 'ماکو'], ['کاربرد', 'پتینه و بافت']]],
            ['id' => 'p18', 'group' => 'new', 'title' => 'مولتی سورفیس نقره‌ای ۱۲۵ میل سوداکو', 'cat' => 'مولتی سورفیس‌ها', 'cats' => ['art'], 'brand' => 'sodaco', 'price' => 490000, 'rating' => 4.5, 'reviews' => 3, 'image' => 'new-silver.webp', 'gallery' => ['new-silver.webp', 'new-gold.webp'], 'sku' => 'RS-1018', 'stock' => 19, 'date' => '2026-10-25', 'sold' => 18, 'short' => 'رنگ مولتی سورفیس متالیک نقره‌ای سوداکو با درخشش بالا؛ ایده‌آل برای کارهای هنری.', 'specs' => [['حجم', '۱۲۵ میلی‌لیتر'], ['رنگ', 'نقره‌ای متالیک'], ['برند', 'سوداکو'], ['سطوح', 'همه سطوح']]],
            ['id' => 'p19', 'group' => 'new', 'title' => 'مولتی سورفیس طلایی ۱۲۵ میل سوداکو', 'cat' => 'مولتی سورفیس‌ها', 'cats' => ['art'], 'brand' => 'sodaco', 'price' => 490000, 'rating' => 5.0, 'reviews' => 4, 'image' => 'new-gold.webp', 'gallery' => ['new-gold.webp', 'new-silver.webp'], 'sku' => 'RS-1019', 'stock' => 21, 'date' => '2026-10-25', 'sold' => 22, 'short' => 'مولتی سورفیس متالیک طلایی با پوشش یکدست و مقاوم؛ مناسب تذهیب و دکور.', 'specs' => [['حجم', '۱۲۵ میلی‌لیتر'], ['رنگ', 'طلایی متالیک'], ['برند', 'سوداکو'], ['سطوح', 'همه سطوح']]],
            ['id' => 'p20', 'group' => 'new', 'title' => 'بوم نیم دایره قطر ۱۰۰', 'cat' => 'بوم‌ها', 'cats' => ['stencil'], 'brand' => 'boomiran', 'price' => 1950000, 'rating' => 4.5, 'reviews' => 2, 'image' => 'new-canvas-100.webp', 'gallery' => ['new-canvas-100.webp', 'new-canvas-70.webp'], 'sku' => 'RS-1020', 'stock' => 5, 'date' => '2026-10-20', 'sold' => 9, 'short' => 'بوم نیم‌دایره با پارچه کتان و زیرسازی آماده رنگ‌آمیزی؛ سایز شاخص برای دکور دیوار.', 'specs' => [['قطر', '۱۰۰ سانتی‌متر'], ['جنس', 'کتان'], ['برند', 'بومیران'], ['نوع', 'نیم‌دایره']]],
            ['id' => 'p21', 'group' => 'new', 'title' => 'بوم نیم دایره قطر ۷۰', 'cat' => 'بوم‌ها', 'cats' => ['stencil'], 'brand' => 'boomiran', 'price' => 1110000, 'rating' => 4.0, 'reviews' => 1, 'image' => 'new-canvas-70.webp', 'gallery' => ['new-canvas-70.webp', 'new-canvas-60.webp'], 'sku' => 'RS-1021', 'stock' => 8, 'date' => '2026-10-20', 'sold' => 12, 'short' => 'بوم نیم‌دایره سایز متوسط، مناسب تابلوهای دکوراتیو و ترکیب‌بندی دو تکه.', 'specs' => [['قطر', '۷۰ سانتی‌متر'], ['جنس', 'کتان'], ['برند', 'بومیران'], ['نوع', 'نیم‌دایره']]],
            ['id' => 'p22', 'group' => 'new', 'title' => 'بوم نیم دایره قطر ۶۰', 'cat' => 'بوم‌ها', 'cats' => ['stencil'], 'brand' => 'boomiran', 'price' => 910000, 'rating' => 4.0, 'reviews' => 1, 'image' => 'new-canvas-60.webp', 'gallery' => ['new-canvas-60.webp', 'new-canvas-70.webp'], 'sku' => 'RS-1022', 'stock' => 10, 'date' => '2026-10-20', 'sold' => 15, 'short' => 'بوم نیم‌دایره کوچک با بافت یکدست؛ مناسب کارهای هنری و هدیه.', 'specs' => [['قطر', '۶۰ سانتی‌متر'], ['جنس', 'کتان'], ['برند', 'بومیران'], ['نوع', 'نیم‌دایره']]],
            ['id' => 'p23', 'group' => 'shop', 'title' => 'رنگ اکریلیک هنری سوداکو ۲۵۰ میل', 'cat' => 'رنگ اکریلیک', 'cats' => ['art'], 'brand' => 'sodaco', 'price' => 380000, 'old' => 450000, 'discount' => 16, 'rating' => 4.5, 'reviews' => 14, 'image' => 'popular-acrylic.webp', 'gallery' => ['popular-acrylic.webp', 'popular-oil.webp'], 'sku' => 'RS-1023', 'stock' => 40, 'date' => '2026-09-14', 'sold' => 96, 'short' => 'اکریلیک هنری با پیگمنت بالا و پوشانندگی عالی؛ رقیق‌شونده با آب.', 'specs' => [['حجم', '۲۵۰ میلی‌لیتر'], ['پایه', 'آب‌پایه'], ['برند', 'سوداکو'], ['پوشش', 'مات']]],
            ['id' => 'p24', 'group' => 'shop', 'title' => 'رنگ روغن هنری پارس آرتیست ۳۷ میل', 'cat' => 'رنگ روغن‌ها', 'cats' => ['art'], 'brand' => 'pars', 'price' => 265000, 'rating' => 4.5, 'reviews' => 21, 'image' => 'popular-oil.webp', 'gallery' => ['popular-oil.webp', 'popular-acrylic.webp'], 'sku' => 'RS-1024', 'stock' => 55, 'date' => '2026-08-30', 'sold' => 174, 'short' => 'رنگ روغن با روغن بزرک تصفیه‌شده؛ زمان خشک‌شدن متعادل و ترکیب‌پذیری بالا.', 'specs' => [['حجم', '۳۷ میلی‌لیتر'], ['پایه', 'روغنی'], ['برند', 'پارس آرتیست'], ['پوشش', 'براق']]],
            ['id' => 'p25', 'group' => 'shop', 'title' => 'قلم‌مو پتینه پارس آرتیست سایز ۱۲', 'cat' => 'قلم‌مو', 'cats' => ['tools'], 'brand' => 'pars', 'price' => 320000, 'rating' => 4.0, 'reviews' => 8, 'image' => 'popular-brush.webp', 'gallery' => ['popular-brush.webp', 'cat-tools.webp'], 'sku' => 'RS-1025', 'stock' => 37, 'date' => '2026-08-11', 'sold' => 143, 'short' => 'قلم‌مو با موی مصنوعی مقاوم، مناسب اجرای پتینه، وارنیش و کارهای هنری.', 'specs' => [['سایز', '۱۲'], ['جنس مو', 'مصنوعی'], ['برند', 'پارس آرتیست']]],
            ['id' => 'p26', 'group' => 'shop', 'title' => 'ماله پتینه استیل ۲۵ سانت', 'cat' => 'ماله', 'cats' => ['tools'], 'brand' => 'mako', 'price' => 480000, 'rating' => 4.5, 'reviews' => 10, 'image' => 'popular-male.webp', 'gallery' => ['popular-male.webp', 'popular-roller.webp'], 'sku' => 'RS-1026', 'stock' => 15, 'date' => '2026-07-19', 'sold' => 87, 'short' => 'ماله استیل با لبه صیقلی برای اجرای یکدست میکروسمنت و تکسچر.', 'specs' => [['عرض', '۲۵ سانتی‌متر'], ['جنس', 'استیل'], ['برند', 'ماکو']]],
            ['id' => 'p27', 'group' => 'shop', 'title' => 'میکروسمنت آماده کارن ۵ کیلویی', 'cat' => 'میکروسمنت', 'cats' => ['patina'], 'brand' => 'karen', 'price' => 2450000, 'old' => 2900000, 'discount' => 15, 'rating' => 5.0, 'reviews' => 30, 'image' => 'popular-micro.webp', 'gallery' => ['popular-micro.webp', 'popular-male.webp'], 'sku' => 'RS-1027', 'stock' => 7, 'date' => '2026-09-08', 'sold' => 121, 'short' => 'میکروسمنت دو جزئی آماده اجرا برای دیوار و کف؛ سطحی یکپارچه، مقاوم و بدون درز.', 'specs' => [['وزن', '۵ کیلوگرم'], ['اجزا', 'دو جزئی'], ['پوشش‌دهی', '۳ تا ۵ متر مربع'], ['برند', 'کارن']]],
            ['id' => 'p28', 'group' => 'shop', 'title' => 'رزین اپوکسی هنری شفاف ۱ کیلویی', 'cat' => 'رزین اپوکسی', 'cats' => ['art'], 'brand' => 'karen', 'price' => 3200000, 'old' => 3800000, 'discount' => 16, 'rating' => 5.0, 'reviews' => 25, 'image' => 'popular-resin.webp', 'gallery' => ['popular-resin.webp', 'popular-acrylic.webp'], 'sku' => 'RS-1028', 'stock' => 4, 'date' => '2026-09-22', 'sold' => 98, 'short' => 'رزین اپوکسی شفاف با زمان کار مناسب و شفافیت بالا؛ مناسب کارهای هنری و پوشش نهایی.', 'specs' => [['وزن', '۱ کیلوگرم'], ['نسبت ترکیب', '۲ به ۱'], ['زمان خشک', '۲۴ ساعت'], ['برند', 'کارن']]],
            ['id' => 'p29', 'group' => 'shop', 'title' => 'شابلون ماندالا طرح اختصاصی ۴۰×۴۰', 'cat' => 'شابلون', 'cats' => ['stencil'], 'brand' => 'boomiran', 'price' => 590000, 'rating' => 4.5, 'reviews' => 13, 'image' => 'cat-stencil.webp', 'gallery' => ['cat-stencil.webp', 'new-canvas-70.webp'], 'sku' => 'RS-1029', 'stock' => 23, 'date' => '2026-06-25', 'sold' => 156, 'short' => 'شابلون ماندالا با برش لیزری دقیق و فیلم مقاوم؛ قابل استفاده چندباره.', 'specs' => [['ابعاد', '۴۰×۴۰ سانتی‌متر'], ['جنس', 'فیلم پت'], ['برند', 'بومیران'], ['نوع', 'ماندالا']]],
            ['id' => 'p30', 'group' => 'shop', 'title' => 'چسب کاغذی ساختمانی ۵ کیلویی', 'cat' => 'چسب', 'cats' => ['building'], 'brand' => 'rich', 'price' => 950000, 'old' => 1150000, 'discount' => 17, 'rating' => 4.0, 'reviews' => 9, 'image' => 'cat-building.webp', 'gallery' => ['cat-building.webp', 'sale-2200.webp'], 'sku' => 'RS-1030', 'stock' => 12, 'date' => '2026-04-28', 'sold' => 74, 'short' => 'چسب کاغذی مخصوص زیرسازی دیوار؛ چسبندگی بالا و اجرای آسان قبل از رنگ.', 'specs' => [['وزن', '۵ کیلوگرم'], ['کاربرد', 'زیرسازی دیوار'], ['برند', 'ریچ']]],
            ['id' => 'p31', 'group' => 'shop', 'title' => 'ابزار سفالگری حرفه‌ای ۸ پارچه', 'cat' => 'سفالگری', 'cats' => ['art'], 'brand' => 'pars', 'price' => 890000, 'rating' => 4.5, 'reviews' => 7, 'image' => 'popular-pottery.webp', 'gallery' => ['popular-pottery.webp', 'popular-roller.webp'], 'sku' => 'RS-1031', 'stock' => 9, 'date' => '2026-05-09', 'sold' => 41, 'short' => 'ست ابزار سفالگری شامل قالب، کاردک و ابزارهای شکل‌دهی؛ مناسب هنرجویان و حرفه‌ای‌ها.', 'specs' => [['تعداد', '۸ پارچه'], ['جنس', 'استیل و چوب'], ['برند', 'پارس آرتیست']]],
            ['id' => 'p32', 'group' => 'shop', 'title' => 'رنگ مولتی سورفیس ریچ ۱.۷۵ کیلویی کد 2240', 'cat' => 'مولتی سورفیس‌ها', 'cats' => ['building', 'art'], 'brand' => 'rich', 'price' => 1150000, 'old' => 1390000, 'discount' => 17, 'rating' => 4.5, 'reviews' => 6, 'image' => 'sale-2200.webp', 'gallery' => ['sale-2200.webp', 'sale-2234.webp'], 'sku' => 'RS-1032', 'stock' => 13, 'date' => '2026-03-08', 'sold' => 52, 'short' => 'مولتی سورفیس با حجم اقتصادی؛ پوشش مقاوم برای سطوح داخلی و خارجی.', 'specs' => [['حجم', '۱.۷۵ کیلوگرم'], ['کد رنگ', '2240'], ['برند', 'ریچ']]],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function posts(): array
    {
        return [
            ['slug' => 'what-is-patina', 'title' => 'پتینه چیست؟ انواع، کاربرد و تفاوت با رنگ دیوار', 'excerpt' => 'پتینه‌کاری هنر تغییر چهره دیوارهاست؛ در این مقاله انواع پتینه، کاربرد هرکدام و تفاوت‌هایش با رنگ معمولی دیوار را بررسی می‌کنیم.', 'cat' => 'پتینه', 'tags' => 'patina', 'image' => 'blog/patina.webp', 'date' => '۱۰ آبان ۱۴۰۵', 'read' => '۸ دقیقه', 'featured' => true],
            ['slug' => 'microcement-guide', 'title' => 'میکروسمنت؛ پوششی یکپارچه برای کف و دیوار', 'excerpt' => 'سطحی بدون درز، مقاوم و مدرن؛ میکروسمنت را با مراحل اجرا و نکات کلیدی‌اش بشناسید.', 'cat' => 'میکروسمنت', 'tags' => 'patina', 'image' => 'blog/microcement.webp', 'date' => '۱۲ مرداد ۱۴۰۵', 'read' => '۶ دقیقه'],
            ['slug' => 'paint-roller-vs-brush', 'title' => 'غلطک یا قلم‌مو؛ کدام برای کدام سطح؟', 'excerpt' => 'انتخاب ابزار درست، کیفیت نهایی کار را تعیین می‌کند؛ مقایسه غلطک و قلم‌مو در سطوح مختلف.', 'cat' => 'ابزار', 'tags' => 'tools', 'image' => 'blog/tools.webp', 'date' => '۰۸ تیر ۱۴۰۵', 'read' => '۵ دقیقه'],
            ['slug' => 'metallic-acrylic-paint', 'title' => 'رنگ اکریلیک متالیک چیست و چطور از آن برای دیوار و تابلو استفاده کنیم؟', 'excerpt' => 'درخشش فلزی، سطوح لوکس می‌سازد؛ راهنمای کامل انتخاب، ترکیب و اجرای اکریلیک متالیک روی دیوار و تابلو.', 'cat' => 'رنگ هنری', 'tags' => 'art', 'image' => 'blog/metallic.webp', 'date' => '۰۷ مهر ۱۴۰۵', 'read' => '۷ دقیقه'],
            ['slug' => 'gold-leaf-patina', 'title' => 'ورق طلا و نقره در پتینه‌کاری؛ از انتخاب ورق تا تکنیک آنتیک', 'excerpt' => 'همه چیز درباره ورق طلا و نقره: چسب مخصوص، ابزار، روش اجرا و تکنیک آنتیک برای رسیدن به سطحی کلاسیک.', 'cat' => 'آموزش', 'tags' => 'patina', 'image' => 'blog/gold-leaf.webp', 'date' => '۲۴ شهریور ۱۴۰۵', 'read' => '۱۲ دقیقه'],
            ['slug' => 'buy-artist-oil-paint', 'title' => 'خرید رنگ روغن هنری؛ تفاوت برندها و نکاتی که قبل از خرید باید بدانید', 'excerpt' => 'پیگمنت، روغن و زمان خشک‌شدن؛ سه فاکتور کلیدی که کیفیت رنگ روغن هنری را تعیین می‌کنند.', 'cat' => 'رنگ هنری', 'tags' => 'art', 'image' => 'blog/oil-paint.webp', 'date' => '۱۴ شهریور ۱۴۰۵', 'read' => '۸ دقیقه'],
            ['slug' => 'velvet-powder-wall', 'title' => 'پودر مخمل چیست؟ کاربرد، روش اجرا و تفاوت با رنگ مخملی', 'excerpt' => 'دیوارهای نرم و لوکس با پودر مخمل؛ چه سطوحی مناسب است و چه ابزاری برای اجرا لازم دارید.', 'cat' => 'پتینه', 'tags' => 'patina', 'image' => 'blog/velvet.webp', 'date' => '۲۹ مرداد ۱۴۰۵', 'read' => '۱۰ دقیقه'],
            ['slug' => 'what-is-epoxy-resin', 'title' => 'رزین اپوکسی چیست؟ انواع، نسبت ترکیب و کاربرد در هنر', 'excerpt' => 'نسبت ترکیب، زمان کار، حباب‌گیری و ایمنی؛ هر آنچه برای شروع کار با رزین اپوکسی باید بدانید.', 'cat' => 'رنگ هنری', 'tags' => 'art', 'image' => 'blog/epoxy.webp', 'date' => '۱۷ مرداد ۱۴۰۵', 'read' => '۱۱ دقیقه'],
        ];
    }
}
