@extends('layouts.app')

@section('content')
  @php
    $brands = \App\Support\DemoCatalog::brands();
  @endphp
  <main class="page-main" id="main-content">
    <div class="page-container">
      <nav class="breadcrumb" aria-label="مسیر صفحه">
        <a href="{{ home_url('/') }}">خانه</a><svg><use href="#i-chevron-left"/></svg><span class="current">درباره ما</span>
      </nav>

      <section class="about-hero">
        <div>
          <span class="hero-badge"><svg><use href="#i-award"/></svg>بیش از ۲۰ برند معتبر</span>
          <h1>درباره رنگ صادقی؛ مرجع جامع رنگ، پتینه و ابزار</h1>
          <p class="lead">فروشگاه رنگ صادقی با تمرکز تخصصی بر رنگ ساختمانی، رنگ صنعتی، رنگ پتینه، هنری و ابزارآلات دستی و برقی حرفه‌ای فعالیت می‌کند. هدف ما ارائه‌ی مجموعه‌ای کامل از محصولات معتبر است تا مشتریان بتوانند خریدی امن، سریع و با اطلاعات دقیق را تجربه کنند.</p>
          <div class="about-cta">
            <a class="btn btn-primary" href="{{ rs_page_url('shop') }}"><svg><use href="#i-bag"/></svg>مشاهده فروشگاه</a>
            <a class="btn btn-outline" href="{{ rs_page_url('contact') }}"><svg><use href="#i-phone"/></svg>تماس با ما</a>
          </div>
        </div>
        <img src="{{ rs_img('blog/patina.webp') }}" alt="فضای فروشگاه و متریال رنگ صادقی">
      </section>

      <div class="stats">
        <div class="stat"><b><span data-count="20">۲۰</span><i>+</i></b><span>برند معتبر</span></div>
        <div class="stat"><b><span data-count="1500">۱۵۰۰</span><i>+</i></b><span>کالای متنوع</span></div>
        <div class="stat"><b><span data-count="12">۱۲</span><i>+</i></b><span>سال تجربه اجرا</span></div>
        <div class="stat"><b><span data-count="9000">۹۰۰۰</span><i>+</i></b><span>مشتری راضی</span></div>
      </div>

      <section class="about-story">
        <img src="{{ rs_img('blog/tools.webp') }}" alt="ابزار و متریال حرفه‌ای رنگ صادقی" loading="lazy">
        <div>
          <h2>داستان ما</h2>
          <p>رنگ صادقی از یک فروشگاه تخصصی رنگ در آمل شروع شد؛ جایی که تمرکز اصلی بر تأمین رنگ ساختمانی و ابزار نقاشی بود. با رشد پروژه‌های دکوراتیو و ورود متریال‌های جدید مثل پتینه و میکروسمنت، این مسیر به سمت تکمیل سبد محصولات حرفه‌ای پیش رفت.</p>
          <p>امروز در کنار رنگ و ابزار، رنگ‌های هنری، رزین اپوکسی، شابلون، بوم و مواد سفالگری را نیز تأمین می‌کنیم؛ با این باور که یک پروژه خوب، پیش از هر چیز به <strong>متریال درست</strong> و <strong>مشاوره دقیق</strong> نیاز دارد.</p>
          <ul class="about-list">
            <li><svg><use href="#i-check-circle"/></svg><span>تأمین کالای اصل از برندهای شناخته‌شده داخلی و خارجی</span></li>
            <li><svg><use href="#i-check-circle"/></svg><span>مشاوره تخصصی پیش از خرید، بر اساس نوع سطح و شرایط اجرا</span></li>
            <li><svg><use href="#i-check-circle"/></svg><span>ارسال سریع به سراسر کشور از انبار آمل</span></li>
          </ul>
        </div>
      </section>

      <section class="page-section">
        <div class="section-head">
          <div><h2>چرا رنگ صادقی؟</h2><p>چهار اصلی که در هر سفارش رعایت می‌کنیم</p></div>
        </div>
        <div class="value-grid">
          <article class="value-card"><span class="value-icon"><svg><use href="#i-shield"/></svg></span><h3>اصالت و کیفیت</h3><p>همه‌ی محصولات با ضمانت اصالت عرضه می‌شوند و از مبادی رسمی تأمین می‌گردند.</p></article>
          <article class="value-card"><span class="value-icon"><svg><use href="#i-headset"/></svg></span><h3>مشاوره تخصصی</h3><p>کارشناسان ما با تجربه اجرای پروژه، بهترین متریال را برای سطح شما پیشنهاد می‌دهند.</p></article>
          <article class="value-card"><span class="value-icon"><svg><use href="#i-truck"/></svg></span><h3>ارسال سریع</h3><p>ارسال به سراسر کشور با پست پیشتاز و باربری؛ تحویل در مازندران با پیک.</p></article>
          <article class="value-card"><span class="value-icon"><svg><use href="#i-layers"/></svg></span><h3>تنوع بی‌نظیر</h3><p>از رنگ ساختمانی و صنعتی تا پتینه، رزین، شابلون و ابزار حرفه‌ای در یک فروشگاه.</p></article>
        </div>
      </section>

      <section class="page-section">
        <div class="section-head">
          <div><h2>گوشه‌ای از کارگاه و پروژه‌ها</h2><p>تصاویری از متریال و اجراهای انجام‌شده</p></div>
        </div>
        <div class="about-gallery">
          <a href="{{ rs_page_url('blog') }}"><img src="{{ rs_img('blog/patina.webp') }}" alt="پتینه کاری" loading="lazy"></a>
          <a href="{{ rs_page_url('blog') }}"><img src="{{ rs_img('blog/metallic.webp') }}" alt="رنگ متالیک" loading="lazy"></a>
          <a href="{{ rs_page_url('blog') }}"><img src="{{ rs_img('blog/microcement.webp') }}" alt="میکروسمنت" loading="lazy"></a>
          <a href="{{ rs_page_url('blog') }}"><img src="{{ rs_img('blog/gold-leaf.webp') }}" alt="ورق طلا" loading="lazy"></a>
          <a href="{{ rs_page_url('blog') }}"><img src="{{ rs_img('blog/tools.webp') }}" alt="ابزار نقاشی" loading="lazy"></a>
          <a href="{{ rs_page_url('blog') }}"><img src="{{ rs_img('promo-resin.webp') }}" alt="رزین" loading="lazy"></a>
        </div>
      </section>

      <section class="brand-strip">
        <h2>با بهترین برندها کار می‌کنیم</h2>
        <p>در رنگ صادقی تلاش می‌کنیم بهترین انتخاب را برای شما فراهم کنیم</p>
        <div class="brand-marquee">
          <div class="brand-track">
            @foreach(array_merge($brands, $brands) as $b)
              <a class="brand-item" href="{{ add_query_arg('brand', $b['slug'], rs_page_url('shop')) }}" aria-label="{{ $b['title'] }}">
                <img src="{{ rs_img($b['image']) }}" alt="{{ $b['title'] }}" loading="lazy">
              </a>
            @endforeach
          </div>
        </div>
      </section>

      <section class="page-section">
        <div class="cta-band">
          <div>
            <h2>پروژه‌ای در پیش دارید؟</h2>
            <p>برای انتخاب متریال، برآورد مقدار و قیمت، با کارشناسان ما در تماس باشید.</p>
          </div>
          <a class="btn" href="{{ rs_page_url('contact') }}"><svg><use href="#i-send"/></svg>درخواست مشاوره</a>
        </div>
      </section>
    </div>
  </main>
@endsection
