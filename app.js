/* ==========================================================================
   رنگ صادقی | هسته‌ی مشترک همه صفحات (app.js)
   این فایل دیتای محصولات، سبد خرید، علاقه‌مندی‌ها، جستجو، منو و مودال‌ها را
   در تمام صفحات سایت (خانه، فروشگاه، دسته‌بندی، محصول، وبلاگ...) مدیریت می‌کند.
   ========================================================================== */

/* ---------------------------------- دسته‌بندی ها ---------------------------------- */
const CATEGORIES = [
  {
    slug: 'building', title: 'ساختمانی و صنعتی', image: 'cat-building.webp',
    desc: 'رنگ‌های ساختمانی، تینر، چسب و پوشش‌های صنعتی برای پروژه‌های اجرایی و بازسازی.',
    children: [
      { title: 'رنگ ساختمانی', href: 'shop.html?cat=building' },
      { title: 'رنگ صنعتی', href: 'shop.html?cat=building' },
      { title: 'تینر و حلال', href: 'shop.html?cat=building' },
      { title: 'چسب و پرایمر', href: 'shop.html?cat=building' },
      { title: 'بتونه و آستری', href: 'shop.html?cat=building' }
    ]
  },
  {
    slug: 'art', title: 'بخش هنری', image: 'cat-art.webp',
    desc: 'رنگ اکریلیک و روغن، رزین اپوکسی، مواد سفالگری و افکت‌های حرفه‌ای برای هنرمندان.',
    children: [
      { title: 'رنگ اکریلیک', href: 'shop.html?cat=art' },
      { title: 'رنگ روغن', href: 'shop.html?cat=art' },
      { title: 'رزین اپوکسی هنری', href: 'shop.html?cat=art' },
      { title: 'سفالگری و مجسمه‌سازی', href: 'shop.html?cat=art' },
      { title: 'افکت و وارنیش', href: 'shop.html?cat=art' }
    ]
  },
  {
    slug: 'patina', title: 'پتینه', image: 'cat-patina.webp',
    desc: 'تکسچر، میکروسمنت، رنگ‌های دکوراتیو، ورق طلا و تثبیت‌کننده‌های پتینه‌کاری.',
    children: [
      { title: 'تکسچرها', href: 'shop.html?cat=patina' },
      { title: 'میکروسمنت', href: 'shop.html?cat=patina' },
      { title: 'رنگ‌های دکوراتیو', href: 'shop.html?cat=patina' },
      { title: 'ورق طلا و نقره', href: 'shop.html?cat=patina' },
      { title: 'تثبیت‌کننده', href: 'shop.html?cat=patina' }
    ]
  },
  {
    slug: 'stencil', title: 'شابلون و تابلو', image: 'cat-stencil.webp',
    desc: 'شابلون ماندالا، داماسک، حروف کالیگرافی، بوم نقاشی و بیس‌های چوبی.',
    children: [
      { title: 'شابلون', href: 'shop.html?cat=stencil' },
      { title: 'ماندالا و داماسک', href: 'shop.html?cat=stencil' },
      { title: 'بوم نقاشی', href: 'shop.html?cat=stencil' },
      { title: 'بیس چوبی', href: 'shop.html?cat=stencil' },
      { title: 'حروف کالیگرافی', href: 'shop.html?cat=stencil' }
    ]
  },
  {
    slug: 'tools', title: 'ابزارها', image: 'cat-tools.webp',
    desc: 'غلطک، قلم‌مو، لیسه و کاردک، ماله، سنباده و ابزارهای برقی حرفه‌ای.',
    children: [
      { title: 'غلطک', href: 'shop.html?cat=tools' },
      { title: 'قلم‌مو', href: 'shop.html?cat=tools' },
      { title: 'لیسه و کاردک', href: 'shop.html?cat=tools' },
      { title: 'ماله', href: 'shop.html?cat=tools' },
      { title: 'سنباده', href: 'shop.html?cat=tools' }
    ]
  }
];

const BRANDS = [
  { slug: 'rich', title: 'ریچ', image: 'brand-rich.webp' },
  { slug: 'sandora', title: 'ساندورا', image: 'brand-sandora.webp' },
  { slug: 'karen', title: 'کارن', image: 'brand-other.webp' },
  { slug: 'mako', title: 'ماکو', image: 'brand-mako.webp' },
  { slug: 'sodaco', title: 'سوداکو', image: 'brand-monomart.webp' },
  { slug: 'pars', title: 'پارس آرتیست', image: 'brand-pars.webp' },
  { slug: 'rando', title: 'راندو', image: 'brand-other.webp' },
  { slug: 'boomiran', title: 'بومیران', image: 'brand-boomiran.webp' },
  { slug: 'vilko', title: 'ویلکو', image: 'brand-other.webp' }
];

