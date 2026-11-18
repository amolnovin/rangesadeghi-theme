  <footer class="site-footer hx-footer">
    <div class="hx-footer-top">
      <div class="site-container hx-footer-top-inner">
        <section class="hx-footer-brand">
          <a class="hx-footer-logo" href="{{ home_url('/') }}"><img src="{{ rs_img($general['logo_desktop'] ?? 'assets/images/logo.png') }}" alt="{{ get_bloginfo('name') }}" loading="lazy"></a>
          <p>{{ $general['footer_about'] ?? '' }}</p>
          <div class="hx-footer-social">
            @if(!empty($general['instagram']))
              <a href="{{ $general['instagram'] }}" target="_blank" rel="noopener" aria-label="اینستاگرام"><svg><use href="#i-instagram"/></svg></a>
            @endif
            @if(!empty($general['whatsapp']))
              <a href="{{ $general['whatsapp'] }}" target="_blank" rel="noopener" aria-label="واتساپ"><svg><use href="#i-whatsapp"/></svg></a>
            @endif
            @if(!empty($general['telegram']))
              <a href="{{ $general['telegram'] }}" target="_blank" rel="noopener" aria-label="تلگرام"><svg><use href="#i-send"/></svg></a>
            @endif
            @if(!empty($general['email']))
              <a href="mailto:{{ $general['email'] }}" aria-label="ایمیل"><svg><use href="#i-mail"/></svg></a>
            @endif
          </div>
        </section>

        <div class="hx-footer-cols">
          <section>
            <h3>خرید و دسترسی</h3>
            <a href="{{ home_url('/') }}">صفحه اصلی</a>
            <a href="{{ rs_page_url('shop') }}">فروشگاه</a>
            <a href="{{ rs_page_url('categories') }}">دسته‌بندی محصولات</a>
            <a href="{{ rs_page_url('blog') }}">مجله رنگ</a>
            <a href="{{ rs_page_url('about') }}">درباره ما</a>
          </section>
          <section>
            <h3>دسته‌های پرطرفدار</h3>
            <a href="{{ add_query_arg('cat', 'building', rs_page_url('shop')) }}">رنگ ساختمانی</a>
            <a href="{{ add_query_arg('cat', 'art', rs_page_url('shop')) }}">رنگ هنری</a>
            <a href="{{ add_query_arg('cat', 'patina', rs_page_url('shop')) }}">پتینه و میکروسمنت</a>
            <a href="{{ add_query_arg('cat', 'stencil', rs_page_url('shop')) }}">شابلون و تابلو</a>
            <a href="{{ add_query_arg('cat', 'tools', rs_page_url('shop')) }}">ابزار و لوازم</a>
          </section>
          <section>
            <h3>خدمات مشتریان</h3>
            <a href="{{ rs_page_url('order-details') }}">پیگیری سفارش</a>
            <a href="{{ rs_page_url('cart') }}">سبد خرید</a>
            <a href="{{ rs_page_url('account') }}">حساب کاربری</a>
            <a href="{{ rs_page_url('contact') }}">تماس و پشتیبانی</a>
            <a href="{{ rs_page_url('stores') }}">فروشگاه‌های ما</a>
          </section>
          <section class="hx-footer-contact">
            <h3>ارتباط با ما</h3>
            <a href="tel:{{ $phoneTel }}"><svg><use href="#i-phone"/></svg><span><bdo dir="ltr">{{ $phoneDisplay }}</bdo></span></a>
            <p><svg><use href="#i-clock"/></svg><span class="hx-nowrap">{{ $workingHoursFa }}</span></p>
            <p><svg><use href="#i-pin"/></svg><span>{{ $addressFa }}</span></p>
            @if(!empty($general['email']))
              <a href="mailto:{{ $general['email'] }}"><svg><use href="#i-mail"/></svg><span>{{ $general['email'] }}</span></a>
            @endif
          </section>
        </div>

        <aside class="hx-footer-license">
          <h3>مجوزهای سایت</h3>
          @php
            // Prefer a site-provided official image. For the existing verification URL,
            // construct the official logo endpoint only for the trusted Enamad host.
            $enamadImage = $general['enamad_image'] ?? '';
            $enamadLink = (string) ($general['enamad_url'] ?? '');
            if (!$enamadImage && strtolower((string) wp_parse_url($enamadLink, PHP_URL_HOST)) === 'trustseal.enamad.ir') {
                parse_str((string) wp_parse_url($enamadLink, PHP_URL_QUERY), $enamadQuery);
                $enamadId = (string) ($enamadQuery['id'] ?? '');
                $enamadCode = (string) ($enamadQuery['Code'] ?? '');
                if (ctype_digit($enamadId) && preg_match('/^[A-Za-z0-9]+$/', $enamadCode)) {
                    $enamadImage = 'https://trustseal.enamad.ir/logo.aspx?id=' . $enamadId . '&Code=' . $enamadCode;
                }
            }
          @endphp
          @if(!empty($general['enamad_code']))
            <div class="enamad">{!! $general['enamad_code'] !!}</div>
          @elseif(!empty($enamadImage))
            <a class="enamad" href="{{ $general['enamad_url'] ?? '#' }}" target="_blank" rel="noopener" aria-label="نماد اعتماد الکترونیکی">
              <img src="{{ rs_img($enamadImage) }}" alt="نماد اعتماد الکترونیکی" referrerpolicy="origin" loading="lazy" style="max-width:110px;max-height:110px;object-fit:contain"><small>نماد اعتماد الکترونیکی</small>
            </a>
          @endif
          <span class="hx-license-note"><svg><use href="#i-shield"/></svg>پرداخت امن با درگاه بانکی</span>
        </aside>
      </div>
    </div>

    <div class="hx-footer-trust">
      <div class="site-container hx-trust-inner">
        <span><svg><use href="#i-check-circle"/></svg>ضمانت اصالت کالا</span>
        <span><svg><use href="#i-card"/></svg>پرداخت امن آنلاین</span>
        <span><svg><use href="#i-refresh"/></svg>۷ روز مهلت بازگشت</span>
        <span><svg><use href="#i-headset"/></svg>پشتیبانی تخصصی رنگ</span>
        <span><svg><use href="#i-truck"/></svg>ارسال به سراسر ایران</span>
      </div>
    </div>

    <div class="hx-footer-bottom">
      <div class="site-container hx-footer-bottom-inner">
        <p>{{ $general['copyright_text'] ?? 'تمامی حقوق فروشگاه رنگ صادقی محفوظ می‌باشد و هرگونه کپی‌برداری مستلزم کسب اجازه‌ی کتبی است.' }}</p>
        @if(!empty($general['credit_label']))
          <p>پشتیبانی از <a href="{{ $general['credit_url'] ?? '#' }}" target="_blank" rel="noopener">{{ $general['credit_label'] }}</a></p>
        @endif
      </div>
    </div>
  </footer>

  <button class="back-to-top" id="back-to-top" type="button" aria-label="بازگشت به بالا"><svg><use href="#i-arrow-up"/></svg></button>

  @if(!empty($general['mobile_toolbar_enabled']))
  <nav class="mobile-toolbar mobile-only" aria-label="دسترسی سریع">
    <a href="{{ home_url('/') }}" class="toolbar-home {{ $currentNav === 'home' ? 'active' : '' }}" {!! $currentNav === 'home' ? 'aria-current="page"' : '' !!}><svg><use href="#i-home"/></svg><span>خانه</span></a>
    <a href="{{ rs_page_url('shop') }}" class="toolbar-shop {{ $currentNav === 'shop' ? 'active' : '' }}" {!! $currentNav === 'shop' ? 'aria-current="page"' : '' !!}><svg><use href="#i-grid"/></svg><span>فروشگاه</span></a>
    <button class="mobile-menu-button" type="button"><svg><use href="#i-menu"/></svg><span>منو</span></button>
    <a href="{{ rs_page_url('cart') }}" class="toolbar-cart {{ $currentNav === 'cart' ? 'active' : '' }}"><span class="toolbar-icon"><svg><use href="#i-bag"/></svg><b class="cart-count">{{ $cartCountFa }}</b></span><span>سبد خرید</span></a>
    <a href="{{ rs_page_url('account') }}" class="toolbar-account {{ $currentNav === 'account' ? 'active' : '' }}"><svg><use href="#i-user"/></svg><span>حساب کاربری</span></a>
  </nav>
  @endif

  <div class="page-overlay" id="page-overlay"></div>

  <aside class="side-drawer mobile-menu-drawer" id="mobile-menu-drawer" aria-hidden="true" aria-label="منوی موبایل">
    <div class="drawer-heading"><img src="{{ rs_img($general['logo_desktop'] ?? 'assets/images/logo.png') }}" alt="{{ get_bloginfo('name') }}"><button class="drawer-close" type="button" aria-label="بستن"><svg><use href="#i-close"/></svg></button></div>
    <div class="mobile-drawer-tabs"><button class="active" type="button" data-drawer-tab="nav">منو</button><button type="button" id="drawer-categories-tab" data-drawer-tab="cats">دسته بندی ها</button></div>
    <nav class="mobile-drawer-nav" id="mobile-drawer-nav">
      <a href="{{ home_url('/') }}">صفحه اصلی</a>
      <a href="{{ rs_page_url('shop') }}">فروشگاه</a>
      <a href="{{ rs_page_url('categories') }}">دسته بندی محصولات</a>
      <a href="{{ rs_page_url('blog') }}">بلاگ</a>
      <a href="{{ rs_page_url('about') }}">درباره ما</a>
      <a href="{{ rs_page_url('contact') }}">تماس با ما</a>
      <a href="{{ rs_page_url('stores') }}">فروشگاه های ما</a>
    </nav>
    <div class="mobile-drawer-categories" id="mobile-drawer-categories" hidden>
      <details><summary>بخش هنری <svg><use href="#i-chevron-down"/></svg></summary><a href="{{ add_query_arg('cat', 'art', rs_page_url('shop')) }}">رنگ اکریلیک</a><a href="{{ add_query_arg('cat', 'art', rs_page_url('shop')) }}">رنگ روغن ها</a><a href="{{ add_query_arg('cat', 'art', rs_page_url('shop')) }}">رزین اپوکسی</a><a href="{{ add_query_arg('cat', 'art', rs_page_url('shop')) }}">سفالگری و مجسمه سازی</a></details>
      <details><summary>پتینه <svg><use href="#i-chevron-down"/></svg></summary><a href="{{ add_query_arg('cat', 'patina', rs_page_url('shop')) }}">تکسچر ها</a><a href="{{ add_query_arg('cat', 'patina', rs_page_url('shop')) }}">میکروسمنت</a><a href="{{ add_query_arg('cat', 'patina', rs_page_url('shop')) }}">رنگ های دکوراتیو</a></details>
      <details><summary>ابزار ها <svg><use href="#i-chevron-down"/></svg></summary><a href="{{ add_query_arg('cat', 'tools', rs_page_url('shop')) }}">قلم</a><a href="{{ add_query_arg('cat', 'tools', rs_page_url('shop')) }}">غلطک</a><a href="{{ add_query_arg('cat', 'tools', rs_page_url('shop')) }}">لیسه و کاردک</a></details>
      <details><summary>شابلون و تابلو <svg><use href="#i-chevron-down"/></svg></summary><a href="{{ add_query_arg('cat', 'stencil', rs_page_url('shop')) }}">شابلون</a><a href="{{ rs_page_url('categories') }}">بوم ها</a></details>
    </div>
  </aside>

  <aside class="side-drawer cart-drawer" id="cart-drawer" aria-hidden="true" aria-label="سبد خرید">
    <div class="drawer-heading"><h2>سبد خرید</h2><button class="drawer-close" type="button" aria-label="بستن"><svg><use href="#i-close"/></svg></button></div>
    <div class="cart-items" id="cart-items">
      @if(!empty($cartPayload['items']))
        @foreach($cartPayload['items'] as $ci)
          <div class="cart-item" data-cart-key="{{ $ci['key'] }}">
            <img src="{{ $ci['image'] }}" alt="{{ $ci['title'] }}">
            <div>
              <h3>{{ $ci['title'] }}</h3>
              <div class="cart-item-price">{{ $ci['line_total_fa'] }} تومان</div>
              <div class="cart-item-actions">
                <button type="button" data-cart-action="plus" data-id="{{ $ci['key'] }}" aria-label="افزایش"><svg><use href="#i-plus"/></svg></button>
                <span>{{ $ci['quantity_fa'] }}</span>
                <button type="button" data-cart-action="minus" data-id="{{ $ci['key'] }}" aria-label="کاهش"><svg><use href="#i-minus"/></svg></button>
              </div>
            </div>
            <button type="button" class="remove-cart-item" data-cart-action="remove" data-id="{{ $ci['key'] }}" aria-label="حذف محصول"><svg><use href="#i-trash"/></svg></button>
          </div>
        @endforeach
      @else
        <div class="cart-empty"><svg><use href="#i-bag"/></svg><p>سبد خرید شما خالی است.</p><a class="quick-add" href="{{ rs_page_url('shop') }}">مشاهده محصولات</a></div>
      @endif
    </div>
    <div class="cart-footer">
      <div class="cart-total"><span>جمع سبد خرید</span><strong id="cart-total">{{ $cartPayload['subtotal_fa'] ?? '۰ تومان' }}</strong></div>
      <a href="{{ rs_page_url('checkout') }}" class="checkout-button" id="checkout-button" style="text-align:center;display:block">تسویه حساب</a>
      <button type="button" class="continue-shopping drawer-close">ادامه خرید</button>
    </div>
  </aside>

  <section class="modal" id="quick-modal" role="dialog" aria-modal="true" aria-hidden="true" aria-label="مشاهده سریع محصول">
    <button class="modal-close" type="button" aria-label="بستن"><svg><use href="#i-close"/></svg></button>
    <div id="quick-modal-content"></div>
  </section>

  <section class="modal login-modal" id="login-modal" role="dialog" aria-modal="true" aria-hidden="true" aria-label="ورود به حساب کاربری">
    <button class="modal-close" type="button" aria-label="بستن"><svg><use href="#i-close"/></svg></button>
    <img src="{{ rs_img($general['logo_desktop'] ?? 'assets/images/logo.png') }}" alt="{{ get_bloginfo('name') }}" width="110">
    <h2>ورود | ثبت نام</h2>
    <p>برای ورود یا ثبت نام، شماره موبایل خود را وارد کنید.</p>
    <form id="login-form">
      <label for="mobile-number">شماره موبایل</label>
      <input id="mobile-number" name="identity" inputmode="numeric" placeholder="مثلاً 09123456789" required>
      <div id="login-modal-pass-wrap" hidden style="margin-top:10px">
        <label for="modal-password">رمز عبور حساب کاربری</label>
        <input id="modal-password" name="password" type="password" placeholder="رمز عبور خود را وارد کنید">
      </div>
      @if(class_exists('WP_MC_Captcha') && class_exists('WP_MC_Core') && \WP_MC_Core::get('enabled', 1) && \WP_MC_Core::get('apply_login')){!! \WP_MC_Captcha::output() !!}@endif
      <button type="submit">ادامه و ورود</button>
    </form>
  </section>

  <div class="toast" id="toast" role="status" aria-live="polite"><svg><use href="#i-check"/></svg><span></span></div>
