@extends('layouts.app')

@section('content')
  <div class="read-progress" id="read-progress" aria-hidden="true"><span></span></div>
  <main class="page-main" id="main-content">
    <div class="page-container">
      @if(!empty($article))
        <nav class="breadcrumb" aria-label="مسیر صفحه">
          <a href="{{ home_url('/') }}">خانه</a><svg><use href="#i-chevron-left"/></svg>
          <a href="{{ rs_page_url('blog') }}">وبلاگ</a><svg><use href="#i-chevron-left"/></svg>
          <span class="current">{{ $article['title'] }}</span>
        </nav>

        <header class="article-head">
          <span class="post-cat">{{ $article['cat'] }}</span>
          <h1>{{ $article['title'] }}</h1>
          <div class="post-meta">
            <span class="article-author"><img src="{{ $article['image'] }}" alt="">{{ $article['author'] }}</span>
            <span><svg><use href="#i-calendar"/></svg>{{ $article['date'] }}</span>
            <span><svg><use href="#i-clock"/></svg>{{ $article['read'] }}</span>
          </div>
        </header>

        <figure class="article-cover">
          <img src="{{ $article['image'] }}" alt="{{ $article['title'] }}" fetchpriority="high">
        </figure>

        <div class="article-layout">
          <article class="article-card">
            <div class="article-body">
              {!! $article['content'] !!}
            </div>

            @if(!empty($article['tags']))
              <div class="article-tags">
                @foreach($article['tags'] as $tag)
                  <a href="{{ add_query_arg('bq', $tag, rs_page_url('blog')) }}">#{{ str_replace(' ', '_', $tag) }}</a>
                @endforeach
              </div>
            @endif

            <div class="share-row">
              <span>اشتراک‌گذاری:</span>
              <a href="https://t.me/share/url?url={{ rawurlencode($article['url']) }}" target="_blank" rel="noopener" aria-label="اشتراک در تلگرام"><svg><use href="#i-send"/></svg></a>
              <a href="https://wa.me/?text={{ rawurlencode($article['url']) }}" target="_blank" rel="noopener" aria-label="اشتراک در واتساپ"><svg><use href="#i-whatsapp"/></svg></a>
              <button type="button" data-copy="{{ $article['url'] }}" aria-label="کپی لینک"><svg><use href="#i-link"/></svg></button>
            </div>

            <div class="author-box">
              <img src="{{ $article['image'] }}" alt="{{ $article['author'] }}">
              <div>
                <h3>{{ $article['author'] }}</h3>
                <p>محتوای تخصصی این مجله با همکاری کارشناسان فنی فروشگاه رنگ صادقی و بر اساس تجربه اجرای پروژه‌های رنگ و پتینه تهیه می‌شود.</p>
              </div>
            </div>

            @php
              $prevPost = get_previous_post();
              $nextPost = get_next_post();
            @endphp
            @if($prevPost || $nextPost)
              <div class="post-nav">
                @if($prevPost)
                  <a href="{{ get_permalink($prevPost) }}"><small>مقاله قبلی</small>{{ get_the_title($prevPost) }}</a>
                @endif
                @if($nextPost)
                  <a href="{{ get_permalink($nextPost) }}"><small>مقاله بعدی</small>{{ get_the_title($nextPost) }}</a>
                @endif
              </div>
            @endif

            @if(comments_open((int) $article['id']) || !empty($postComments))
              <section class="comments" id="comments">
                <h2 style="font-size:18px;margin:22px 0 10px">دیدگاه‌ها ({{ $article['comments_count'] }})</h2>

                @foreach($postComments as $cm)
                  <div class="comment">
                    <span class="avatar">{{ mb_substr($cm->comment_author ?: 'ک', 0, 1) }}</span>
                    <div>
                      <strong>{{ $cm->comment_author ?: 'کاربر سایت' }}</strong>
                      <span>{{ rs_jalali(strtotime($cm->comment_date_gmt ?: 'now')) }}</span>
                      <p>{{ wp_strip_all_tags($cm->comment_content) }}</p>
                    </div>
                  </div>
                @endforeach

                @if(comments_open((int) $article['id']))
                  <form action="{{ site_url('/wp-comments-post.php') }}" method="post" class="comment-form" style="margin-top:18px">
                    <input type="hidden" name="comment_post_ID" value="{{ $article['id'] }}">
                    <div class="form-grid">
                      @if(!is_user_logged_in())
                        <div class="field"><label for="c-name">نام <span>*</span></label><input id="c-name" name="author" required placeholder="نام شما"></div>
                        <div class="field"><label for="c-mail">ایمیل <span>*</span></label><input id="c-mail" name="email" type="email" required placeholder="you@example.com" dir="ltr"></div>
                      @endif
                      <div class="field full"><label for="c-text">دیدگاه شما <span>*</span></label><textarea id="c-text" name="comment" required placeholder="دیدگاه خود را بنویسید…"></textarea></div>
                    </div>
                    <button class="btn btn-primary" type="submit" style="margin-top:12px">ارسال دیدگاه</button>
                  </form>
                @endif
              </section>
            @endif
          </article>

          <aside class="article-side" aria-label="ابزارهای مقاله">
            @if(!empty($toc))
              <section class="side-card">
                <h3><svg><use href="#i-list"/></svg>فهرست مطالب</h3>
                <ul class="toc" id="article-toc">
                  @foreach($toc as $t)
                    <li><a href="#{{ $t['id'] }}">{{ $t['title'] }}</a></li>
                  @endforeach
                </ul>
              </section>
            @endif

            <section class="side-cta">
              <h3>خرید متریال پتینه و رنگ</h3>
              <p>تکسچر، رنگ دکوراتیو، ورق طلا و ابزار اجرا با ضمانت اصالت کالا.</p>
              <a class="btn btn-primary btn-sm btn-block" href="{{ rs_page_url('shop') }}" style="margin-top:10px">مشاهده محصولات</a>
            </section>

            @if(!empty($sidebarProducts))
              <section class="side-card">
                <h3><svg><use href="#i-bag"/></svg>محصولات مرتبط</h3>
                <div style="display:grid;gap:10px;margin-top:10px">
                  @foreach($sidebarProducts as $sp)
                    <a href="{{ $sp['url'] }}" style="display:flex;align-items:center;gap:10px;text-decoration:none;color:inherit">
                      <img src="{{ $sp['image'] }}" alt="{{ $sp['title'] }}" style="width:52px;height:52px;border-radius:10px;object-fit:cover" loading="lazy">
                      <div style="font-size:13px">
                        <b style="display:block;line-height:1.4">{{ $sp['title'] }}</b>
                        <small style="color:var(--accent)">{{ rs_price($sp['price']) }} تومان</small>
                      </div>
                    </a>
                  @endforeach
                </div>
              </section>
            @endif
          </aside>
        </div>

        @if(!empty($relatedPosts))
          <section class="page-section">
            <div class="section-head">
              <div><h2>مقالات مرتبط</h2><p>مطالعه بیشتر در مجله رنگ صادقی</p></div>
              <a class="section-link" href="{{ rs_page_url('blog') }}">همه مقالات <svg><use href="#i-chevron-left"/></svg></a>
            </div>
            <div class="blog-grid">
              @foreach($relatedPosts as $rp)
                <article class="post-card">
                  <div class="post-card-media">
                    <a href="{{ $rp['url'] }}"><img src="{{ $rp['image'] }}" alt="{{ $rp['title'] }}" loading="lazy"></a>
                    <span class="post-cat">{{ $rp['cat'] }}</span>
                  </div>
                  <div class="post-card-body">
                    <h3><a href="{{ $rp['url'] }}">{{ $rp['title'] }}</a></h3>
                    <div class="post-card-foot">
                      <span>{{ $rp['read'] }}</span>
                      <a class="read" href="{{ $rp['url'] }}">ادامه مطلب <svg><use href="#i-chevron-left"/></svg></a>
                    </div>
                  </div>
                </article>
              @endforeach
            </div>
          </section>
        @endif
      @endif
    </div>
  </main>
@endsection
