  <header class="site-header hx-header" id="site-header">
    @if(!empty($general['utility_bar_enabled']))
    <!-- نوار خدمات بالای سایت -->
    <div class="hx-utility desktop-only">
      <div class="site-container hx-utility-inner">
        <nav class="hx-utility-links" aria-label="دسترسی‌های سریع">
          <a href="{{ rs_page_url('stores') }}"><svg><use href="#i-store"/></svg>فروشگاه‌های ما</a>
          <a href="{{ rs_page_url('order-details') }}"><svg><use href="#i-box"/></svg>پیگیری سفارش</a>
          <a href="{{ rs_page_url('blog') }}"><svg><use href="#i-book"/></svg>مجله رنگ</a>
          <a href="{{ rs_page_url('contact') }}"><svg><use href="#i-headset"/></svg>مشاوره تخصصی</a>
        </nav>
        <div class="hx-utility-side">
          <span class="hx-utility-note"><svg><use href="#i-truck"/></svg>{{ $general['free_shipping_note'] ?? 'ارسال رایگان سفارش‌های بالای ۱۰ میلیون تومان' }}</span>
          <div class="social-links">
            @if(!empty($general['instagram']))
              <a href="{{ $general['instagram'] }}" target="_blank" rel="noopener" aria-label="اینستاگرام"><svg><use href="#i-instagram"/></svg></a>
            @endif
            @if(!empty($general['whatsapp']))
              <a href="{{ $general['whatsapp'] }}" target="_blank" rel="noopener" aria-label="واتساپ"><svg><use href="#i-whatsapp"/></svg></a>
            @endif
            @if(!empty($general['telegram']))
              <a href="{{ $general['telegram'] }}" target="_blank" rel="noopener" aria-label="تلگرام"><svg><use href="#i-send"/></svg></a>
            @endif
          </div>
          <button class="wishlist-top" id="wishlist-top" type="button">
            <svg><use href="#i-heart"/></svg><span>علاقه‌مندی</span><b id="wishlist-count">{{ $wishlistCountFa }}</b>
          </button>
        </div>
      </div>
    </div>
    @endif

    <!-- هدر اصلی دسکتاپ (چسبان + فشرده‌شو) -->
    <div class="main-header desktop-only" id="main-header">
      <div class="site-container hx-main-inner">
        <a class="hx-logo" href="{{ home_url('/') }}" aria-label="{{ get_bloginfo('name') }}">
          <img src="{{ rs_img($general['logo_desktop'] ?? 'assets/images/logo.png') }}" alt="{{ get_bloginfo('name') }}" width="100" height="39">
        </a>

        <div class="category-nav hx-cats">
          <button class="category-button" id="category-button" type="button" aria-expanded="false">
            <svg><use href="#i-menu"/></svg>
            <span>دسته‌بندی کالاها</span>
            <svg class="down-icon"><use href="#i-chevron-down"/></svg>
          </button>
          <div class="mega-menu" id="mega-menu">
            <div class="mega-column">
              <a class="mega-title" href="{{ rs_page_url('categories') }}">بخش هنری <svg><use href="#i-chevron-left"/></svg></a>
              <a href="{{ add_query_arg('cat', 'art', rs_page_url('shop')) }}">رنگ اکریلیک</a>
              <a href="{{ add_query_arg('cat', 'art', rs_page_url('shop')) }}">رنگ روغن ها</a>
              <a href="{{ add_query_arg('cat', 'art', rs_page_url('shop')) }}">رزین اپوکسی هنری</a>
              <a href="{{ add_query_arg('cat', 'art', rs_page_url('shop')) }}">سفالگری و مجسمه سازی</a>
              <a href="{{ add_query_arg('cat', 'art', rs_page_url('shop')) }}">افکت ها</a>
              <a href="{{ add_query_arg('cat', 'art', rs_page_url('shop')) }}">وارنیش و تثبیت کننده</a>
            </div>
            <div class="mega-column">
              <a class="mega-title" href="{{ rs_page_url('categories') }}">پتینه <svg><use href="#i-chevron-left"/></svg></a>
              <a href="{{ add_query_arg('cat', 'patina', rs_page_url('shop')) }}">تکسچر ها</a>
              <a href="{{ add_query_arg('cat', 'patina', rs_page_url('shop')) }}">میکروسمنت</a>
              <a href="{{ add_query_arg('cat', 'patina', rs_page_url('shop')) }}">رنگ های دکوراتیو</a>
              <a href="{{ add_query_arg('cat', 'patina', rs_page_url('shop')) }}">ورق ها</a>
              <a href="{{ rs_page_url('categories') }}">تثبیت کننده ها</a>
            </div>
            <div class="mega-column">
              <a class="mega-title" href="{{ rs_page_url('categories') }}">ابزار ها <svg><use href="#i-chevron-left"/></svg></a>
              <a href="{{ add_query_arg('cat', 'tools', rs_page_url('shop')) }}">غلطک</a>
              <a href="{{ add_query_arg('cat', 'tools', rs_page_url('shop')) }}">قلم</a>
              <a href="{{ add_query_arg('cat', 'tools', rs_page_url('shop')) }}">لیسه و کاردک</a>
              <a href="{{ rs_page_url('categories') }}">ماله</a>
              <a href="{{ add_query_arg('cat', 'tools', rs_page_url('shop')) }}">سنباده ها</a>
            </div>
            <div class="mega-column">
              <a class="mega-title" href="{{ rs_page_url('categories') }}">شابلون و تابلو <svg><use href="#i-chevron-left"/></svg></a>
              <a href="{{ add_query_arg('cat', 'stencil', rs_page_url('shop')) }}">شابلون</a>
              <a href="{{ add_query_arg('cat', 'stencil', rs_page_url('shop')) }}">ماندالا</a>
              <a href="{{ add_query_arg('cat', 'stencil', rs_page_url('shop')) }}">داماسک</a>
              <a href="{{ rs_page_url('categories') }}">بوم ها</a>
              <a href="{{ rs_page_url('categories') }}">بیس های چوبی</a>
            </div>
            <div class="mega-highlight">
              <span class="mega-highlight-tag"><svg><use href="#i-sparkles"/></svg>{{ $general['mega_highlight_tag'] ?? 'پیشنهاد هفته' }}</span>
              <b>{{ $general['mega_highlight_title'] ?? 'تا ۳۵٪ تخفیف روی رنگ‌های ساختمانی' }}</b>
              <a class="btn btn-primary btn-sm" href="{{ add_query_arg('sale', '1', rs_page_url('shop')) }}">{{ $general['mega_highlight_btn'] ?? 'مشاهده تخفیف‌ها' }}</a>
            </div>
          </div>
        </div>

        <div class="search-box hx-search">
          <form class="search-form" action="{{ home_url('/') }}" method="get" data-search-form>
            <input type="hidden" name="post_type" value="product">
            <span class="hx-search-icon"><svg><use href="#i-search"/></svg></span>
            <input type="search" name="s" value="{{ get_search_query() }}" placeholder="{{ $general['search_placeholder'] ?? 'جستجو در رنگ، پتینه، ابزار و شابلون…' }}" aria-label="جستجوی محصولات" autocomplete="off">
            <button type="submit" aria-label="جستجو"><svg><use href="#i-chevron-left"/></svg></button>
          </form>
          <div class="search-results"></div>
        </div>

        <div class="header-tools hx-tools">
          <a class="hx-tool account-button" href="{{ rs_page_url('account') }}" aria-label="ورود یا ثبت نام">
            <svg><use href="#i-user"/></svg>
            <span>{{ $isLoggedIn ? ($currentUserName ?: 'حساب کاربری') : 'ورود | ثبت‌نام' }}</span>
          </a>
          <button class="hx-tool hx-tool-cart cart-button" type="button" aria-label="سبد خرید">
            <svg><use href="#i-bag"/></svg><span>سبد خرید</span><span class="cart-count">{{ $cartCountFa }}</span>
          </button>
        </div>
      </div>

      <div class="hx-navbar">
        <div class="site-container hx-navbar-inner">
          @if(has_nav_menu('primary_navigation'))
            {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'container' => 'nav', 'container_class' => 'main-nav', 'container_aria_label' => 'منوی اصلی', 'echo' => false, 'depth' => 1, 'items_wrap' => '%3$s']) !!}
          @else
            <nav class="main-nav" aria-label="منوی اصلی">
              <a class="{{ $currentNav === 'home' ? 'active' : '' }}" href="{{ home_url('/') }}">صفحه اصلی</a>
              <a class="{{ $currentNav === 'shop' ? 'active' : '' }}" href="{{ rs_page_url('shop') }}">فروشگاه</a>
              <a class="{{ $currentNav === 'categories' ? 'active' : '' }}" href="{{ rs_page_url('categories') }}">دسته‌بندی محصولات</a>
              <a class="{{ $currentNav === 'blog' ? 'active' : '' }}" href="{{ rs_page_url('blog') }}">مجله</a>
              <a class="{{ $currentNav === 'about' ? 'active' : '' }}" href="{{ rs_page_url('about') }}">درباره ما</a>
              <a class="{{ $currentNav === 'contact' ? 'active' : '' }}" href="{{ rs_page_url('contact') }}">تماس با ما</a>
              <a class="{{ $currentNav === 'stores' ? 'active' : '' }}" href="{{ rs_page_url('stores') }}">فروشگاه‌های ما</a>
            </nav>
          @endif

          @if(!empty($general['deal_nav_enabled']))
            <a class="hx-nav-deal" href="{{ !empty($general['deal_nav_url']) ? $general['deal_nav_url'] : add_query_arg('sale', '1', rs_page_url('shop')) }}">
              <svg><use href="#i-sparkles"/></svg>{{ $general['deal_nav_text'] ?? 'تخفیف‌های امروز' }}
            </a>
          @endif
        </div>
      </div>
    </div>

    <!-- هدر موبایل -->
    <div class="mobile-header mobile-only">
      <div class="hx-mobile-top">
        <a href="{{ home_url('/') }}" class="mobile-logo"><img src="{{ rs_img($general['logo_mobile'] ?? 'assets/images/logo-alt.webp') }}" alt="{{ get_bloginfo('name') }}"></a>
        <button class="hx-mobile-search-toggle" type="button" aria-label="باز کردن جستجو" aria-controls="mobile-search-box" aria-expanded="false"><svg><use href="#i-search"/></svg></button>
      </div>
      <div class="search-box mobile-search" id="mobile-search-box" hidden>
        <form class="search-form" action="{{ home_url('/') }}" method="get" data-search-form>
          <input type="hidden" name="post_type" value="product">
          <input type="search" name="s" value="{{ get_search_query() }}" placeholder="جستجوی محصولات" aria-label="جستجوی محصولات" autocomplete="off">
          <button type="submit" aria-label="جستجو"><svg><use href="#i-search"/></svg></button>
        </form>
        <div class="search-results"></div>
      </div>
      <button class="hx-hidden-cart cart-button" type="button" aria-label="سبد خرید"><svg><use href="#i-bag"/></svg><span class="cart-count">{{ $cartCountFa }}</span></button>
    </div>
  </header>
