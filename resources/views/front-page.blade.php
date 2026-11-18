@extends('layouts.app')

@section('content')
  <main id="main-content">
    @foreach($sectionOrder as $secKey)

      {{-- ============ ۱. هیرو اسلایدر + دو کارت کناری ============ --}}
      @if($secKey === 'hero' && !empty($home['hero_enabled']))
        <section class="hx-hero site-container" aria-label="پیشنهادهای فروشگاه">
          <div class="hero-slider hx-slider">
            @foreach($heroSlides as $idx => $slide)
              <div class="hero-slide {{ $idx === 0 ? 'active' : '' }}" data-slide="{{ $idx }}">
                @if(!empty($slide['link']))
                  <a href="{{ $slide['link'] }}" style="display:block;width:100%;height:100%">
                @endif
                <picture>
                  @if(!empty($slide['mobile']))
                    <source media="(max-width: 768px)" srcset="{{ rs_img($slide['mobile']) }}">
                  @endif
                  <img src="{{ rs_img($slide['desktop'] ?? 'assets/images/hero-pottery.webp') }}" alt="{{ $slide['alt'] ?? '' }}" {!! $idx === 0 ? 'fetchpriority="high"' : 'loading="lazy"' !!}>
                </picture>
                @if(!empty($slide['link']))
                  </a>
                @endif
              </div>
            @endforeach

            @if(count($heroSlides) > 1)
              <div class="hero-controls desktop-only">
                <button class="hero-prev" type="button" aria-label="اسلاید قبلی"><svg><use href="#i-chevron-right"/></svg></button>
                <button class="hero-next" type="button" aria-label="اسلاید بعدی"><svg><use href="#i-chevron-left"/></svg></button>
              </div>
              <div class="hero-dots" role="tablist" aria-label="انتخاب اسلاید">
                @foreach($heroSlides as $idx => $slide)
                  <button class="{{ $idx === 0 ? 'active' : '' }}" type="button" data-slide-to="{{ $idx }}" aria-label="اسلاید {{ $idx + 1 }}"></button>
                @endforeach
              </div>
            @endif
          </div>

          @if(!empty($heroSideCards))
            <aside class="hx-hero-side desktop-only" aria-label="پیشنهادهای ویژه">
              @foreach($heroSideCards as $card)
                @php
                  $cardHref = !empty($card['url'])
                    ? $card['url']
                    : (!empty($card['sale_only'])
                      ? add_query_arg('sale', '1', rs_page_url('shop'))
                      : (!empty($card['cat_slug']) ? add_query_arg('cat', $card['cat_slug'], rs_page_url('shop')) : rs_page_url('shop')));
                @endphp
                <a class="hx-side-card" href="{{ $cardHref }}">
                  <img src="{{ rs_img($card['image'] ?? 'assets/images/blog/metallic.webp') }}" alt="{{ $card['title'] ?? '' }}" loading="lazy">
                  <span class="hx-side-body">
                    <span class="hx-side-tag {{ ($card['tag_style'] ?? 'red') === 'gold' ? 'is-gold' : '' }}">{{ $card['tag'] ?? '' }}</span>
                    <b>{{ $card['title'] ?? '' }}</b>
                    <small>{{ $card['subtitle'] ?? '' }}</small>
                  </span>
                </a>
              @endforeach
            </aside>
          @endif
        </section>
      @endif

      {{-- ============ ۲. دسته‌بندی‌ها ============ --}}
      @if($secKey === 'categories' && !empty($home['categories_enabled']))
        <section class="hx-catbar site-container" aria-labelledby="hx-cats-title">
          <header class="hx-sec-head">
            <h2 id="hx-cats-title">{{ $home['categories_title'] ?? 'خرید بر اساس دسته‌بندی' }}</h2>
            <a href="{{ rs_page_url('categories') }}">همه دسته‌بندی‌ها<svg><use href="#i-chevron-left"/></svg></a>
          </header>
          <div class="hx-cats-row">
            @foreach($homeCategories as $cat)
              <a class="hx-cat" href="{{ $cat['url'] }}">
                <span class="hx-cat-img"><img src="{{ $cat['img'] }}" alt="{{ $cat['name'] }}" loading="lazy"></span>
                <span class="hx-cat-name">{{ $cat['name'] }}</span>
                <small>{{ $cat['count_fa'] }}</small>
              </a>
            @endforeach
            <a class="hx-cat" href="{{ rs_page_url('categories') }}">
              <span class="hx-cat-img is-all"><svg><use href="#i-grid"/></svg></span>
              <span class="hx-cat-name">همه دسته‌ها</span>
              <small>مشاهده</small>
            </a>
          </div>
        </section>
      @endif

      {{-- ============ ۳. پیشنهاد شگفت‌انگیز ============ --}}
      @if($secKey === 'deal' && !empty($home['deal_enabled']) && !empty($dealProducts))
        <section class="hx-deal" id="sale" aria-labelledby="hx-deal-title">
          <div class="site-container hx-deal-inner">
            <header class="hx-deal-head">
              <div class="hx-deal-title">
                <span class="hx-deal-mark"><svg><use href="#i-sparkles"/></svg></span>
                <div>
                  <h2 id="hx-deal-title">{{ $home['deal_title'] ?? 'پیشنهاد شگفت‌انگیز' }}</h2>
                  <p>{{ $home['deal_subtitle'] ?? 'تخفیف‌های ویژه امروز، تا پایان روز' }}</p>
                </div>
              </div>
              <div class="hx-countdown" id="hx-countdown" data-end="{{ $home['deal_end_datetime'] ?? '' }}" aria-live="off">
                <span class="hx-cd"><b data-cd="h">۰۲</b><small>ساعت</small></span>
                <i>:</i>
                <span class="hx-cd"><b data-cd="m">۳۵</b><small>دقیقه</small></span>
                <i>:</i>
                <span class="hx-cd"><b data-cd="s">۱۲</b><small>ثانیه</small></span>
              </div>
              <a class="hx-deal-all" href="{{ add_query_arg('sale', '1', rs_page_url('shop')) }}">مشاهده همه<svg><use href="#i-chevron-left"/></svg></a>
            </header>
            <div class="carousel-wrap">
              <div class="product-track" id="sale-products">
                @foreach($dealProducts as $product)
                  @include('partials.product-card', ['product' => $product, 'wishlistIds' => $wishlistIds])
                @endforeach
              </div>
              <button class="carousel-arrow carousel-prev" type="button" data-carousel="sale-products" data-dir="prev" aria-label="قبلی"><svg><use href="#i-chevron-right"/></svg></button>
              <button class="carousel-arrow carousel-next" type="button" data-carousel="sale-products" data-dir="next" aria-label="بعدی"><svg><use href="#i-chevron-left"/></svg></button>
            </div>
          </div>
        </section>
      @endif

      {{-- ============ ۴. نوار خدمات ============ --}}
      @if($secKey === 'services' && !empty($home['services_enabled']) && !empty($services))
        <section class="hx-services site-container" aria-label="خدمات فروشگاه">
          @foreach($services as $srv)
            <article class="hx-service">
              <span class="hx-service-icon"><svg><use href="#{{ $srv['icon'] ?? 'i-shield' }}"/></svg></span>
              <div><b>{{ $srv['title'] ?? '' }}</b><small>{{ $srv['subtitle'] ?? '' }}</small></div>
            </article>
          @endforeach
        </section>
      @endif

      {{-- ============ ۵. خرید بر اساس پروژه ============ --}}
      @if($secKey === 'projects' && !empty($home['projects_enabled']) && !empty($projects))
        <section class="hx-projects site-container" aria-labelledby="hx-project-title">
          <header class="hx-sec-head">
            <h2 id="hx-project-title">{{ $home['projects_title'] ?? 'خرید بر اساس پروژه' }}</h2>
            <a href="{{ rs_page_url('categories') }}">همه پروژه‌ها<svg><use href="#i-chevron-left"/></svg></a>
          </header>
          <div class="hx-project-grid">
            @foreach($projects as $proj)
              <a class="hx-project {{ !empty($proj['tall']) ? 'is-tall' : '' }}" href="{{ !empty($proj['url']) ? $proj['url'] : add_query_arg('cat', $proj['cat'] ?? 'building', rs_page_url('shop')) }}">
                <img src="{{ rs_img($proj['image'] ?? 'assets/images/cat-building-photo.webp') }}" alt="{{ $proj['title'] ?? '' }}" loading="lazy">
                <span class="hx-project-body">
                  <b>{{ $proj['title'] ?? '' }}</b>
                  <small>{{ $proj['subtitle'] ?? '' }}</small>
                  <em>شروع خرید<svg><use href="#i-chevron-left"/></svg></em>
                </span>
              </a>
            @endforeach
          </div>
        </section>
      @endif

      {{-- ============ ۶. پرفروش‌ترین‌ها ============ --}}
      @if($secKey === 'bestsellers' && !empty($home['bestsellers_enabled']) && !empty($bestsellers))
        <section class="hx-panel site-container" id="best" aria-labelledby="hx-best-title">
          <header class="hx-sec-head">
            <h2 id="hx-best-title">{{ $home['bestsellers_title'] ?? 'پرفروش‌ترین محصولات' }}</h2>
            <a href="{{ add_query_arg('rs_sort', 'bestseller', rs_page_url('shop')) }}">مشاهده همه<svg><use href="#i-chevron-left"/></svg></a>
          </header>
          <div class="carousel-wrap">
            <div class="product-track" id="best-products">
              @foreach($bestsellers as $product)
                @include('partials.product-card', ['product' => $product, 'wishlistIds' => $wishlistIds])
              @endforeach
            </div>
            <button class="carousel-arrow carousel-prev" type="button" data-carousel="best-products" data-dir="prev" aria-label="قبلی"><svg><use href="#i-chevron-right"/></svg></button>
            <button class="carousel-arrow carousel-next" type="button" data-carousel="best-products" data-dir="next" aria-label="بعدی"><svg><use href="#i-chevron-left"/></svg></button>
          </div>
        </section>
      @endif

      {{-- ============ ۷. برندها ============ --}}
      @if($secKey === 'brands' && !empty($home['brands_enabled']) && !empty($brandItems))
        <section class="brands-section hx-brands" aria-labelledby="brands-title">
          <div class="brands-heading">
            <h2 id="brands-title">بیش از <span>{{ rs_fa($home['brands_count_badge'] ?? '۲۰') }}</span> برند با کیفیت</h2>
            <p>{{ $home['brands_desc'] ?? '' }}</p>
          </div>
          <div class="brand-marquee">
            <div class="brand-track" id="brand-track">
              @foreach(array_merge($brandItems, $brandItems) as $b)
                <a class="brand-item" href="{{ $b['url'] }}" aria-label="{{ $b['title'] }}">
                  <img src="{{ $b['image'] }}" alt="{{ $b['title'] }}" loading="lazy">
                </a>
              @endforeach
            </div>
          </div>
        </section>
      @endif

      {{-- ============ ۸. پالت رنگ ============ --}}
      @if($secKey === 'palette' && !empty($home['palette_enabled']) && !empty($paletteSwatches))
        <section class="hx-colors site-container" aria-labelledby="hx-color-title">
          <div class="hx-color-copy">
            <span class="hx-eyebrow"><svg><use href="#i-palette"/></svg>{{ $home['palette_eyebrow'] ?? 'راهنمای رنگ' }}</span>
            <h2 id="hx-color-title">{{ $home['palette_title'] ?? 'این هفته با کدام پالت رنگ شروع کنیم؟' }}</h2>
            <p>{{ $home['palette_desc'] ?? '' }}</p>
            <div class="hx-color-actions">
              <a class="btn btn-primary" href="{{ add_query_arg('cat', 'building', rs_page_url('shop')) }}">مشاهده رنگ‌ها</a>
              <a class="btn btn-outline" href="{{ rs_page_url('contact') }}">مشاوره رایگان رنگ</a>
            </div>
          </div>
          <div class="hx-color-grid">
            @foreach($paletteSwatches as $sw)
              <a class="hx-swatch" href="{{ add_query_arg('cat', $sw['cat'] ?? 'building', rs_page_url('shop')) }}" style="--sw:{{ $sw['hex'] ?? '#c9b8a4' }}">
                <span class="hx-swatch-chip"></span>
                <b>{{ $sw['name'] ?? '' }}</b>
                <small>{{ $sw['code'] ?? '' }}</small>
              </a>
            @endforeach
          </div>
        </section>
      @endif

      {{-- ============ ۹. جدیدترین‌ها ============ --}}
      @if($secKey === 'newest' && !empty($home['newest_enabled']) && !empty($newestProducts))
        <section class="hx-panel site-container" id="newest" aria-labelledby="hx-new-title">
          <header class="hx-sec-head">
            <h2 id="hx-new-title">{{ $home['newest_title'] ?? 'جدیدترین محصولات' }}</h2>
            <a href="{{ add_query_arg('rs_sort', 'newest', rs_page_url('shop')) }}">مشاهده همه<svg><use href="#i-chevron-left"/></svg></a>
          </header>
          <div class="carousel-wrap">
            <div class="product-track" id="new-products">
              @foreach($newestProducts as $product)
                @include('partials.product-card', ['product' => $product, 'wishlistIds' => $wishlistIds])
              @endforeach
            </div>
            <button class="carousel-arrow carousel-prev" type="button" data-carousel="new-products" data-dir="prev" aria-label="قبلی"><svg><use href="#i-chevron-right"/></svg></button>
            <button class="carousel-arrow carousel-next" type="button" data-carousel="new-products" data-dir="next" aria-label="بعدی"><svg><use href="#i-chevron-left"/></svg></button>
          </div>
        </section>
      @endif

      {{-- ============ ۱۰. مجله ============ --}}
      @if($secKey === 'blog' && !empty($home['blog_enabled']) && !empty($blogPosts))
        <section class="hx-editorial site-container" aria-labelledby="hx-blog-title">
          <header class="hx-sec-head">
            <h2 id="hx-blog-title">{{ $home['blog_title'] ?? 'از مجله رنگ صادقی' }}</h2>
            <a href="{{ rs_page_url('blog') }}">همه مقاله‌ها<svg><use href="#i-chevron-left"/></svg></a>
          </header>
          <div class="hx-blog-grid">
            @foreach($blogPosts as $post)
              <a class="hx-post" href="{{ $post['url'] }}">
                <span class="hx-post-media"><img src="{{ $post['image'] }}" alt="{{ $post['title'] }}" loading="lazy"><em>{{ $post['cat'] }}</em></span>
                <span class="hx-post-body"><b>{{ $post['title'] }}</b><small><svg><use href="#i-clock"/></svg>{{ $post['read'] }}</small></span>
              </a>
            @endforeach
          </div>
        </section>
      @endif

      {{-- ============ ۱۱. فروشگاه حضوری + خبرنامه ============ --}}
      @if($secKey === 'closing' && !empty($home['closing_enabled']))
        <section class="hx-closing site-container" aria-label="فروشگاه حضوری و خبرنامه">
          <article class="hx-store-card">
            <span class="hx-store-icon"><svg><use href="#i-store"/></svg></span>
            <h2>{{ $home['store_card_title'] ?? 'حضوری هم ما را ببینید' }}</h2>
            <p>{{ $addressShortFa }}<br>{{ $workingHoursFa }}</p>
            <div class="hx-store-actions">
              <a class="btn btn-primary btn-sm" href="{{ rs_page_url('stores') }}">مشاهده فروشگاه‌ها</a>
              <a class="btn btn-outline btn-sm" href="tel:{{ $phoneTel }}">تماس با فروشگاه</a>
            </div>
          </article>
          <article class="hx-news-card">
            <span class="hx-store-icon"><svg><use href="#i-mail"/></svg></span>
            <h2>{{ $home['news_card_title'] ?? 'تخفیف‌ها را اول از همه بگیرید' }}</h2>
            <p>{{ $home['news_card_desc'] ?? 'با عضویت در خبرنامه، پیشنهادهای هفتگی و مقاله‌های تخصصی رنگ را دریافت کنید.' }}</p>
            <form class="hx-news-form" data-newsletter-form data-newsletter-action="rs_home_newsletter_subscribe">
              <label class="rs-news-honeypot" aria-hidden="true">وب‌سایت<input name="newsletter_website" type="text" tabindex="-1" autocomplete="off"></label>
              <input type="text" name="contact" placeholder="ایمیل یا شماره موبایل" aria-label="ایمیل یا شماره موبایل" required>
              <button class="btn btn-primary" type="submit">عضویت</button>
            </form>
            <small class="hx-news-note">با عضویت، قوانین حریم خصوصی فروشگاه را می‌پذیرید.</small>
          </article>
        </section>
      @endif

    @endforeach
  </main>
@endsection
