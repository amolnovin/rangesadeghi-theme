@extends('layouts.app')

@section('content')
  <main class="page-main" id="main-content">
    <div class="page-container">
      <nav class="breadcrumb" aria-label="مسیر صفحه">
        <a href="{{ home_url('/') }}">خانه</a><svg><use href="#i-chevron-left"/></svg><span class="current">فروشگاه های ما</span>
      </nav>

      <header class="stores-intro">
        <h1>فروشگاه‌های ما</h1>
        <p>مجموعه‌ی رنگ صادقی در دو فروشگاه تخصصی فعالیت می‌کند؛ هر کدام با تمرکز بر بخش متفاوتی از دنیای رنگ و هنر. برای ورود به هر فروشگاه، روی دکمه مربوطه کلیک کنید.</p>
      </header>

      <div class="stores-grid">
        <article class="store-card">
          <div class="store-top">
            <span class="store-logo"><img src="{{ rs_img($general['logo_desktop'] ?? 'assets/images/logo.png') }}" alt="رنگ صادقی"></span>
            <div>
              <h2>فروشگاه رنگ صادقی</h2>
              <a href="{{ home_url('/') }}">{{ parse_url(home_url('/'), PHP_URL_HOST) ?: 'rangsadeghy.com' }}</a>
            </div>
          </div>
          <p>مرجع جامع رنگ ساختمانی، صنعتی، پتینه، هنری و ابزارآلات حرفه‌ای؛ با بیش از ۲۰ برند معتبر و تمرکز بر مشاوره فنی برای پروژه‌های اجرایی و دکوراتیو.</p>
          <ul class="store-features">
            <li><svg><use href="#i-check-circle"/></svg><span>رنگ ساختمانی و صنعتی، تینر و چسب</span></li>
            <li><svg><use href="#i-check-circle"/></svg><span>پتینه، میکروسمنت، تکسچر و رنگ‌های دکوراتیو</span></li>
            <li><svg><use href="#i-check-circle"/></svg><span>رنگ هنری، رزین اپوکسی و مواد سفالگری</span></li>
            <li><svg><use href="#i-check-circle"/></svg><span>ابزار دستی و برقی: غلطک، قلم‌مو، ماله و کاردک</span></li>
          </ul>
          <div class="store-info">
            <div><svg><use href="#i-pin"/></svg><span>{{ $addressFa }}</span></div>
            <div><svg><use href="#i-clock"/></svg><span>{{ $workingHoursFa }}</span></div>
            <div><svg><use href="#i-phone"/></svg><span dir="ltr">{{ $phoneDisplay }}</span></div>
          </div>
          <div class="store-actions">
            <a class="btn btn-primary" href="{{ rs_page_url('shop') }}"><svg><use href="#i-globe"/></svg>ورود به فروشگاه</a>
            <a class="btn btn-outline" href="{{ rs_page_url('contact') }}"><svg><use href="#i-map"/></svg>مسیریابی</a>
          </div>
        </article>

        <article class="store-card naghshi">
          <div class="store-top">
            <span class="store-logo store-logo-text" aria-hidden="true">نقشینو</span>
            <div>
              <h2>فروشگاه نقشینو</h2>
              <a href="https://naghshinoart.com" target="_blank" rel="noopener">naghshinoart.com</a>
            </div>
          </div>
          <p>فروشگاه تخصصی شابلون و تابلو؛ با تنوع بی‌نظیر شابلون در ابعاد و طرح‌های مختلف، حروف کالیگرافی و بوم‌های نقاشی برای خلق تابلوهای دکوراتیو.</p>
          <ul class="store-features">
            <li><svg><use href="#i-check-circle"/></svg><span>شابلون ماندالا، داماسک، طرح فرش، کاشی و آیات</span></li>
            <li><svg><use href="#i-check-circle"/></svg><span>حروف کالیگرافی در بیش از ۵۰ مدل و سایزبندی</span></li>
            <li><svg><use href="#i-check-circle"/></svg><span>بوم نقاشی برای همه سبک‌های هنری و دکوراتیو</span></li>
            <li><svg><use href="#i-check-circle"/></svg><span>طرح‌های اختصاصی و سفارشی‌سازی</span></li>
          </ul>
          <div class="store-info">
            <div><svg><use href="#i-globe"/></svg><span>فروش آنلاین و ارسال به سراسر کشور</span></div>
            <div><svg><use href="#i-clock"/></svg><span>پشتیبانی آنلاین در ساعات کاری</span></div>
            <div><svg><use href="#i-truck"/></svg><span>ارسال با بسته‌بندی محافظ برای شابلون و بوم</span></div>
          </div>
          <div class="store-actions">
            <a class="btn btn-primary" href="https://naghshinoart.com" target="_blank" rel="noopener"><svg><use href="#i-globe"/></svg>ورود به فروشگاه</a>
            <a class="btn btn-outline" href="{{ add_query_arg('cat', 'stencil', rs_page_url('shop')) }}"><svg><use href="#i-grid"/></svg>دسته شابلون</a>
          </div>
        </article>
      </div>

      <section class="store-compare">
        <h2>مقایسه دو فروشگاه</h2>
        <table class="compare-table">
          <thead>
            <tr><th>ویژگی</th><th>رنگ صادقی (rangsadeghy.com)</th><th>نقشینو (naghshinoart.com)</th></tr>
          </thead>
          <tbody>
            <tr><td>تخصص اصلی</td><td>رنگ، پتینه، میکروسمنت و ابزار</td><td>شابلون، تابلو و بوم نقاشی</td></tr>
            <tr><td>رنگ ساختمانی و صنعتی</td><td>دارد</td><td>—</td></tr>
            <tr><td>متریال پتینه و دکوراتیو</td><td>دارد</td><td>محدود</td></tr>
            <tr><td>شابلون و حروف کالیگرافی</td><td>محدود</td><td>تنوع کامل</td></tr>
            <tr><td>بوم و بیس چوبی</td><td>دارد</td><td>تنوع کامل</td></tr>
            <tr><td>فروشگاه حضوری</td><td>آمل، مازندران</td><td>فروش آنلاین</td></tr>
            <tr><td>ارسال به سراسر کشور</td><td>دارد</td><td>دارد</td></tr>
          </tbody>
        </table>
      </section>

      <section class="map-wide">
        <div class="map-canvas" role="img" aria-label="نقشه فروشگاه حضوری رنگ صادقی">
          <span class="map-label">فروشگاه رنگ صادقی — آمل</span>
          <span class="map-pin"><svg><use href="#i-pin"/></svg></span>
        </div>
        <div class="map-foot">
          <span><strong>فروشگاه حضوری:</strong> {{ $addressFa }} — {{ $workingHoursFa }}</span>
          <a class="text-link" href="{{ $general['map_url'] ?? '#' }}" target="_blank" rel="noopener">مشاهده مسیر <svg><use href="#i-chevron-left"/></svg></a>
        </div>
      </section>

      <section class="page-section">
        <div class="cta-band">
          <div>
            <h2>کدام فروشگاه را انتخاب کنیم؟</h2>
            <p>برای رنگ، پتینه و ابزار سراغ رنگ صادقی بروید؛ برای شابلون، بوم و تابلو، نقشینو انتخاب شماست. در هر دو فروشگاه، پشتیبانی و ارسال یکسان است.</p>
          </div>
          <a class="btn" href="{{ rs_page_url('contact') }}"><svg><use href="#i-headset"/></svg>مشاوره انتخاب</a>
        </div>
      </section>
    </div>
  </main>
@endsection