/* ---------------------------------- محصولات ---------------------------------- */
/* group: sale | best | new | shop   |   cats: اسلاگ دسته‌بندی‌ها  */
const products = [
  /* ---------- فروش ویژه ---------- */
  {
    id: 'p1', group: 'sale', title: 'کاردک استیل ۳ عددی ویلکو', cat: 'ابزارها، لیسه و کاردک',
    cats: ['tools'], brand: 'vilko', price: 590000, old: 790000, discount: 25, rating: 4.5, reviews: 12,
    image: 'sale-kardak.webp', gallery: ['sale-kardak.webp', 'promo-kardak.webp', 'popular-roller.webp'],
    sku: 'RS-1001', stock: 18, date: '2026-09-02', sold: 84,
    short: 'ست کاردک استیل حرفه‌ای در سه اندازه مختلف، مناسب پتینه‌کاری، میکروسمنت و اجرای تکسچر.',
    specs: [['جنس', 'استیل ضدزنگ'], ['تعداد در بسته', '۳ عدد'], ['کاربرد', 'پتینه، میکروسمنت، کناف'], ['برند', 'ویلکو'], ['کشور سازنده', 'ایران']]
  },
  {
    id: 'p2', group: 'sale', title: 'تار عنکبوتی ۵۰۰ گرمی گلد ساندورا', cat: 'رنگ‌های دکوراتیو، عنکبوتی',
    cats: ['patina'], brand: 'sandora', price: 1220000, old: 1848000, discount: 34, rating: 5, reviews: 26,
    image: 'sale-spider-gold.webp', gallery: ['sale-spider-gold.webp', 'sale-spider.webp'],
    sku: 'RS-1002', stock: 9, date: '2026-08-21', sold: 132,
    short: 'رنگ دکوراتیو تار عنکبوتی با افکت متالیک طلایی؛ پوشش‌دهی بالا و اجرای آسان روی دیوار.',
    specs: [['وزن', '۵۰۰ گرم'], ['رنگ', 'طلایی (Gold)'], ['پوشش‌دهی', '۶ تا ۸ متر مربع'], ['برند', 'ساندورا'], ['پایه', 'آب‌پایه']]
  },
  {
    id: 'p3', group: 'sale', title: 'تار عنکبوتی ۵۰۰ گرمی ته بنفش ساندورا', cat: 'رنگ‌های دکوراتیو، عنکبوتی',
    cats: ['patina'], brand: 'sandora', price: 1220000, old: 1848000, discount: 34, rating: 4.5, reviews: 8,
    image: 'sale-spider.webp', gallery: ['sale-spider.webp', 'sale-spider-gold.webp'],
    sku: 'RS-1003', stock: 14, date: '2026-08-21', sold: 61,
    short: 'افکت تار عنکبوتی با ته‌رنگ بنفش؛ انتخابی متفاوت برای دیوار شاخص نشیمن و لابی.',
    specs: [['وزن', '۵۰۰ گرم'], ['رنگ', 'ته بنفش'], ['پوشش‌دهی', '۶ تا ۸ متر مربع'], ['برند', 'ساندورا'], ['پایه', 'آب‌پایه']]
  },
  {
    id: 'p4', group: 'sale', title: 'تار عنکبوتی ۵۰۰ گرمی ته آبی ساندورا', cat: 'رنگ‌های دکوراتیو، عنکبوتی',
    cats: ['patina'], brand: 'sandora', price: 1220000, old: 1848000, discount: 34, rating: 4, reviews: 5,
    image: 'sale-spider.webp', gallery: ['sale-spider.webp', 'sale-spider-gold.webp'],
    sku: 'RS-1004', stock: 11, date: '2026-08-21', sold: 45,
    short: 'رنگ دکوراتیو عنکبوتی با ته‌رنگ آبی؛ افکتی مدرن و عمق‌دار روی سطح دیوار.',
    specs: [['وزن', '۵۰۰ گرم'], ['رنگ', 'ته آبی'], ['پوشش‌دهی', '۶ تا ۸ متر مربع'], ['برند', 'ساندورا'], ['پایه', 'آب‌پایه']]
  },
  {
    id: 'p5', group: 'sale', title: 'تار عنکبوتی ۵۰۰ گرمی سیلور ساندورا', cat: 'رنگ‌های دکوراتیو، عنکبوتی',
    cats: ['patina'], brand: 'sandora', price: 1220000, old: 1848000, discount: 34, rating: 4.5, reviews: 9,
    image: 'sale-spider.webp', gallery: ['sale-spider.webp', 'sale-spider-gold.webp'],
    sku: 'RS-1005', stock: 16, date: '2026-08-21', sold: 72,
    short: 'افکت عنکبوتی سیلور با درخشش نقره‌ای؛ هماهنگ با سبک‌های مینیمال و مدرن.',
    specs: [['وزن', '۵۰۰ گرم'], ['رنگ', 'نقره‌ای (Silver)'], ['پوشش‌دهی', '۶ تا ۸ متر مربع'], ['برند', 'ساندورا'], ['پایه', 'آب‌پایه']]
  },
  {
    id: 'p6', group: 'sale', title: 'رنگ مولتی سورفیس ریچ کد 2234', cat: 'مولتی سورفیس‌ها',
    cats: ['art', 'building'], brand: 'rich', price: 690000, old: 830000, discount: 17, rating: 5, reviews: 18,
    image: 'sale-2234.webp', gallery: ['sale-2234.webp', 'sale-2200.webp', 'sale-2172.webp'],
    sku: 'RS-1006', stock: 24, date: '2026-07-30', sold: 118,
    short: 'رنگ مولتی سورفیس ریچ با چسبندگی بالا روی چوب، فلز، شیشه و پلاستیک؛ بدون نیاز به آستری.',
    specs: [['حجم', '۱ کیلوگرم'], ['کد رنگ', '2234'], ['سطوح', 'چوب، فلز، شیشه، پلاستیک'], ['برند', 'ریچ'], ['پوشش', 'نیم‌براق']]
  },
  {
    id: 'p7', group: 'sale', title: 'رنگ مولتی سورفیس ریچ کد 2200', cat: 'مولتی سورفیس‌ها',
    cats: ['art', 'building'], brand: 'rich', price: 690000, old: 830000, discount: 17, rating: 4.5, reviews: 7,
    image: 'sale-2200.webp', gallery: ['sale-2200.webp', 'sale-2234.webp', 'sale-2172.webp'],
    sku: 'RS-1007', stock: 20, date: '2026-07-30', sold: 66,
    short: 'مولتی سورفیس با پوشش‌دهی عالی و خشک شدن سریع؛ مناسب کارهای هنری و بازسازی.',
    specs: [['حجم', '۱ کیلوگرم'], ['کد رنگ', '2200'], ['سطوح', 'چوب، فلز، شیشه'], ['برند', 'ریچ'], ['پوشش', 'نیم‌براق']]
  },
  {
    id: 'p8', group: 'sale', title: 'رنگ مولتی سورفیس ریچ کد 2172', cat: 'مولتی سورفیس‌ها',
    cats: ['art', 'building'], brand: 'rich', price: 690000, old: 830000, discount: 17, rating: 4, reviews: 4,
    image: 'sale-2172.webp', gallery: ['sale-2172.webp', 'sale-2200.webp', 'sale-2234.webp'],
    sku: 'RS-1008', stock: 8, date: '2026-07-30', sold: 39,
    short: 'رنگ همه‌کاره ریچ برای سطوح سخت؛ مقاوم در برابر رطوبت و خط‌وخش.',
    specs: [['حجم', '۱ کیلوگرم'], ['کد رنگ', '2172'], ['سطوح', 'چوب، فلز، شیشه'], ['برند', 'ریچ'], ['پوشش', 'نیم‌براق']]
  },

  /* ---------- پرفروش‌ترین‌ها ---------- */
  {
    id: 'p9', group: 'best', title: 'ماژیک بیسیک دو سر راندو کد 7545', cat: 'ماژیک',
    cats: ['art'], brand: 'rando', price: 100000, rating: 4.5, reviews: 31,
    image: 'best-marker-7545.webp', gallery: ['best-marker-7545.webp', 'best-marker-7533.webp'],
    sku: 'RS-1009', stock: 60, date: '2026-06-11', sold: 340,
    short: 'ماژیک دو سر با نوک باریک و پهن، مناسب طراحی، اسکچ و کارهای گرافیکی.',
    specs: [['نوع', 'ماژیک الکلی دو سر'], ['کد رنگ', '7545'], ['برند', 'راندو'], ['کاربرد', 'طراحی و اسکچ']]
  },
  {
    id: 'p10', group: 'best', title: 'تکسچر سرامیک کارن ۱ کیلویی', cat: 'تکسچرها',
    cats: ['patina'], brand: 'karen', price: 590000, rating: 5, reviews: 42,
    image: 'best-ceramic.webp', gallery: ['best-ceramic.webp', 'best-ceramic-5.webp'],
    sku: 'RS-1010', stock: 22, date: '2026-05-18', sold: 410,
    short: 'تکسچر سرامیک کارن با بافت نرم و پوشش‌دهی بالا؛ پایه‌ی بسیاری از پروژه‌های پتینه.',
    specs: [['وزن', '۱ کیلوگرم'], ['بافت', 'سرامیک'], ['پوشش‌دهی', '۴ تا ۶ متر مربع'], ['برند', 'کارن'], ['پایه', 'آب‌پایه']]
  },
  {
    id: 'p11', group: 'best', title: 'تکسچر فاین استون کارن ۱ کیلویی', cat: 'تکسچرها',
    cats: ['patina'], brand: 'karen', price: 400000, rating: 4.5, reviews: 19,
    image: 'best-fine-stone.webp', gallery: ['best-fine-stone.webp', 'best-sand-stone.webp'],
    sku: 'RS-1011', stock: 30, date: '2026-05-18', sold: 275,
    short: 'تکسچر سنگی ریزبافت برای ایجاد سطحی شبیه سنگ طبیعی؛ مناسب دیوار و ستون.',
    specs: [['وزن', '۱ کیلوگرم'], ['بافت', 'سنگ ریز'], ['پوشش‌دهی', '۴ تا ۶ متر مربع'], ['برند', 'کارن']]
  },
  {
    id: 'p12', group: 'best', title: 'غلطک فومی ۱۰ سانت ماکو', cat: 'غلطک',
    cats: ['tools'], brand: 'mako', price: 370000, rating: 4, reviews: 15,
    image: 'best-roller.webp', gallery: ['best-roller.webp', 'popular-roller.webp'],
    sku: 'RS-1012', stock: 45, date: '2026-04-02', sold: 520,
    short: 'غلطک فومی با دسته ارگونومیک برای پوشش‌های یکدست روی سطوح صاف و پتینه.',
    specs: [['عرض', '۱۰ سانتی‌متر'], ['جنس رول', 'فوم'], ['برند', 'ماکو'], ['کاربرد', 'رنگ و پوشش پتینه']]
  },
  {
    id: 'p13', group: 'best', title: 'تکسچر سرامیک کارن ۵ کیلویی', cat: 'تکسچرها',
    cats: ['patina'], brand: 'karen', price: 2250000, rating: 5, reviews: 23,
    image: 'best-ceramic-5.webp', gallery: ['best-ceramic-5.webp', 'best-ceramic.webp'],
    sku: 'RS-1013', stock: 6, date: '2026-05-18', sold: 148,
    short: 'بسته اقتصادی ۵ کیلویی تکسچر سرامیک؛ مناسب پروژه‌های بزرگ و اجرای پیمانکاری.',
    specs: [['وزن', '۵ کیلوگرم'], ['بافت', 'سرامیک'], ['پوشش‌دهی', '۲۰ تا ۳۰ متر مربع'], ['برند', 'کارن']]
  },
  {
    id: 'p14', group: 'best', title: 'ماژیک بیسیک دو سر راندو کد 7533', cat: 'ماژیک',
    cats: ['art'], brand: 'rando', price: 100000, rating: 4.5, reviews: 12,
    image: 'best-marker-7533.webp', gallery: ['best-marker-7533.webp', 'best-marker-7545.webp'],
    sku: 'RS-1014', stock: 52, date: '2026-06-11', sold: 288,
    short: 'ماژیک راندو کد 7533 با جوهر پوشاننده و خشک‌شدن سریع.',
    specs: [['نوع', 'ماژیک الکلی دو سر'], ['کد رنگ', '7533'], ['برند', 'راندو']]
  },
  {
    id: 'p15', group: 'best', title: 'لاک افکت کارن ۲۵۰ میل', cat: 'افکت‌ها',
    cats: ['patina', 'art'], brand: 'karen', price: 520000, rating: 4.5, reviews: 16,
    image: 'best-lacquer.webp', gallery: ['best-lacquer.webp', 'best-fine-stone.webp'],
    sku: 'RS-1015', stock: 17, date: '2026-03-15', sold: 205,
    short: 'لاک محافظ افکت با چسبندگی بالا؛ تثبیت‌کننده رنگ و پوشش پتینه در برابر رطوبت.',
    specs: [['حجم', '۲۵۰ میلی‌لیتر'], ['پوشش', 'براق'], ['برند', 'کارن'], ['نوع', 'تثبیت‌کننده']]
  },
  {
    id: 'p16', group: 'best', title: 'تکسچر سند استون کارن ۱ کیلویی', cat: 'تکسچرها',
    cats: ['patina'], brand: 'karen', price: 400000, rating: 4, reviews: 11,
    image: 'best-sand-stone.webp', gallery: ['best-sand-stone.webp', 'best-fine-stone.webp'],
    sku: 'RS-1016', stock: 26, date: '2026-05-18', sold: 190,
    short: 'تکسچر سنگ شنی با بافت درشت‌تر؛ مناسب نمای داخلی و ستون‌های دکوراتیو.',
    specs: [['وزن', '۱ کیلوگرم'], ['بافت', 'سنگ شنی'], ['پوشش‌دهی', '۴ تا ۶ متر مربع'], ['برند', 'کارن']]
  },

  /* ---------- جدیدترین‌ها ---------- */
  {
    id: 'p17', group: 'new', title: 'اسفنج مصنوعی دونه ریز ماکو', cat: 'ابزارها، ابزار پتینه',
    cats: ['tools'], brand: 'mako', price: 750000, rating: 4, reviews: 6,
    image: 'new-sponge.webp', gallery: ['new-sponge.webp', 'cat-patina.webp'],
    sku: 'RS-1017', stock: 33, date: '2026-10-28', sold: 24,
    short: 'اسفنج مصنوعی دونه ریز برای تکنیک‌های پتینه، اسفنجی و ایجاد بافت نرم.',
    specs: [['جنس', 'اسفنج مصنوعی'], ['دانه‌بندی', 'ریز'], ['برند', 'ماکو'], ['کاربرد', 'پتینه و بافت']]
  },
  {
    id: 'p18', group: 'new', title: 'مولتی سورفیس نقره‌ای ۱۲۵ میل سوداکو', cat: 'مولتی سورفیس‌ها',
    cats: ['art'], brand: 'sodaco', price: 490000, rating: 4.5, reviews: 3,
    image: 'new-silver.webp', gallery: ['new-silver.webp', 'new-gold.webp'],
    sku: 'RS-1018', stock: 19, date: '2026-10-25', sold: 18,
    short: 'رنگ مولتی سورفیس متالیک نقره‌ای سوداکو با درخشش بالا؛ ایده‌آل برای کارهای هنری.',
    specs: [['حجم', '۱۲۵ میلی‌لیتر'], ['رنگ', 'نقره‌ای متالیک'], ['برند', 'سوداکو'], ['سطوح', 'همه سطوح']]
  },
  {
    id: 'p19', group: 'new', title: 'مولتی سورفیس طلایی ۱۲۵ میل سوداکو', cat: 'مولتی سورفیس‌ها',
    cats: ['art'], brand: 'sodaco', price: 490000, rating: 5, reviews: 4,
    image: 'new-gold.webp', gallery: ['new-gold.webp', 'new-silver.webp'],
    sku: 'RS-1019', stock: 21, date: '2026-10-25', sold: 22,
    short: 'مولتی سورفیس متالیک طلایی با پوشش یکدست و مقاوم؛ مناسب تذهیب و دکور.',
    specs: [['حجم', '۱۲۵ میلی‌لیتر'], ['رنگ', 'طلایی متالیک'], ['برند', 'سوداکو'], ['سطوح', 'همه سطوح']]
  },
  {
    id: 'p20', group: 'new', title: 'بوم نیم دایره قطر ۱۰۰', cat: 'بوم‌ها',
    cats: ['stencil'], brand: 'boomiran', price: 1950000, rating: 4.5, reviews: 2,
    image: 'new-canvas-100.webp', gallery: ['new-canvas-100.webp', 'new-canvas-70.webp'],
    sku: 'RS-1020', stock: 5, date: '2026-10-20', sold: 9,
    short: 'بوم نیم‌دایره با پارچه کتان و زیرسازی آماده رنگ‌آمیزی؛ سایز شاخص برای دکور دیوار.',
    specs: [['قطر', '۱۰۰ سانتی‌متر'], ['جنس', 'کتان'], ['برند', 'بومیران'], ['نوع', 'نیم‌دایره']]
  },
  {
    id: 'p21', group: 'new', title: 'بوم نیم دایره قطر ۷۰', cat: 'بوم‌ها',
    cats: ['stencil'], brand: 'boomiran', price: 1110000, rating: 4, reviews: 1,
    image: 'new-canvas-70.webp', gallery: ['new-canvas-70.webp', 'new-canvas-60.webp'],
    sku: 'RS-1021', stock: 8, date: '2026-10-20', sold: 12,
    short: 'بوم نیم‌دایره سایز متوسط، مناسب تابلوهای دکوراتیو و ترکیب‌بندی دو تکه.',
    specs: [['قطر', '۷۰ سانتی‌متر'], ['جنس', 'کتان'], ['برند', 'بومیران'], ['نوع', 'نیم‌دایره']]
  },
  {
    id: 'p22', group: 'new', title: 'بوم نیم دایره قطر ۶۰', cat: 'بوم‌ها',
    cats: ['stencil'], brand: 'boomiran', price: 910000, rating: 4, reviews: 1,
    image: 'new-canvas-60.webp', gallery: ['new-canvas-60.webp', 'new-canvas-70.webp'],
    sku: 'RS-1022', stock: 10, date: '2026-10-20', sold: 15,
    short: 'بوم نیم‌دایره کوچک با بافت یکدست؛ مناسب کارهای هنری و هدیه.',
    specs: [['قطر', '۶۰ سانتی‌متر'], ['جنس', 'کتان'], ['برند', 'بومیران'], ['نوع', 'نیم‌دایره']]
  },

  /* ---------- سایر محصولات فروشگاه ---------- */
  {
    id: 'p23', group: 'shop', title: 'رنگ اکریلیک هنری سوداکو ۲۵۰ میل', cat: 'رنگ اکریلیک',
    cats: ['art'], brand: 'sodaco', price: 380000, old: 450000, discount: 16, rating: 4.5, reviews: 14,
    image: 'popular-acrylic.webp', gallery: ['popular-acrylic.webp', 'popular-oil.webp'],
    sku: 'RS-1023', stock: 40, date: '2026-09-14', sold: 96,
    short: 'اکریلیک هنری با پیگمنت بالا و پوشانندگی عالی؛ رقیق‌شونده با آب.',
    specs: [['حجم', '۲۵۰ میلی‌لیتر'], ['پایه', 'آب‌پایه'], ['برند', 'سوداکو'], ['پوشش', 'مات']]
  },
  {
    id: 'p24', group: 'shop', title: 'رنگ روغن هنری پارس آرتیست ۳۷ میل', cat: 'رنگ روغن‌ها',
    cats: ['art'], brand: 'pars', price: 265000, rating: 4.5, reviews: 21,
    image: 'popular-oil.webp', gallery: ['popular-oil.webp', 'popular-acrylic.webp'],
    sku: 'RS-1024', stock: 55, date: '2026-08-30', sold: 174,
    short: 'رنگ روغن با روغن بزرک تصفیه‌شده؛ زمان خشک‌شدن متعادل و ترکیب‌پذیری بالا.',
    specs: [['حجم', '۳۷ میلی‌لیتر'], ['پایه', 'روغنی'], ['برند', 'پارس آرتیست'], ['پوشش', 'براق']]
  },
  {
    id: 'p25', group: 'shop', title: 'قلم‌مو پتینه پارس آرتیست سایز ۱۲', cat: 'قلم‌مو',
    cats: ['tools'], brand: 'pars', price: 320000, rating: 4, reviews: 8,
    image: 'popular-brush.webp', gallery: ['popular-brush.webp', 'cat-tools.webp'],
    sku: 'RS-1025', stock: 37, date: '2026-08-11', sold: 143,
    short: 'قلم‌مو با موی مصنوعی مقاوم، مناسب اجرای پتینه، وارنیش و کارهای هنری.',
    specs: [['سایز', '۱۲'], ['جنس مو', 'مصنوعی'], ['برند', 'پارس آرتیست']]
  },
  {
    id: 'p26', group: 'shop', title: 'ماله پتینه استیل ۲۵ سانت', cat: 'ماله',
    cats: ['tools'], brand: 'mako', price: 480000, rating: 4.5, reviews: 10,
    image: 'popular-male.webp', gallery: ['popular-male.webp', 'popular-roller.webp'],
    sku: 'RS-1026', stock: 15, date: '2026-07-19', sold: 87,
    short: 'ماله استیل با لبه صیقلی برای اجرای یکدست میکروسمنت و تکسچر.',
    specs: [['عرض', '۲۵ سانتی‌متر'], ['جنس', 'استیل'], ['برند', 'ماکو']]
  },
  {
    id: 'p27', group: 'shop', title: 'میکروسمنت آماده کارن ۵ کیلویی', cat: 'میکروسمنت',
    cats: ['patina'], brand: 'karen', price: 2450000, old: 2900000, discount: 15, rating: 5, reviews: 30,
    image: 'popular-micro.webp', gallery: ['popular-micro.webp', 'popular-male.webp'],
    sku: 'RS-1027', stock: 7, date: '2026-09-08', sold: 121,
    short: 'میکروسمنت دو جزئی آماده اجرا برای دیوار و کف؛ سطحی یکپارچه، مقاوم و بدون درز.',
    specs: [['وزن', '۵ کیلوگرم'], ['اجزا', 'دو جزئی'], ['پوشش‌دهی', '۳ تا ۵ متر مربع'], ['برند', 'کارن']]
  },
  {
    id: 'p28', group: 'shop', title: 'رزین اپوکسی هنری شفاف ۱ کیلویی', cat: 'رزین اپوکسی',
    cats: ['art'], brand: 'karen', price: 3200000, old: 3800000, discount: 16, rating: 5, reviews: 25,
    image: 'popular-resin.webp', gallery: ['popular-resin.webp', 'popular-acrylic.webp'],
    sku: 'RS-1028', stock: 4, date: '2026-09-22', sold: 98,
    short: 'رزین اپوکسی شفاف با زمان کار مناسب و شفافیت بالا؛ مناسب کارهای هنری و پوشش نهایی.',
    specs: [['وزن', '۱ کیلوگرم'], ['نسبت ترکیب', '۲ به ۱'], ['زمان خشک', '۲۴ ساعت'], ['برند', 'کارن']]
  },
  {
    id: 'p29', group: 'shop', title: 'شابلون ماندالا طرح اختصاصی ۴۰×۴۰', cat: 'شابلون',
    cats: ['stencil'], brand: 'boomiran', price: 590000, rating: 4.5, reviews: 13,
    image: 'cat-stencil.webp', gallery: ['cat-stencil.webp', 'new-canvas-70.webp'],
    sku: 'RS-1029', stock: 23, date: '2026-06-25', sold: 156,
    short: 'شابلون ماندالا با برش لیزری دقیق و فیلم مقاوم؛ قابل استفاده چندباره.',
    specs: [['ابعاد', '۴۰×۴۰ سانتی‌متر'], ['جنس', 'فیلم پت'], ['برند', 'بومیران'], ['نوع', 'ماندالا']]
  },
  {
    id: 'p30', group: 'shop', title: 'چسب کاغذی ساختمانی ۵ کیلویی', cat: 'چسب',
    cats: ['building'], brand: 'rich', price: 950000, old: 1150000, discount: 17, rating: 4, reviews: 9,
    image: 'cat-building.webp', gallery: ['cat-building.webp', 'sale-2200.webp'],
    sku: 'RS-1030', stock: 12, date: '2026-04-28', sold: 74,
    short: 'چسب کاغذی مخصوص زیرسازی دیوار؛ چسبندگی بالا و اجرای آسان قبل از رنگ.',
    specs: [['وزن', '۵ کیلوگرم'], ['کاربرد', 'زیرسازی دیوار'], ['برند', 'ریچ']]
  },
  {
    id: 'p31', group: 'shop', title: 'ابزار سفالگری حرفه‌ای ۸ پارچه', cat: 'سفالگری',
    cats: ['art'], brand: 'pars', price: 890000, rating: 4.5, reviews: 7,
    image: 'popular-pottery.webp', gallery: ['popular-pottery.webp', 'popular-roller.webp'],
    sku: 'RS-1031', stock: 9, date: '2026-05-09', sold: 41,
    short: 'ست ابزار سفالگری شامل قالب، کاردک و ابزارهای شکل‌دهی؛ مناسب هنرجویان و حرفه‌ای‌ها.',
    specs: [['تعداد', '۸ پارچه'], ['جنس', 'استیل و چوب'], ['برند', 'پارس آرتیست']]
  },
  {
    id: 'p32', group: 'shop', title: 'رنگ مولتی سورفیس ریچ ۱.۷۵ کیلویی کد 2240', cat: 'مولتی سورفیس‌ها',
    cats: ['building', 'art'], brand: 'rich', price: 1150000, old: 1390000, discount: 17, rating: 4.5, reviews: 6,
    image: 'sale-2200.webp', gallery: ['sale-2200.webp', 'sale-2234.webp'],
    sku: 'RS-1032', stock: 13, date: '2026-03-08', sold: 52,
    short: 'مولتی سورفیس با حجم اقتصادی؛ پوشش مقاوم برای سطوح داخلی و خارجی.',
    specs: [['حجم', '۱.۷۵ کیلوگرم'], ['کد رنگ', '2240'], ['برند', 'ریچ']]
  }
];

