@extends('layouts.app')

@section('content')
  <main class="page-main" id="main-content">
    <div class="page-container">
      <nav class="breadcrumb" aria-label="مسیر صفحه">
        <a href="{{ home_url('/') }}">خانه</a><svg><use href="#i-chevron-left"/></svg><span class="current">وبلاگ</span>
      </nav>

      <div class="blog-top">
        <header class="page-head" style="margin-top:0">
          <h1>مجله رنگ صادقی</h1>
          <p>آموزش، بررسی و راهنمای خرید در دنیای رنگ، پتینه، میکروسمنت، رزین و ابزارهای نقاشی؛ نوشته‌شده توسط تیم فنی فروشگاه رنگ صادقی.</p>
          <div class="head-meta">
            <span><svg><use href="#i-book"/></svg>مقالات تخصصی</span>
            <span><svg><use href="#i-check-circle"/></svg>مطالب کارشناسی‌شده</span>
            <span><svg><use href="#i-clock"/></svg>به‌روزرسانی هفتگی</span>
          </div>
        </header>

        @if(!empty($featuredPost))
          <article class="featured-post">
            <img src="{{ $featuredPost['image'] }}" alt="{{ $featuredPost['title'] }}" fetchpriority="high">
            <div class="featured-body">
              <span class="post-cat">مقاله ویژه • {{ $featuredPost['cat'] }}</span>
              <h2><a href="{{ $featuredPost['url'] }}">{{ $featuredPost['title'] }}</a></h2>
              <p>{{ $featuredPost['excerpt'] }}</p>
              <div class="post-meta">
                <span><svg><use href="#i-calendar"/></svg>{{ $featuredPost['date'] }}</span>
                <span><svg><use href="#i-clock"/></svg>{{ $featuredPost['read'] }}</span>
                <span><svg><use href="#i-user"/></svg>{{ $featuredPost['author'] }}</span>
              </div>
            </div>
          </article>
        @endif

        <div class="blog-filterbar">
          <div class="chip-row">
            @foreach($blogCategories as $catSlug => $catLabel)
              <a class="chip {{ $activeCat === $catSlug ? 'active' : '' }}" href="{{ $catSlug === 'all' ? rs_page_url('blog') : add_query_arg('bcat', $catSlug, rs_page_url('blog')) }}">
                {{ $catLabel }}
              </a>
            @endforeach
          </div>
          <form class="blog-search" method="get" action="{{ rs_page_url('blog') }}">
            <input id="blog-search-input" name="bq" value="{{ $searchQuery }}" type="search" placeholder="جستجو در مقالات…" aria-label="جستجو در مقالات">
            <button type="submit" aria-label="جستجو"><svg><use href="#i-search"/></svg></button>
          </form>
        </div>
      </div>

      <div class="blog-layout">
        <div>
          <div class="blog-grid" id="blog-grid">
            @forelse($gridPosts as $post)
              <article class="post-card">
                <div class="post-card-media">
                  <a href="{{ $post['url'] }}"><img src="{{ $post['image'] }}" alt="{{ $post['title'] }}" loading="lazy"></a>
                  <span class="post-cat">{{ $post['cat'] }}</span>
                </div>
                <div class="post-card-body">
                  <div class="post-meta dark">
                    <span><svg><use href="#i-calendar"/></svg>{{ $post['date'] }}</span>
                    <span><svg><use href="#i-clock"/></svg>{{ $post['read'] }}</span>
                  </div>
                  <h3><a href="{{ $post['url'] }}">{{ $post['title'] }}</a></h3>
                  <p>{{ $post['excerpt'] }}</p>
                  <div class="post-card-foot">
                    <span>{{ $post['author'] }}</span>
                    <a class="read" href="{{ $post['url'] }}">ادامه مطلب <svg><use href="#i-chevron-left"/></svg></a>
                  </div>
                </div>
              </article>
            @empty
              <div class="empty-state" style="grid-column:1/-1">
                <svg><use href="#i-book"/></svg>
                <h3>مطلبی یافت نشد</h3>
                <a class="btn btn-primary btn-sm" href="{{ rs_page_url('blog') }}">نمایش همه مقالات</a>
              </div>
            @endforelse
          </div>

          @if($totalPages > 1)
            <nav class="pagination" aria-label="صفحه‌بندی مقالات">
              @for($i = 1; $i <= $totalPages; $i++)
                <a href="{{ get_pagenum_link($i) }}" class="{{ $i === $paged ? 'active' : '' }}">{{ rs_fa($i) }}</a>
              @endfor
            </nav>
          @endif
        </div>

        <aside class="blog-side">
          <section class="side-card">
            <h3><svg><use href="#i-sparkles"/></svg>پیشنهاد ویژه فروشگاه</h3>
            <p style="font-size:13px;color:var(--muted);margin:8px 0 14px">تکسچر، رنگ دکوراتیو، میکروسمنت و ابزار حرفه‌ای با ارسال سریع از انبار آمل.</p>
            <a class="btn btn-primary btn-sm btn-block" href="{{ rs_page_url('shop') }}">ورود به فروشگاه</a>
          </section>

          <section class="side-card">
            <h3><svg><use href="#i-mail"/></svg>خبرنامه آموزشی</h3>
            <p style="font-size:13px;color:var(--muted);margin:8px 0 12px">جدیدترین تکنیک‌های پتینه و تخفیف‌ها را دریافت کنید.</p>
            <form data-newsletter-form style="display:grid;gap:8px">
              <input type="text" name="contact" required placeholder="ایمیل یا شماره موبایل">
              @if(class_exists('WP_MC_Captcha') && class_exists('WP_MC_Core') && \WP_MC_Core::get('enabled', 1)){!! \WP_MC_Captcha::output() !!}@endif
              <button class="btn btn-outline btn-sm" type="submit">عضویت در خبرنامه</button>
            </form>
          </section>
        </aside>
      </div>
    </div>
  </main>
@endsection
