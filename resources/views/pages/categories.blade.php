@extends('layouts.app')

@section('content')
  @php
    $shopUrl = rs_page_url('shop');
    $groups = [
      [
        'slug' => 'patina',
        'title' => 'پتینه',
        'image' => rs_img('blog/patina.webp'),
        'desc' => 'تکسچر، میکروسمنت، رنگ‌های دکوراتیو، ورق طلا و تثبیت‌کننده‌ها برای اجرای دیوارهای لوکس و یکپارچه.',
        'subs' => ['تکسچرها', 'میکروسمنت', 'رنگ دکوراتیو', 'ورق طلا', 'تثبیت‌کننده', 'افکت عنکبوتی'],
      ],
      [
        'slug' => 'art',
        'title' => 'بخش هنری',
        'image' => rs_img('blog/metallic.webp'),
        'desc' => 'رنگ اکریلیک و روغن هنری، رزین اپوکسی، مواد سفالگری، افکت و وارنیش برای هنرمندان و هنرجویان.',
        'subs' => ['رنگ اکریلیک', 'رنگ روغن', 'رزین اپوکسی', 'سفالگری', 'افکت', 'وارنیش'],
      ],
      [
        'slug' => 'tools',
        'title' => 'ابزارها',
        'image' => rs_img('blog/tools.webp'),
        'desc' => 'غلطک، قلم‌مو، لیسه و کاردک، ماله، سنباده و ابزارهای برقی حرفه‌ای برای اجرای بی‌نقص.',
        'subs' => ['غلطک', 'قلم‌مو', 'لیسه و کاردک', 'ماله', 'سنباده', 'ابزار برقی'],
      ],
      [
        'slug' => 'stencil',
        'title' => 'شابلون و تابلو',
        'image' => rs_img('cat-stencil-photo.webp'),
        'desc' => 'شابلون ماندالا، داماسک و کالیگرافی، بوم نقاشی و بیس‌های چوبی با طرح‌های اختصاصی.',
        'subs' => ['شابلون', 'ماندالا', 'داماسک', 'کالیگرافی', 'بوم', 'بیس چوبی'],
      ],
      [
        'slug' => 'building',
        'title' => 'ساختمانی و صنعتی',
        'image' => rs_img('cat-building-photo.webp'),
        'desc' => 'رنگ ساختمانی و صنعتی، تینر و حلال، چسب کاغذی، بتونه و آستری برای پروژه‌های اجرایی.',
        'subs' => ['رنگ ساختمانی', 'رنگ صنعتی', 'تینر', 'چسب', 'بتونه', 'آستری'],
      ],
    ];
  @endphp

  <main class="page-main" id="main-content">
    <div class="page-container">
      <nav class="breadcrumb" aria-label="مسیر صفحه">
        <a href="{{ home_url('/') }}">خانه</a><svg><use href="#i-chevron-left"/></svg><span class="current">دسته بندی محصولات</span>
      </nav>

      <header class="page-head">
        <h1>دسته بندی محصولات</h1>
        <p>محصولات فروشگاه رنگ صادقی در پنج گروه اصلی دسته‌بندی شده‌اند؛ برای دیدن زیرشاخه‌ها و محصولات هر گروه، روی دسته‌بندی مورد نظر کلیک کنید.</p>
        <div class="head-meta">
          <span><svg><use href="#i-layers"/></svg>۵ گروه اصلی</span>
          <span><svg><use href="#i-grid"/></svg>بیش از ۳۰ زیرشاخه</span>
          <span><svg><use href="#i-check-circle"/></svg>برندهای معتبر</span>
        </div>
      </header>

      <div class="cat-grid">
        @foreach($groups as $g)
          @php $catLink = add_query_arg('cat', $g['slug'], $shopUrl); @endphp
          <article class="cat-card">
            <div class="cat-card-media">
              <img src="{{ $g['image'] }}" alt="{{ $g['title'] }}" loading="lazy">
            </div>
            <div class="cat-card-body">
              <h2><a href="{{ $catLink }}">{{ $g['title'] }} <svg width="16" height="16"><use href="#i-chevron-left"/></svg></a></h2>
              <p>{{ $g['desc'] }}</p>
              <div class="cat-sub-list">
                @foreach($g['subs'] as $sub)
                  <a href="{{ $catLink }}">{{ $sub }}</a>
                @endforeach
              </div>
              <div class="cat-card-foot">
                <span>ارسال سریع • ضمانت اصالت</span>
                <a class="text-link" href="{{ $catLink }}">مشاهده محصولات <svg><use href="#i-chevron-left"/></svg></a>
              </div>
            </div>
          </article>
        @endforeach
      </div>

      <section class="page-section">
        <div class="section-head">
          <div><h2>محبوب‌ترین دسته‌بندی‌ها</h2><p>پرجستجوترین گروه‌های محصول در رنگ صادقی</p></div>
        </div>
        <div class="subcat-tiles">
          <a class="subcat-tile" href="{{ add_query_arg('cat', 'patina', $shopUrl) }}"><img src="{{ rs_img('popular-micro.webp') }}" alt="" loading="lazy"><div><strong>میکروسمنت</strong><span>دیوار و کف یکپارچه</span></div></a>
          <a class="subcat-tile" href="{{ add_query_arg('cat', 'patina', $shopUrl) }}"><img src="{{ rs_img('best-ceramic.webp') }}" alt="" loading="lazy"><div><strong>تکسچر سرامیک</strong><span>پایه پتینه‌کاری</span></div></a>
          <a class="subcat-tile" href="{{ add_query_arg('cat', 'patina', $shopUrl) }}"><img src="{{ rs_img('sale-spider-gold.webp') }}" alt="" loading="lazy"><div><strong>تار عنکبوتی</strong><span>افکت متالیک</span></div></a>
          <a class="subcat-tile" href="{{ add_query_arg('cat', 'tools', $shopUrl) }}"><img src="{{ rs_img('popular-male.webp') }}" alt="" loading="lazy"><div><strong>ماله و کاردک</strong><span>ابزار اجرا</span></div></a>
          <a class="subcat-tile" href="{{ add_query_arg('cat', 'tools', $shopUrl) }}"><img src="{{ rs_img('best-roller.webp') }}" alt="" loading="lazy"><div><strong>غلطک</strong><span>پوشش یکدست</span></div></a>
          <a class="subcat-tile" href="{{ add_query_arg('cat', 'art', $shopUrl) }}"><img src="{{ rs_img('popular-oil.webp') }}" alt="" loading="lazy"><div><strong>رنگ روغن</strong><span>نقاشی هنری</span></div></a>
          <a class="subcat-tile" href="{{ add_query_arg('cat', 'art', $shopUrl) }}"><img src="{{ rs_img('popular-resin.webp') }}" alt="" loading="lazy"><div><strong>رزین اپوکسی</strong><span>کارهای هنری</span></div></a>
          <a class="subcat-tile" href="{{ add_query_arg('cat', 'stencil', $shopUrl) }}"><img src="{{ rs_img('new-canvas-100.webp') }}" alt="" loading="lazy"><div><strong>بوم نقاشی</strong><span>تابلو دکوراتیو</span></div></a>
        </div>
      </section>

      <section class="page-section">
        <div class="cta-band">
          <div>
            <h2>دسته‌بندی مورد نظرتان را پیدا نکردید؟</h2>
            <p>کافیست نام محصول یا برند را برای ما بفرستید؛ تیم پشتیبانی سریع‌ترین مسیر را پیشنهاد می‌دهد.</p>
          </div>
          <a class="btn" href="{{ rs_page_url('contact') }}"><svg><use href="#i-phone"/></svg>تماس با پشتیبانی</a>
        </div>
      </section>
    </div>
  </main>
@endsection