/* ---------------------------------- ابزارک ها ---------------------------------- */
const $ = (selector, root = document) => root.querySelector(selector);
const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];
const imagePath = name => `assets/images/${name}`;
const getProduct = id => products.find(product => product.id === id);
const getCategory = slug => CATEGORIES.find(category => category.slug === slug);
const formatPrice = value => toFa(Number(value).toLocaleString('en-US').replaceAll(',', '.'));
const normalizeText = value => toFa(String(value).toLowerCase().trim())
  .replaceAll('ي', 'ی').replaceAll('ك', 'ک').replaceAll('\u200c', ' ').replace(/\s+/g, ' ');
const FA_DIGITS = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
const toFa = value => String(value).replace(/\d/g, digit => FA_DIGITS[digit]);
const toEn = value => String(value).replace(/[۰-۹]/g, digit => FA_DIGITS.indexOf(digit));
const productUrl = id => `product.html?id=${id}`;

function loadState(key, fallback) {
  try { return JSON.parse(localStorage.getItem(key)) ?? fallback; }
  catch { return fallback; }
}

let cart = loadState('sadeghi-cart', {});
let wishlist = new Set(loadState('sadeghi-wishlist', []));
let toastTimer;

function saveState() {
  try {
    localStorage.setItem('sadeghi-cart', JSON.stringify(cart));
    localStorage.setItem('sadeghi-wishlist', JSON.stringify([...wishlist]));
  } catch { /* در پیش‌نمایش ممکن است localStorage در دسترس نباشد */ }
}

