@extends('layouts.app')

@section('content')
  <main class="page-main" id="main-content">
    <div class="page-container">
      <nav class="breadcrumb" aria-label="مسیر صفحه">
        <a href="{{ home_url('/') }}">خانه</a><svg><use href="#i-chevron-left"/></svg>
        @if(!empty($searchQuery))
          <a href="{{ rs_page_url('shop') }}">فروشگاه</a><svg><use href="#i-chevron-left"/></svg>
          <span class="current">{{ $archiveHeading }}</span>
        @else
          <span class="current">{{ $archiveHeading === 'فروشگاه محصولات رنگ صادقی' ? 'فروشگاه' : $archiveHeading }}</span>
        @endif
      </nav>

      <header class="page-head with-visual">
        <div>
          <h1>{{ $archiveHeading === 'فروشگاه محصولات رنگ صادقی' ? 'فروشگاه رنگ صادقی' : $archiveHeading }}</h1>
          <p>مجموعه‌ای منتخب از رنگ‌های ساختمانی و صنعتی، مواد پتینه و میکروسمنت، رنگ‌های هنری، رزین و ابزارآلات حرفه‌ای؛ همه در یک فروشگاه با ضمانت اصالت کالا.</p>
          <div class="head-meta">
            <span><svg><use href="#i-shield"/></svg>ضمانت اصالت کالا</span>
            <span><svg><use href="#i-truck"/></svg>ارسال به سراسر کشور</span>
            <span><svg><use href="#i-headset"/></svg>مشاوره تخصصی خرید</span>
          </div>
        </div>
        <img src="{{ rs_img('blog/tools.webp') }}" alt="ابزار و محصولات فروشگاه رنگ صادقی" loading="lazy">
      </header>

      <div class="shop-layout">
        <aside class="shop-sidebar" id="shop-sidebar" aria-label="فیلتر محصولات" data-filter-base="{{ $filterBaseUrl }}">
          <button type="button" class="rs-close-filters mobile-only" aria-label="بستن فیلترها">بستن فیلترها ✕</button>
          @if(!empty($shopCfg['filter_categories']))
          <div class="filter-card">
            <h3>دسته بندی محصولات <svg><use href="#i-layers"/></svg></h3>
            <div class="filter-list" data-filter-list="cats">
              @foreach($filterCategories as $fc)
                @include('partials.filter-category', ['fc' => $fc, 'selectedCats' => $selectedCats])
              @endforeach
            </div>
          </div>
          @endif

          @if(!empty($shopCfg['filter_brands']))
          <div class="filter-card">
            <h3>برند <svg><use href="#i-award"/></svg></h3>
            <div class="filter-list" data-filter-list="brands">
              @foreach($filterBrands as $fb)
                <label class="filter-option">
                  <input type="checkbox" name="brand" value="{{ $fb['slug'] }}" {{ in_array($fb['slug'], $selectedBrands, true) ? 'checked' : '' }}>
                  <span>{{ $fb['title'] }}</span>
                  <b class="count">{{ rs_fa($fb['count']) }}</b>
                </label>
              @endforeach
            </div>
          </div>
          @endif

          @foreach($filterAttributes as $fa)
          <div class="filter-card"><h3>{{ $fa['name'] }}</h3><div class="filter-list">
            @foreach($fa['choices'] as $choice)
              <label class="filter-option"><input type="checkbox" data-attribute="{{ $fa['taxonomy'] }}" value="{{ $choice['slug'] }}" {{ in_array($choice['slug'], $selectedAttributes[$fa['taxonomy']] ?? [], true) ? 'checked' : '' }}><span>{{ $choice['name'] }}</span><b class="count">{{ rs_fa($choice['count']) }}</b></label>
            @endforeach
          </div></div>
          @endforeach

          @if(!empty($shopCfg['filter_price']))
          <div class="filter-card">
            <h3>محدوده قیمت (تومان) <svg><use href="#i-tag"/></svg></h3>
            <div class="price-inputs">
              <div><label for="filter-min">از</label><input id="filter-min" name="min_price" value="{{ $minPrice > 0 ? $minPrice : '' }}" inputmode="numeric" placeholder="۰"></div>
              <div><label for="filter-max">تا</label><input id="filter-max" name="max_price" value="{{ $maxPrice > 0 ? $maxPrice : '' }}" inputmode="numeric" placeholder="۵.۰۰۰.۰۰۰"></div>
            </div>
            <p class="filter-note">قیمت‌ها به تومان و بدون احتساب هزینه ارسال است.</p>
          </div>
          @endif

          @if(!empty($shopCfg['filter_on_sale']) || !empty($shopCfg['filter_in_stock']))
          <div class="filter-card">
            <h3>سایر فیلترها <svg><use href="#i-filter"/></svg></h3>
            <div class="filter-list">
              @if(!empty($shopCfg['filter_on_sale']))
                <label class="filter-option"><input type="checkbox" id="filter-sale" {{ $onlySale ? 'checked' : '' }}><span>فقط کالاهای تخفیف‌دار</span></label>
              @endif
              @if(!empty($shopCfg['filter_in_stock']))
                <label class="filter-option"><input type="checkbox" id="filter-stock" {{ $onlyStock ? 'checked' : '' }}><span>فقط کالاهای موجود</span></label>
              @endif
            </div>
          </div>
          @endif

          @if(!empty($shopCfg['sidebar_banner_enabled']))
          <a class="filter-side-banner" href="{{ add_query_arg('cat', $shopCfg['sidebar_banner_cat'] ?? 'patina', rs_page_url('shop')) }}" aria-label="پیشنهاد ویژه فروشگاه">
            <img src="{{ rs_img($shopCfg['sidebar_banner_image'] ?? 'assets/images/blog/microcement.webp') }}" alt="پیشنهاد ویژه" loading="lazy">
          </a>
          @endif
        </aside>

        <div class="shop-main">
          <div class="shop-toolbar">
            <span class="result-count" id="result-count">نمایش <b>{{ $showingFa }}</b> از <b>{{ $totalFa }}</b> محصول</span>
            <button class="btn btn-soft btn-sm mobile-filter-button" type="button" data-open-filters><svg><use href="#i-filter"/></svg>فیلترها</button>
            <span class="toolbar-spacer"></span>
            <label class="sort-select"><span>مرتب‌سازی:</span>
              <select id="sort-select">
                <option value="default" {{ $sort === 'default' ? 'selected' : '' }}>پیش‌فرض</option>
                <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>جدیدترین</option>
                <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>محبوب‌ترین</option>
                <option value="bestseller" {{ $sort === 'bestseller' ? 'selected' : '' }}>پرفروش‌ترین</option>
                <option value="cheap" {{ $sort === 'cheap' ? 'selected' : '' }}>ارزان‌ترین</option>
                <option value="expensive" {{ $sort === 'expensive' ? 'selected' : '' }}>گران‌ترین</option>
                <option value="name" {{ $sort === 'name' ? 'selected' : '' }}>نام (الفبایی)</option>
              </select>
            </label>
            <div class="view-toggles" role="group" aria-label="نوع نمایش">
              <button class="{{ $view === 'grid' ? 'active' : '' }}" type="button" data-view="grid" aria-label="نمایش شبکه‌ای"><svg><use href="#i-grid"/></svg></button>
              <button class="{{ $view === 'list' ? 'active' : '' }}" type="button" data-view="list" aria-label="نمایش لیستی"><svg><use href="#i-list"/></svg></button>
            </div>
          </div>

          <div class="active-filters" id="active-filters">
            @if(!empty($searchQuery))
              <button type="button" class="filter-chip" data-clear="q">جستجو: {{ $searchQuery }} <svg><use href="#i-close"/></svg></button>
            @endif
            @foreach($selectedCats as $sc)
              <button type="button" class="filter-chip" data-clear="cat:{{ $sc }}">{{ $selectedCategoryLabels[$sc] ?? $sc }} <svg><use href="#i-close"/></svg></button>
            @endforeach
            @foreach($selectedBrands as $sb)
              <button type="button" class="filter-chip" data-clear="brand:{{ $sb }}">{{ $selectedBrandLabels[$sb] ?? $sb }} <svg><use href="#i-close"/></svg></button>
            @endforeach
            @if($onlySale)
              <button type="button" class="filter-chip" data-clear="sale">فقط تخفیف‌دار <svg><use href="#i-close"/></svg></button>
            @endif
            @if($onlyStock)
              <button type="button" class="filter-chip" data-clear="stock">فقط موجود <svg><use href="#i-close"/></svg></button>
            @endif
          </div>

          <div class="shop-grid {{ $view === 'list' ? 'is-list' : '' }}" id="shop-grid">
            @forelse($products as $product)
              @include('partials.product-card', ['product' => $product, 'listMode' => ($view === 'list'), 'wishlistIds' => $wishlistIds])
            @empty
              <div class="empty-state" style="grid-column:1/-1">
                <svg><use href="#i-search"/></svg>
                <h3>محصولی با این فیلترها پیدا نشد</h3>
                <p>می‌توانید فیلترها را تغییر دهید یا همه آن‌ها را پاک کنید.</p>
                <a class="btn btn-primary btn-sm" href="{{ rs_page_url('shop') }}">پاک کردن فیلترها</a>
              </div>
            @endforelse
          </div>

          <nav class="pagination" id="shop-pagination" aria-label="صفحه‌بندی محصولات" data-mode="{{ $shopCfg['pagination_mode'] ?? 'numbers' }}">
            @if($total_pages > 1)
              @if(($shopCfg['pagination_mode'] ?? 'numbers') !== 'numbers')
                @if($page < $total_pages)<a class="rs-load-more" href="{{ add_query_arg('paged', $page + 1) }}" data-page="{{ $page + 1 }}">نمایش محصولات بیشتر</a>@endif
              @else
                @if($page > 1)<a href="{{ add_query_arg('paged', $page - 1) }}" aria-label="صفحه قبل">‹</a>@endif
                @php $lastShown = 0; @endphp
                @for($i = 1; $i <= $total_pages; $i++)
                  @if($i === 1 || $i === $total_pages || abs($i - $page) <= 2)
                    @if($lastShown && $i - $lastShown > 1)<span aria-hidden="true">…</span>@endif
                    <a href="{{ add_query_arg('paged', $i) }}" class="{{ $i === $page ? 'active' : '' }}" @if($i === $page) aria-current="page" @endif>{{ rs_fa($i) }}</a>
                    @php $lastShown = $i; @endphp
                  @endif
                @endfor
                @if($page < $total_pages)<a href="{{ add_query_arg('paged', $page + 1) }}" aria-label="صفحه بعد">›</a>@endif
              @endif
            @endif
          </nav>
        </div>
      </div>

      @if(!empty($shopCfg['subcat_tiles_enabled']))
      <section class="page-section">
        <div class="section-head">
          <div><h2>خرید بر اساس دسته‌بندی</h2><p>سریع‌تر به محصول مورد نظرتان برسید</p></div>
          <a class="section-link" href="{{ rs_page_url('categories') }}">همه دسته‌بندی‌ها <svg><use href="#i-chevron-left"/></svg></a>
        </div>
        <div class="subcat-tiles">
          <a class="subcat-tile" href="{{ add_query_arg('cat', 'patina', rs_page_url('shop')) }}"><img src="{{ rs_img('popular-micro.webp') }}" alt="میکروسمنت" loading="lazy"><div><strong>میکروسمنت</strong><span>مشاهده</span></div></a>
          <a class="subcat-tile" href="{{ add_query_arg('cat', 'patina', rs_page_url('shop')) }}"><img src="{{ rs_img('best-ceramic.webp') }}" alt="تکسچر" loading="lazy"><div><strong>تکسچر</strong><span>مشاهده</span></div></a>
          <a class="subcat-tile" href="{{ add_query_arg('cat', 'art', rs_page_url('shop')) }}"><img src="{{ rs_img('popular-acrylic.webp') }}" alt="رنگ اکریلیک" loading="lazy"><div><strong>رنگ اکریلیک</strong><span>مشاهده</span></div></a>
          <a class="subcat-tile" href="{{ add_query_arg('cat', 'art', rs_page_url('shop')) }}"><img src="{{ rs_img('popular-resin.webp') }}" alt="رزین اپوکسی" loading="lazy"><div><strong>رزین اپوکسی</strong><span>مشاهده</span></div></a>
          <a class="subcat-tile" href="{{ add_query_arg('cat', 'tools', rs_page_url('shop')) }}"><img src="{{ rs_img('popular-brush.webp') }}" alt="قلم‌مو" loading="lazy"><div><strong>قلم‌مو</strong><span>مشاهده</span></div></a>
          <a class="subcat-tile" href="{{ add_query_arg('cat', 'tools', rs_page_url('shop')) }}"><img src="{{ rs_img('popular-roller.webp') }}" alt="غلطک" loading="lazy"><div><strong>غلطک</strong><span>مشاهده</span></div></a>
          <a class="subcat-tile" href="{{ add_query_arg('cat', 'stencil', rs_page_url('shop')) }}"><img src="{{ rs_img('cat-stencil.webp') }}" alt="شابلون" loading="lazy"><div><strong>شابلون</strong><span>مشاهده</span></div></a>
          <a class="subcat-tile" href="{{ add_query_arg('cat', 'building', rs_page_url('shop')) }}"><img src="{{ rs_img('cat-building.webp') }}" alt="ساختمانی" loading="lazy"><div><strong>ساختمانی</strong><span>مشاهده</span></div></a>
        </div>
      </section>
      @endif

      @if(!empty($shopCfg['cta_band_enabled']))
      <section class="page-section">
        <div class="cta-band">
          <div>
            <h2>در انتخاب محصول مطمئن نیستید؟</h2>
            <p>کارشناسان رنگ صادقی در انتخاب رنگ، متریال و ابزار کنار شما هستند؛ از ساعت ۸ صبح تا ۸ شب.</p>
          </div>
          <a class="btn" href="{{ rs_page_url('contact') }}"><svg><use href="#i-headset"/></svg>مشاوره رایگان</a>
        </div>
      </section>
      @endif
    </div>
  </main>
@endsection
