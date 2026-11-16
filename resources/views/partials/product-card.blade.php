@php
  $isListMode = !empty($listMode);
  $liked = in_array((string) $product['id'], $wishlistIds ?? [], true);
  $cardClasses = $isListMode ? 'product-card is-list' : 'product-card';
  $discountPct = (int) ($product['discount'] ?? 0);
@endphp
<article class="{{ $cardClasses }}"
  data-product-id="{{ $product['id'] }}"
  data-cat="{{ implode(' ', $product['cats'] ?? []) }}"
  data-brand="{{ $product['brand'] ?? '' }}"
  data-price="{{ $product['price'] }}"
  data-rating="{{ $product['rating'] ?? 0 }}"
  data-sale="{{ $discountPct > 0 ? 1 : 0 }}"
  data-stock="{{ $product['stock'] ?? 0 }}"
  data-date="{{ $product['date'] ?? '' }}"
  data-sold="{{ $product['sold'] ?? 0 }}"
  data-title="{{ $product['title'] }}"
  data-image="{{ $product['image'] }}"
  data-url="{{ $product['url'] }}">
  <div class="product-media">
    <a href="{{ $product['url'] }}" aria-label="مشاهده {{ $product['title'] }}">
      <img src="{{ $product['image'] }}" alt="{{ $product['title'] }}" loading="lazy" decoding="async">
    </a>
    @if($discountPct > 0)
      <span class="discount-badge" title="فروش ویژه"><span class="db-pct">{{ rs_fa($discountPct) }}٪</span><span class="db-label">فروش ویژه</span></span>
    @elseif(!empty($product['is_new']))
      <span class="product-flag is-new">جدید</span>
    @endif
    @if(!empty($product['is_low']))
      <span class="product-flag is-low">آخرین موجودی</span>
    @endif
    <button class="wishlist-button {{ $liked ? 'active' : '' }}" type="button" data-wish="{{ $product['id'] }}" aria-label="افزودن به علاقه‌مندی" aria-pressed="{{ $liked ? 'true' : 'false' }}">
      <svg><use href="#i-heart"/></svg>
    </button>
    @if(($product['type'] ?? 'simple') === 'variable')
      <a class="add-to-cart" href="{{ $product['url'] }}"><svg><use href="#i-bag"/></svg>انتخاب گزینه</a>
    @else
      <button class="add-to-cart" type="button" data-add="{{ $product['id'] }}"><svg><use href="#i-bag"/></svg>خرید محصول</button>
    @endif
  </div>
  <div class="product-info">
    <h3 class="product-title"><a href="{{ $product['url'] }}">{{ rs_fa($product['title']) }}</a></h3>
    <div class="product-category">{{ $product['cat'] ?? '' }}</div>
    <div class="product-price">
      <span class="old-price">{{ !empty($product['old']) && $product['old'] > $product['price'] ? (rs_price($product['old']) . ' تومان') : '' }}</span>
      <span class="current-price">{{ rs_price($product['price']) }} <small>تومان</small></span>
    </div>
    @if($isListMode)
      <div class="product-excerpt">{{ $product['short'] ?? '' }}</div>
      <div class="product-list-actions">
        @if(($product['type'] ?? 'simple') === 'variable')
          <a class="btn-ghost" href="{{ $product['url'] }}">انتخاب گزینه</a>
        @else
          <button type="button" class="btn-ghost" data-add="{{ $product['id'] }}">افزودن به سبد</button>
        @endif
        <button type="button" class="btn-ghost {{ $liked ? 'active' : '' }}" data-wish="{{ $product['id'] }}">علاقه‌مندی</button>
      </div>
    @endif
  </div>
</article>