function showToast(message, icon = 'i-check') {
  const toast = $('#toast');
  if (!toast) return;
  toast.querySelector('use').setAttribute('href', `#${icon}`);
  $('span', toast).textContent = message;
  toast.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => toast.classList.remove('show'), 3200);
}

/* ---------------------------------- کارت محصول ---------------------------------- */
function productCard(product, options = {}) {
  const liked = wishlist.has(product.id);
  const className = options.list ? 'product-card is-list' : 'product-card';
  const date = product.date || '2026-01-01';
  const isNew = (Date.now() - new Date(date).getTime()) < 1000 * 60 * 60 * 24 * 70;
  return `
    <article class="${className}" data-product-id="${product.id}"
      data-cat="${(product.cats || []).join(' ')}"
      data-brand="${product.brand || ''}"
      data-price="${product.price}"
      data-rating="${product.rating || 0}"
      data-sale="${product.discount ? 1 : 0}"
      data-stock="${product.stock || 0}"
      data-date="${date}"
      data-sold="${product.sold || 0}"
      data-title="${normalizeText(product.title)}">
      <div class="product-media">
        <a href="${productUrl(product.id)}" aria-label="مشاهده ${product.title}">
          <img src="${imagePath(product.image)}" alt="${product.title}" loading="lazy">
        </a>
        ${product.discount ? `<span class="discount-badge" title="فروش ویژه"><span class="db-pct">${toFa(product.discount)}٪</span><span class="db-label">فروش ویژه</span></span>` : ''}
        ${!product.discount && isNew ? '<span class="product-flag is-new">جدید</span>' : ''}
        ${product.stock && product.stock <= 5 ? '<span class="product-flag is-low">آخرین موجودی</span>' : ''}
        <button class="wishlist-button ${liked ? 'active' : ''}" type="button" data-wish="${product.id}" aria-label="افزودن به علاقه‌مندی" aria-pressed="${liked}"><svg><use href="#i-heart"/></svg></button>
        <button class="add-to-cart" type="button" data-add="${product.id}"><svg><use href="#i-bag"/></svg>خرید محصول</button>
      </div>
      <div class="product-info">
        <h3 class="product-title"><a href="${productUrl(product.id)}">${toFa(product.title)}</a></h3>
        <div class="product-category">${product.cat}</div>
        <div class="product-price">
          <span class="old-price">${product.old ? `${formatPrice(product.old)} تومان` : ''}</span>
          <span class="current-price">${formatPrice(product.price)} <small>تومان</small></span>
        </div>
        ${options.list ? `<div class="product-excerpt">${product.short || ''}</div>
          <div class="product-list-actions"><button type="button" class="btn-ghost" data-add="${product.id}">افزودن به سبد</button><button type="button" class="btn-ghost" data-wish="${product.id}">علاقه‌مندی</button></div>` : ''}
      </div>
    </article>`;
}

