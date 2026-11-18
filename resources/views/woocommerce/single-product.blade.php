@extends('layouts.app')

@section('content')
  @php
    $fullStars = (int) round((float) ($product['rating'] ?? 4.8));
    $starsText = str_repeat('★', max(1, min(5, $fullStars))) . str_repeat('☆', max(0, 5 - $fullStars));
    $reviewsTotal = max(count($reviews), (int) ($product['reviews'] ?? 0));
    $shippingBullets = array_filter(array_map('trim', explode("\n", (string) ($productCfg['shipping_tab_bullets'] ?? ''))));
  @endphp

  <main class="page-main" id="main-content">
    <div class="page-container">
      <nav class="breadcrumb" aria-label="مسیر صفحه">
        <a href="{{ home_url('/') }}">خانه</a><svg><use href="#i-chevron-left"/></svg>
        <a href="{{ rs_page_url('shop') }}">فروشگاه</a><svg><use href="#i-chevron-left"/></svg>
        <a href="{{ $product['cat_url'] }}" id="product-breadcrumb-cat">{{ $product['cat'] }}</a><svg><use href="#i-chevron-left"/></svg>
        <span class="current" id="product-breadcrumb-current">{{ rs_fa($product['title']) }}</span>
      </nav>

      <div class="product-layout" data-single-product data-product-id="{{ $product['id'] }}" data-base-price="{{ $product['price'] }}" data-variations="{{ esc_attr(wp_json_encode($variations)) }}">
        <div class="gallery">
          <div class="gallery-main" id="gallery-main">
            <img src="{{ $product['image'] }}" alt="{{ $product['title'] }}" fetchpriority="high">
            <span class="gallery-badge"><svg><use href="#i-shield"/></svg>{{ $productCfg['warranty_label'] ?? 'اصالت کالا' }}</span>
            @if(!empty($productCfg['gallery_zoom']))
              <button class="gallery-zoom" type="button" aria-label="بزرگ‌نمایی تصویر"><svg><use href="#i-zoom"/></svg></button>
            @endif
          </div>
          @if(count($product['gallery'] ?? []) > 1)
            <div class="gallery-thumbs" id="gallery-thumbs" aria-label="گالری تصاویر محصول">
              @foreach($product['gallery'] as $gIdx => $gImg)
                <button type="button" class="{{ $gIdx === 0 ? 'active' : '' }}" data-thumb="{{ $gImg }}" aria-label="تصویر {{ $gIdx + 1 }}">
                  <img src="{{ $gImg }}" alt="{{ $product['title'] }}" loading="lazy">
                </button>
              @endforeach
            </div>
          @endif
        </div>

        <div class="product-summary">
          @if(!empty($productCfg['show_brand']) || !empty($productCfg['show_sku']))
            <div class="brand-line">
              @if(!empty($productCfg['show_brand']))
                <span>برند: <a href="{{ $product['brand_url'] }}" id="product-brand">{{ $product['brand_title'] }}</a></span>
              @endif
              @if(!empty($productCfg['show_brand']) && !empty($productCfg['show_sku']))
                <span class="dot"></span>
              @endif
              @if(!empty($productCfg['show_sku']))
                <span>کد کالا: <bdo id="product-sku">{{ rs_fa($product['sku']) }}</bdo></span>
              @endif
            </div>
          @endif

          <h1 class="product-h1" id="product-title">{{ rs_fa($product['title']) }}</h1>

          @if(!empty($productCfg['show_rating_row']))
            <div class="rating-row">
              <span class="stars" id="product-rating">{{ $starsText }}</span>
              <span class="rate-value"><bdo id="product-rate-value">{{ rs_fa($product['rating'] ?: 4.8) }}</bdo> از ۵</span>
              <a href="#tab-reviews" id="product-reviews">({{ rs_fa($reviewsTotal) }} دیدگاه)</a>
              @if(($product['sold'] ?? 0) > 0)
                <span aria-hidden="true">•</span>
                <span>بیش از {{ rs_fa($product['sold']) }} فروش موفق</span>
              @endif
            </div>
          @endif

          <div class="price-block">
            <span class="now"><bdo id="product-price">{{ rs_price($product['price']) }}</bdo><small>تومان</small></span>
            @if(!empty($product['old']) && $product['old'] > $product['price'])
              <span class="was" id="product-old">{{ rs_price($product['old']) }} تومان</span>
            @endif
            @if(($product['discount'] ?? 0) > 0)
              <span class="off" id="product-off">-{{ rs_fa($product['discount']) }}%</span>
            @endif
            @if($product['in_stock'])
              <span class="stock" id="product-stock"><svg><use href="#i-check"/></svg> موجود در انبار</span>
            @else
              <span class="stock is-out" id="product-stock">ناموجود</span>
            @endif
          </div>

          @if(!empty($product['short']))
            <p class="short-desc" id="product-short">{{ $product['short'] }}</p>
          @endif

          @if(!empty($attributes))
            @foreach($attributes as $attrName => $attrValues)
              <div class="variant-group">
                <label>{{ wc_attribute_label($attrName) }}</label>
                <div class="variant-options" data-wc-attribute="attribute_{{ sanitize_title($attrName) }}">
                  @foreach($attrValues as $vIdx => $val)
                    <button type="button" class="{{ $vIdx === 0 ? 'active' : '' }}" data-attr-val="{{ $val }}">{{ $val }}</button>
                  @endforeach
                </div>
              </div>
            @endforeach
          @endif

          <div class="buy-row">
            <div class="qty-stepper">
              <button type="button" id="qty-minus" aria-label="کاهش تعداد"><svg><use href="#i-minus"/></svg></button>
              <input id="qty-input" value="۱" data-qty="1" inputmode="numeric" aria-label="تعداد" readonly>
              <button type="button" id="qty-plus" aria-label="افزایش تعداد"><svg><use href="#i-plus"/></svg></button>
            </div>
            <button class="btn btn-primary" type="button" id="product-add" data-single-add="{{ $product['id'] }}" {{ !$product['in_stock'] ? 'disabled' : '' }}>
              <svg><use href="#i-bag"/></svg>{{ $product['in_stock'] ? 'افزودن به سبد خرید' : 'ناموجود' }}
            </button>
            <button class="icon-action {{ $isWished ? 'active' : '' }}" type="button" data-wish="{{ $product['id'] }}" aria-label="افزودن به علاقه‌مندی" aria-pressed="{{ $isWished ? 'true' : 'false' }}">
              <svg><use href="#i-heart"/></svg>
            </button>
          </div>

          @if(!empty($productCfg['show_trust_badges']))
            <div class="product-trust">
              <div><svg><use href="#i-truck"/></svg><span>ارسال سریع به سراسر کشور</span></div>
              <div><svg><use href="#i-shield"/></svg><span>تضمین اصالت و کیفیت</span></div>
              <div><svg><use href="#i-refresh"/></svg><span>۷ روز ضمانت بازگشت</span></div>
            </div>
          @endif

          <ul class="product-meta">
            <li><b>دسته:</b> <a href="{{ $product['cat_url'] }}">{{ $product['cat'] }}</a></li>
            <li><b>برند:</b> <span id="product-brand-2">{{ $product['brand_title'] }}</span></li>
            <li><b>ارسال از:</b> {{ $productCfg['warehouse_label'] ?? 'انبار آمل، مازندران' }}</li>
            <li><b>گارانتی:</b> {{ $productCfg['warranty_label'] ?? 'اصالت کالا' }}</li>
          </ul>
        </div>
      </div>

      <section class="product-tabs">
        <div class="tab-nav" role="tablist">
          @if(!empty($productCfg['tab_description']))
            <button type="button" class="active" data-tab="tab-description" role="tab">توضیحات</button>
          @endif
          @if(!empty($productCfg['tab_specs']))
            <button type="button" data-tab="tab-specs" role="tab">مشخصات فنی</button>
          @endif
          @if(!empty($productCfg['tab_reviews']))
            <button type="button" data-tab="tab-reviews-panel" role="tab" id="tab-reviews"><span id="product-tab-reviews">نظرات ({{ rs_fa($reviewsTotal) }})</span></button>
          @endif
          @if(!empty($productCfg['tab_shipping']))
            <button type="button" data-tab="tab-shipping" role="tab">ارسال و مرجوعی</button>
          @endif
        </div>

        @if(!empty($productCfg['tab_description']))
          <div class="tab-panel active" id="tab-description">
            <h3>درباره این محصول</h3>
            <div id="product-desc">{!! $product['description'] !!}</div>
          </div>
        @endif

        @if(!empty($productCfg['tab_specs']))
          <div class="tab-panel" id="tab-specs">
            <h3>مشخصات فنی</h3>
            <table class="spec-table">
              <tbody id="spec-rows">
                @forelse($product['specs'] as $specRow)
                  <tr>
                    <th>{{ $specRow[0] ?? '' }}</th>
                    <td>{{ rs_fa($specRow[1] ?? '') }}</td>
                  </tr>
                @empty
                  <tr><th>برند</th><td>{{ $product['brand_title'] }}</td></tr>
                  <tr><th>کد کالا</th><td>{{ rs_fa($product['sku']) }}</td></tr>
                  <tr><th>دسته‌بندی</th><td>{{ $product['cat'] }}</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        @endif

        @if(!empty($productCfg['tab_reviews']))
          <div class="tab-panel" id="tab-reviews-panel">
            <h3>دیدگاه خریداران</h3>
            <div class="review-summary">
              <div class="review-score">
                <b>{{ rs_fa($product['rating'] ?: 4.8) }}</b>
                <span>از {{ rs_fa($reviewsTotal) }} دیدگاه</span>
                <div class="stars">{{ $starsText }}</div>
              </div>
              <div class="bars">
                <span class="bar"><span>۵</span><i><b style="width:82%"></b></i><span>{{ rs_fa(max(1, (int) round($reviewsTotal * 0.8))) }}</span></span>
                <span class="bar"><span>۴</span><i><b style="width:14%"></b></i><span>{{ rs_fa((int) round($reviewsTotal * 0.14)) }}</span></span>
                <span class="bar"><span>۳</span><i><b style="width:4%"></b></i><span>{{ rs_fa((int) round($reviewsTotal * 0.06)) }}</span></span>
                <span class="bar"><span>۲</span><i><b style="width:0"></b></i><span>۰</span></span>
                <span class="bar"><span>۱</span><i><b style="width:0"></b></i><span>۰</span></span>
              </div>
            </div>

            @forelse($reviews as $rev)
              <div class="review">
                <span class="avatar">{{ mb_substr($rev['name'], 0, 1) }}</span>
                <div>
                  <div class="review-head">
                    <strong>{{ $rev['name'] }}</strong>
                    <span>{{ $rev['date'] }}</span>
                    <span class="stars">{{ str_repeat('★', $rev['rating']) . str_repeat('☆', 5 - $rev['rating']) }}</span>
                  </div>
                  <p>{{ $rev['text'] }}</p>
                  @if(!empty($rev['images']))<div style="display:flex;gap:8px;flex-wrap:wrap">@foreach($rev['images'] as $image)<a href="{{ $image }}" target="_blank" rel="noopener noreferrer"><img src="{{ $image }}" alt="تصویر دیدگاه محصول" style="width:96px;height:96px;object-fit:cover;border-radius:8px" loading="lazy"></a>@endforeach</div>@endif
                </div>
              </div>
            @empty
              <div class="review">
                <span class="avatar">م</span>
                <div>
                  <div class="review-head"><strong>مهدی رضایی</strong><span>خریدار تأییدشده</span><span class="stars">★★★★★</span></div>
                  <p>کیفیت بسته‌بندی و اصالت محصول عالی بود و دقیقاً مطابق مشخصات سایت تحویل شد.</p>
                </div>
              </div>
            @endforelse

            @if(!empty($product['is_wc']) && comments_open((int) $product['wc_id']))
              <div class="rs-review-form-wrap" style="margin-top:24px;padding-top:20px;border-top:1px solid var(--border)">
                <h4>ثبت دیدگاه جدید شما</h4>
                <form action="{{ site_url('/wp-comments-post.php') }}" method="post" class="form-grid" style="margin-top:12px">
                  <input type="hidden" name="comment_post_ID" value="{{ $product['wc_id'] }}">
                  @if(!is_user_logged_in())
                    <div class="field"><label for="rev-author">نام شما *</label><input id="rev-author" name="author" required></div>
                    <div class="field"><label for="rev-email">ایمیل *</label><input id="rev-email" name="email" type="email" required dir="ltr"></div>
                  @endif
                  <div class="field">
                    <label for="rev-rating">امتیاز شما به این محصول</label>
                    <select id="rev-rating" name="rating">
                      <option value="5">۵ ستاره — عالی</option>
                      <option value="4">۴ ستاره — خوب</option>
                      <option value="3">۳ ستاره — متوسط</option>
                      <option value="2">۲ ستاره — ضعیف</option>
                      <option value="1">۱ ستاره — بسیار ضعیف</option>
                    </select>
                  </div>
                  <div class="field full">
                    <label for="rev-comment">متن دیدگاه *</label>
                    <textarea id="rev-comment" name="comment" rows="4" required placeholder="تجربه خرید یا اجرای این محصول را بنویسید…"></textarea>
                  </div>
                  <div><button type="submit" class="btn btn-primary">ثبت دیدگاه</button></div>
                </form>
              </div>
            @endif
          </div>
        @endif

        @if(!empty($productCfg['tab_shipping']))
          <div class="tab-panel" id="tab-shipping">
            <h3>ارسال و مرجوعی</h3>
            <p>{{ $productCfg['shipping_tab_text'] ?? '' }}</p>
            @if(!empty($shippingBullets))
              <ul>
                @foreach($shippingBullets as $bullet)
                  <li>{{ $bullet }}</li>
                @endforeach
              </ul>
            @endif
            <div class="note-box"><svg><use href="#i-info"/></svg><p>در صورت آسیب‌دیدگی ظاهری بسته، از تحویل خودداری کنید و با پشتیبانی <b>{{ $phoneDisplay }}</b> تماس بگیرید.</p></div>
          </div>
        @endif
      </section>

      @if(!empty($productCfg['related_enabled']) && !empty($relatedProducts))
        <section class="product-panel related-rail">
          <div class="panel-heading">
            <h2>محصولات مرتبط</h2>
            <a href="{{ $product['cat_url'] }}">مشاهده همه</a>
          </div>
          <div class="carousel-wrap">
            <div class="product-track" id="related-products">
              @foreach($relatedProducts as $rel)
                @include('partials.product-card', ['product' => $rel, 'wishlistIds' => $wishlistIds])
              @endforeach
            </div>
            <button class="carousel-arrow carousel-prev" type="button" data-carousel="related-products" data-dir="prev" aria-label="قبلی"><svg><use href="#i-chevron-right"/></svg></button>
            <button class="carousel-arrow carousel-next" type="button" data-carousel="related-products" data-dir="next" aria-label="بعدی"><svg><use href="#i-chevron-left"/></svg></button>
          </div>
        </section>
      @endif
    </div>
  </main>

  @if(!empty($productCfg['mobile_sticky_buy']))
    <div class="sticky-buy mobile-only">
      <span class="price" id="sticky-price">{{ rs_price($product['price']) }} تومان</span>
      <button class="btn btn-primary" type="button" id="sticky-add" data-single-add="{{ $product['id'] }}" {{ !$product['in_stock'] ? 'disabled' : '' }}>
        <svg><use href="#i-bag"/></svg>{{ $product['in_stock'] ? 'افزودن به سبد' : 'ناموجود' }}
      </button>
    </div>
  @endif
@endsection