function renderCards(container, list) {
  if (!container) return;
  container.innerHTML = list.map(product => productCard(product)).join('');
}

/* ---------------------------------- رندر صفحه اصلی ---------------------------------- */
function renderHomePanels() {
  ['sale', 'best', 'new'].forEach(group => {
    const track = $(`#${group}-products`);
    if (track) track.innerHTML = products.filter(product => product.group === group).map(product => productCard(product)).join('');
  });
}

function renderBrands() {
  const track = $('#brand-track');
  if (!track) return;
  const items = BRANDS.map(brand => [brand.image, brand.title]);
  track.innerHTML = [...items, ...items].map(([image, name]) =>
    `<a class="brand-item" href="shop.html" aria-label="${name}"><img src="${imagePath(image)}" alt="${name}" loading="lazy"></a>`
  ).join('');
}

/* ---------------------------------- اسلایدر هیرو ---------------------------------- */
let heroIndex = 0;
let heroTimer;
let heroTouchStart = 0;

function showSlide(index) {
  const slides = $$('.hero-slide');
  if (!slides.length) return;
  heroIndex = (index + slides.length) % slides.length;
  slides.forEach((slide, i) => slide.classList.toggle('active', i === heroIndex));
  $$('.hero-dots button').forEach((dot, i) => dot.classList.toggle('active', i === heroIndex));
}
function startHero() {
  if (!$$('.hero-slide').length) return;
  clearInterval(heroTimer);
  heroTimer = setInterval(() => showSlide(heroIndex + 1), 5000);
}
function initHero() {
  const hero = $('.hero-slider');
  if (!hero) return;
  $('.hero-prev')?.addEventListener('click', () => { showSlide(heroIndex - 1); startHero(); });
  $('.hero-next')?.addEventListener('click', () => { showSlide(heroIndex + 1); startHero(); });
  $$('.hero-dots button').forEach(dot => dot.addEventListener('click', () => { showSlide(Number(dot.dataset.slideTo)); startHero(); }));
  hero.addEventListener('mouseenter', () => clearInterval(heroTimer));
  hero.addEventListener('mouseleave', startHero);
  hero.addEventListener('touchstart', event => { heroTouchStart = event.changedTouches[0].clientX; }, { passive: true });
  hero.addEventListener('touchend', event => {
    const delta = event.changedTouches[0].clientX - heroTouchStart;
    if (Math.abs(delta) > 45) { showSlide(heroIndex + (delta > 0 ? 1 : -1)); startHero(); }
  }, { passive: true });
  startHero();
}

/* ---------------------------------- جستجو ---------------------------------- */
function matchingProducts(query) {
  const normalized = normalizeText(query);
  if (!normalized) return [];
  return products.filter(product => normalizeText(`${product.title} ${product.cat} ${product.short || ''}`).includes(normalized)).slice(0, 7);
}
function renderSearch(box) {
  const input = $('input', box);
  const results = $('.search-results', box);
  if (!input || !results) return;
  const query = input.value;
  const matches = matchingProducts(query);
  if (!query.trim()) {
    results.innerHTML = `<div class="search-result-title">درخواست‌های محبوب</div><div class="popular-searches">
      <button type="button" data-search-term="مولتی سورفیس">مولتی سورفیس ریچ</button>
      <button type="button" data-search-term="ساندورا">تار عنکبوتی ساندورا</button>
      <button type="button" data-search-term="سوداکو">اکریلیک سوداکو</button>
      <button type="button" data-search-term="تکسچر">تکسچر کارن</button></div>`;
  } else if (matches.length) {
    results.innerHTML = `<div class="search-result-title">${toFa(matches.length)} نتیجه برای «${query.replace(/[<>]/g, '')}»</div>` + matches.map(product =>
      `<a class="search-result-item" href="${productUrl(product.id)}"><img src="${imagePath(product.image)}" alt=""><span>${product.title}</span><strong>${formatPrice(product.price)} تومان</strong></a>`
    ).join('');
  } else {
    results.innerHTML = '<div class="search-empty">محصولی با این عبارت پیدا نشد.</div>';
  }
  box.classList.add('open');
}
function closeSearches() { $$('.search-box.open').forEach(box => box.classList.remove('open')); }
function initSearch() {
  $$('[data-search-form]').forEach(form => {
    const box = form.closest('.search-box');
    const input = $('input', form);
    if (!box || !input) return;
    input.addEventListener('focus', () => renderSearch(box));
    input.addEventListener('input', () => renderSearch(box));
    form.addEventListener('submit', event => {
      event.preventDefault();
      const box2 = form.closest('.search-box');
      const value = input.value.trim();
      if (!value) { renderSearch(box2); return; }
      const matches = matchingProducts(value);
      if (matches.length) window.location.href = productUrl(matches[0].id);
      else showToast('محصولی با این عبارت پیدا نشد', 'i-search');
    });
  });
}

/* ---------------------------------- لایه ها (دراور/مودال) ---------------------------------- */
function closeLayers() {
  $$('.side-drawer.open').forEach(drawer => { drawer.classList.remove('open'); drawer.setAttribute('aria-hidden', 'true'); });
  $$('.modal.open').forEach(modal => { modal.classList.remove('open'); modal.setAttribute('aria-hidden', 'true'); });
  $('#page-overlay')?.classList.remove('active');
  document.body.classList.remove('no-scroll');
}
function showOverlay() {
  $('#page-overlay')?.classList.add('active');
  document.body.classList.add('no-scroll');
}
function openDrawer(id) {
  const drawer = $(`#${id}`);
  if (!drawer) return;
  closeLayers();
  drawer.classList.add('open');
  drawer.setAttribute('aria-hidden', 'false');
  showOverlay();
  $('.drawer-close', drawer)?.focus();
}
function openModal(id) {
  const modal = $(`#${id}`);
  if (!modal) return;
  closeLayers();
  modal.classList.add('open');
  modal.setAttribute('aria-hidden', 'false');
  showOverlay();
  $('.modal-close', modal)?.focus();
}

/* ---------------------------------- علاقه مندی ---------------------------------- */
function toggleWishlist(id) {
  const product = getProduct(id);
  if (!product) return;
  if (wishlist.has(id)) {
    wishlist.delete(id);
    showToast('محصول از علاقه‌مندی‌ها حذف شد', 'i-heart');
  } else {
    wishlist.add(id);
    showToast('محصول به علاقه‌مندی‌ها اضافه شد', 'i-heart');
  }
  saveState();
  updateWishlistUI();
  $$(`[data-wish="${id}"]`).forEach(button => {
    button.classList.toggle('active', wishlist.has(id));
    button.setAttribute('aria-pressed', wishlist.has(id));
  });
  if ($('#quick-modal.open') && $('.wishlist-view')) openWishlist();
}
function updateWishlistUI() {
  const counter = $('#wishlist-count');
  if (counter) counter.textContent = toFa(wishlist.size);
  $$('[data-wish]').forEach(button => {
    if (button.classList.contains('remove-wish')) return;
    const active = wishlist.has(button.dataset.wish);
    button.classList.toggle('active', active);
    button.setAttribute('aria-pressed', active);
  });
}
function openWishlist() {
  const selected = [...wishlist].map(getProduct).filter(Boolean);
  const content = $('#quick-modal-content');
  if (!content) return;
  content.innerHTML = `<div class="wishlist-view"><h2>لیست علاقه‌مندی‌ها</h2>${selected.length ? `<div class="wishlist-list">${selected.map(product =>
    `<div class="wishlist-row"><img src="${imagePath(product.image)}" alt="${product.title}"><a class="wishlist-name" href="${productUrl(product.id)}">${product.title}</a><strong>${formatPrice(product.price)} تومان</strong><button class="quick-add" data-add="${product.id}">افزودن به سبد</button><button class="remove-wish" data-wish="${product.id}" aria-label="حذف"><svg><use href="#i-trash"/></svg></button></div>`
  ).join('')}</div>` : '<div class="cart-empty"><svg><use href="#i-heart"/></svg><p>هنوز محصولی به علاقه‌مندی‌ها اضافه نکرده‌اید.</p><a class="quick-add" href="shop.html">مشاهده محصولات</a></div>'}</div>`;
  openModal('quick-modal');
}

/* ---------------------------------- سبد خرید ---------------------------------- */
function addToCart(id, quantity = 1) {
  const product = getProduct(id);
  if (!product) return;
  cart[id] = (cart[id] || 0) + quantity;
  saveState();
  updateCartUI();
  showToast(`«${product.title}» به سبد خرید اضافه شد`);
}
function changeCart(id, action) {
  if (!cart[id]) return;
  if (action === 'plus') cart[id]++;
  if (action === 'minus') cart[id]--;
  if (action === 'remove' || cart[id] <= 0) delete cart[id];
  saveState();
  updateCartUI();
}
function cartTotals() {
  return Object.entries(cart).reduce((totals, [id, quantity]) => {
    const product = getProduct(id);
    if (product) { totals.count += quantity; totals.price += product.price * quantity; }
    return totals;
  }, { count: 0, price: 0 });
}
function updateCartUI() {
  const totals = cartTotals();
  $$('.cart-count').forEach(counter => counter.textContent = toFa(totals.count));
  const total = $('#cart-total');
  if (total) total.textContent = `${formatPrice(totals.price)} تومان`;
  const items = $('#cart-items');
  if (!items) return;
  const entries = Object.entries(cart).filter(([id]) => getProduct(id));
  items.innerHTML = entries.length ? entries.map(([id, quantity]) => {
    const product = getProduct(id);
    return `<div class="cart-item"><img src="${imagePath(product.image)}" alt="${product.title}"><div><h3>${product.title}</h3><div class="cart-item-price">${formatPrice(product.price * quantity)} تومان</div><div class="cart-item-actions"><button data-cart-action="plus" data-id="${id}" aria-label="افزایش"><svg><use href="#i-plus"/></svg></button><span>${toFa(quantity)}</span><button data-cart-action="minus" data-id="${id}" aria-label="کاهش"><svg><use href="#i-minus"/></svg></button></div></div><button class="remove-cart-item" data-cart-action="remove" data-id="${id}" aria-label="حذف محصول"><svg><use href="#i-trash"/></svg></button></div>`;
  }).join('') : '<div class="cart-empty"><svg><use href="#i-bag"/></svg><p>سبد خرید شما خالی است.</p><a class="quick-add" href="shop.html">مشاهده محصولات</a></div>';
}

/* ---------------------------------- رویدادهای عمومی ---------------------------------- */
function initEvents() {
  document.addEventListener('click', event => {
    const add = event.target.closest('[data-add]');
    if (add) { event.preventDefault(); event.stopPropagation(); addToCart(add.dataset.add); return; }

    const wish = event.target.closest('[data-wish]');
    if (wish) { event.preventDefault(); event.stopPropagation(); toggleWishlist(wish.dataset.wish); return; }

    const cartAction = event.target.closest('[data-cart-action]');
    if (cartAction) { changeCart(cartAction.dataset.id, cartAction.dataset.cartAction); return; }

    const searchTerm = event.target.closest('[data-search-term]');
    if (searchTerm) {
      const box = searchTerm.closest('.search-box');
      const input = $('input', box);
      input.value = searchTerm.dataset.searchTerm;
      renderSearch(box);
      input.focus();
      return;
    }

    if (event.target.closest('.drawer-close') || event.target.closest('.modal-close')) { closeLayers(); return; }
    if (!event.target.closest('.search-box')) closeSearches();

    const categoryNav = $('.category-nav');
    if (categoryNav && !event.target.closest('.category-nav')) {
      categoryNav.classList.remove('open');
      $('#category-button')?.setAttribute('aria-expanded', 'false');
    }
  });

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') { closeLayers(); closeSearches(); }
  });

  $('#category-button')?.addEventListener('click', event => {
    event.stopPropagation();
    const nav = $('.category-nav');
    nav.classList.toggle('open');
    event.currentTarget.setAttribute('aria-expanded', nav.classList.contains('open'));
  });

  $$('.mobile-menu-button').forEach(button => button.addEventListener('click', () => openDrawer('mobile-menu-drawer')));
  /* دسکتاپ: ورود به صفحه سبد خرید — موبایل: دراور سریع سبد */
  $$('.cart-button').forEach(button => button.addEventListener('click', event => {
    event.preventDefault();
    if (window.matchMedia('(min-width:1025px)').matches) {
      if (!document.body.classList.contains('page-cart')) window.location.href = 'cart.html';
      return;
    }
    openDrawer('cart-drawer');
  }));
  /* دکمه حساب کاربری: ورود به صفحه حساب کاربری */
  $$('.account-button').forEach(button => button.addEventListener('click', event => {
    event.preventDefault();
    if (!document.body.classList.contains('page-account')) window.location.href = 'account.html';
    else openModal('login-modal');
  }));
  $('#page-overlay')?.addEventListener('click', closeLayers);
  $('#wishlist-top')?.addEventListener('click', openWishlist);

  const drawerTabs = $$('.mobile-drawer-tabs button');
  drawerTabs.forEach((tab, index) => tab.addEventListener('click', () => {
    drawerTabs.forEach((item, i) => item.classList.toggle('active', i === index));
    $('#mobile-menu-drawer')?.classList.toggle('show-categories', index === 1);
  }));

  $('#checkout-button')?.addEventListener('click', () => {
    if (!cartTotals().count) { showToast('سبد خرید شما خالی است', 'i-bag'); return; }
    window.location.href = 'checkout.html';
  });
  $('#login-form')?.addEventListener('submit', event => {
    event.preventDefault();
    if (!event.currentTarget.reportValidity()) return;
    closeLayers();
    showToast('کد تایید برای شماره شما ارسال شد (نمایشی)');
    event.currentTarget.reset();
  });

  $$('.carousel-arrow').forEach(button => button.addEventListener('click', () => {
    const track = $(`#${button.dataset.carousel}`);
    if (!track) return;
    const amount = track.clientWidth * .8 * (button.dataset.dir === 'next' ? -1 : 1);
    track.scrollBy({ left: amount, behavior: 'smooth' });
  }));

  $('#back-to-top')?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}

/* ---------------------------------- هدر چسبان ---------------------------------- */
/* هدر اصلی دسکتاپ (main-header desktop-only):
   ۱) به محض رد شدن از نوار لوگو، بدون پرش به بالای صفحه می‌چسبد (is-sticky)
   ۲) با ادامه اسکرول به پایین، ارتفاعش با انیمیشن نرم کوتاه می‌شود (is-compact)
   ۳) با برگشت به بالای صفحه دوباره به ارتفاع کامل برمی‌گردد */
const HEADER_FULL = 85;
const HEADER_COMPACT = 62;
let lastSticky = false;
let lastCompact = false;
function initStickyHeader() {
  const header = $('#main-header');
  if (!header) return;
  const siteHeader = $('#site-header') || header.parentElement;
  const backTop = $('#back-to-top');
  let stickStart = 0;
  let ticking = false;

  /* نقطه شروع چسبیدن = انتهای نوار لوگو (بالای main-header در حالت عادی) */
  const measure = () => {
    const wasSticky = header.classList.contains('is-sticky');
    if (wasSticky) { header.classList.remove('is-sticky'); siteHeader.classList.remove('header-stuck'); }
    stickStart = Math.max(0, header.getBoundingClientRect().top + window.scrollY);
    if (wasSticky) { header.classList.add('is-sticky'); siteHeader.classList.add('header-stuck'); }
  };

  const setOffset = () => {
    const value = lastSticky ? (lastCompact ? HEADER_COMPACT : HEADER_FULL) : 0;
    document.documentElement.style.setProperty('--sticky-offset', `${value}px`);
  };

  const reset = () => {
    header.classList.remove('is-sticky', 'is-compact');
    siteHeader.classList.remove('header-stuck');
    document.body.classList.remove('has-sticky-header', 'header-compact');
    lastSticky = lastCompact = false;
    setOffset();
  };

  const update = () => {
    ticking = false;
    const y = window.scrollY;
    backTop?.classList.toggle('visible', y > 600);
    if (window.innerWidth <= 1024) { if (lastSticky) reset(); return; }

    const sticky = y > stickStart;
    /* کمی فاصله (هیسترزیس) تا هدر در مرز اسکرول نلرزد */
    const compact = sticky && (lastCompact ? y > stickStart + 40 : y > stickStart + 120);

    if (sticky !== lastSticky) {
      header.classList.toggle('is-sticky', sticky);
      siteHeader.classList.toggle('header-stuck', sticky);
      document.body.classList.toggle('has-sticky-header', sticky);
      lastSticky = sticky;
    }
    if (compact !== lastCompact) {
      header.classList.toggle('is-compact', compact);
      document.body.classList.toggle('header-compact', compact);
      lastCompact = compact;
    }
    setOffset();
  };

  const requestUpdate = () => {
    if (!ticking) { ticking = true; window.requestAnimationFrame(update); }
  };

  measure();
  update();
  window.addEventListener('scroll', requestUpdate, { passive: true });
  window.addEventListener('resize', () => { if (window.innerWidth <= 1024) reset(); else { measure(); requestUpdate(); } });
  window.addEventListener('load', () => { measure(); requestUpdate(); });
}

/* ---------------------------------- راه اندازی ---------------------------------- */
function init() {
  renderHomePanels();
  renderBrands();
  initHero();
  initSearch();
  initEvents();
  initStickyHeader();
  updateWishlistUI();
  updateCartUI();

  /* دکمه‌های علاقه‌مندی/خرید کارت‌های ثابت HTML */
  $$('[data-wish-product]').forEach(button => button.addEventListener('click', () => toggleWishlist(button.dataset.wishProduct)));

  document.dispatchEvent(new CustomEvent('rs:ready'));
  if (window.RS && typeof window.RS.onReady === 'function') window.RS.onReady();
}

window.RS = {
  products, CATEGORIES, BRANDS, $, $$, imagePath, getProduct, getCategory,
  formatPrice, normalizeText, toFa, toEn, productUrl, productCard, renderCards,
  addToCart, cartTotals, showToast, openDrawer, openModal, closeLayers, wishlist,
  changeCart, updateCartUI, toggleWishlist, updateWishlistUI,
  getCart: () => cart,
  getCartItems: () => Object.entries(cart).map(([id, quantity]) => ({ product: getProduct(id), quantity })).filter(item => item.product),
  setQuantity: (id, quantity) => { if (quantity <= 0) delete cart[id]; else cart[id] = quantity; saveState(); updateCartUI(); },
  clearCart: () => { cart = {}; saveState(); updateCartUI(); },
  onReady: null
};

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
else init();
