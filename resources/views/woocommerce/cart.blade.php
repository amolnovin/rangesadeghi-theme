@extends('layouts.app')

@section('content')
  @php
    $hasItems = !empty($cartPayload['items']);
    $freeRemaining = (int) ($cartPayload['free_remaining'] ?? 0);
    $freePct = (int) ($cartPayload['free_percent'] ?? 0);
  @endphp

  <main class="page-main" id="main-content">
    <div class="page-container">
      <nav class="breadcrumb" aria-label="مسیر صفحه">
        <a href="{{ home_url('/') }}">خانه</a><svg><use href="#i-chevron-left"/></svg>
        <a href="{{ rs_page_url('shop') }}">فروشگاه</a><svg><use href="#i-chevron-left"/></svg>
        <span class="current">سبد خرید</span>
      </nav>

      <ol class="co-steps" id="co-steps" style="--p:0.00" aria-label="مراحل خرید">
        <li class="is-active" data-step-key="cart" aria-current="step"><span class="co-step-btn"><i><svg><use href="#i-bag"/></svg><em>۱</em></i><span><b>سبد خرید</b><small>بررسی کالاها</small></span></span></li>
        <li data-step-key="address"><span class="co-step-btn"><i><svg><use href="#i-pin"/></svg><em>۲</em></i><span><b>آدرس تحویل</b><small>گیرنده و نشانی</small></span></span></li>
        <li data-step-key="shipping"><span class="co-step-btn"><i><svg><use href="#i-truck"/></svg><em>۳</em></i><span><b>شیوه ارسال</b><small>روش و زمان تحویل</small></span></span></li>
        <li data-step-key="payment"><span class="co-step-btn"><i><svg><use href="#i-card"/></svg><em>۴</em></i><span><b>پرداخت</b><small>روش پرداخت و تخفیف</small></span></span></li>
        <li data-step-key="done"><span class="co-step-btn"><i><svg><use href="#i-check"/></svg><em>۵</em></i><span><b>تکمیل سفارش</b><small>ثبت نهایی</small></span></span></li>
      </ol>

      <div class="cart-layout">
        <section class="cart-panel" aria-labelledby="cart-title">
          <header class="cart-panel-head">
            <h1 id="cart-title">سبد خرید <span id="cart-page-count">{{ $hasItems ? ('(' . $cartPayload['count_fa'] . ' کالا)') : '' }}</span></h1>
            @if($hasItems)
              <button class="cart-clear" type="button" id="cart-page-clear"><svg><use href="#i-trash"/></svg>حذف همه کالاها</button>
            @endif
          </header>

          <div class="free-ship" id="free-ship">
            <div class="free-ship-text">
              <svg><use href="#i-truck"/></svg>
              <span id="free-ship-text">
                @if(!$hasItems)
                  با افزودن کالا، هزینه ارسال محاسبه می‌شود.
                @elseif($freeRemaining > 0)
                  {{ $cartPayload['free_remaining_fa'] }} تومان تا سقف پیشنهادی ارسال رایگان باقی مانده است؛ شرایط نهایی در تسویه حساب مشخص می‌شود.
                @else
                  به سقف پیشنهادی رسیده‌اید؛ شرایط ارسال در تسویه حساب مشخص می‌شود.
                @endif
              </span>
            </div>
            <div class="free-ship-bar"><span id="free-ship-progress" style="width:{{ $hasItems ? $freePct : 0 }}%"></span></div>
          </div>

          <div id="cart-page-items" class="cart-lines">
            @forelse($cartPayload['items'] as $line)
              <article class="cart-line" data-line="{{ $line['key'] }}" data-product-id="{{ $line['parent_id'] }}">
                <a class="cart-line-img" href="{{ $line['url'] }}"><img src="{{ $line['image'] }}" alt="{{ $line['title'] }}" loading="lazy"></a>
                <div class="cart-line-body">
                  <a class="cart-line-title" href="{{ $line['url'] }}">{{ $line['title'] }}</a>
                  <div class="cart-line-meta">
                    <span>{{ $line['cat'] }}</span>
                    <span aria-hidden="true">•</span>
                    <span>کد {{ $line['sku_fa'] }}</span>
                  </div>
                  <div class="cart-line-flags">
                    @if($line['in_stock'])
                      <span class="mini-flag">موجود در انبار</span>
                    @else
                      <span class="mini-flag is-muted">ناموجود</span>
                    @endif
                    @if(($line['discount'] ?? 0) > 0)
                      <span class="mini-flag is-sale">{{ $line['discount_fa'] }}٪ تخفیف</span>
                    @endif
                    <span class="mini-flag is-muted">ارسال از انبار آمل</span>
                  </div>
                  <div class="cart-line-actions">
                    <div class="qty-stepper qty-sm">
                      <button type="button" data-cart-action="plus" data-id="{{ $line['key'] }}" aria-label="افزایش تعداد"><svg><use href="#i-plus"/></svg></button>
                      <input value="{{ $line['quantity_fa'] }}" readonly aria-label="تعداد">
                      <button type="button" data-cart-action="minus" data-id="{{ $line['key'] }}" aria-label="کاهش تعداد"><svg><use href="#i-minus"/></svg></button>
                    </div>
                    <button class="cart-line-btn is-danger" type="button" data-cart-action="remove" data-id="{{ $line['key'] }}"><svg><use href="#i-trash"/></svg>حذف</button>
                    <button class="cart-line-btn" type="button" data-wish="{{ $line['parent_id'] }}"><svg><use href="#i-heart"/></svg>ذخیره در علاقه‌مندی</button>
                  </div>
                </div>
                <div class="cart-line-price">
                  <span class="old">{{ $line['line_old_fa'] }}</span>
                  <b>{{ $line['line_total_fa'] }}</b>
                  <small>تومان</small>
                </div>
              </article>
            @empty
              <div class="cart-empty-lg">
                <svg><use href="#i-bag"/></svg>
                <h3>سبد خرید شما خالی است</h3>
                <p>هنوز محصولی به سبد خرید اضافه نکرده‌اید. از فروشگاه رنگ، پتینه و ابزار مورد نیازتان را انتخاب کنید.</p>
                <a class="btn btn-primary" href="{{ rs_page_url('shop') }}"><svg><use href="#i-grid"/></svg>مشاهده محصولات</a>
              </div>
            @endforelse
          </div>

          <div class="cart-panel-foot">
            <a class="btn btn-outline btn-sm" href="{{ rs_page_url('shop') }}"><svg><use href="#i-chevron-right"/></svg>ادامه خرید</a>
            <span class="cart-panel-note"><svg><use href="#i-info"/></svg>مالیات و هزینه ارسال در تسویه حساب نهایی می‌شود.</span>
          </div>
        </section>

        <aside class="cart-side" aria-label="خلاصه سفارش">
          <div class="summary-card">
            <h2>خلاصه سفارش</h2>
            <div class="summary-row"><span>مبلغ کالاها (<b id="summary-count">{{ $cartPayload['count_fa'] ?? '۰' }}</b> کالا)</span><b id="summary-subtotal">{{ $cartPayload['original_fa'] ?? '۰ تومان' }}</b></div>
            <div class="summary-row is-discount"><span>تخفیف محصولات و کد تخفیف</span><b id="summary-discount">{{ $cartPayload['discount_fa'] ?? '۰ تومان' }}</b></div>
            <div class="summary-row"><span>هزینه ارسال</span><b id="summary-shipping">{{ $cartPayload['shipping_fa'] ?? 'پس از انتخاب آدرس' }}</b></div>
            <div class="summary-total"><span>مبلغ قابل پرداخت</span><b id="summary-total">{{ $cartPayload['total_fa'] ?? '۰ تومان' }}</b></div>

            <form class="coupon" id="coupon-form" novalidate>
              <input id="coupon-input" type="text" placeholder="کد تخفیف دارید؟" aria-label="کد تخفیف">
              <button type="submit">اعمال</button>
            </form>
            <p class="coupon-note" id="coupon-note">
              @if(!empty($cartPayload['coupons']))
                کد فعال: {{ implode('، ', $cartPayload['coupons']) }}
              @else
                کد تخفیف در مرحله پرداخت نیز قابل ثبت است.
              @endif
            </p>

            <a class="btn btn-primary btn-block {{ !$hasItems ? 'is-disabled' : '' }}" href="{{ rs_page_url('checkout') }}" id="cart-checkout">ادامه فرآیند خرید</a>
            <ul class="summary-notes">
              <li><svg><use href="#i-shield"/></svg><span>تضمین اصالت و سلامت کالا</span></li>
              <li><svg><use href="#i-refresh"/></svg><span>۷ روز ضمانت بازگشت کالا</span></li>
              <li><svg><use href="#i-headset"/></svg><span>پشتیبانی ۸ صبح تا ۸ شب</span></li>
            </ul>
          </div>

          <div class="summary-card help-card">
            <span class="help-icon"><svg><use href="#i-headset"/></svg></span>
            <div>
              <h3>در خرید شک دارید؟</h3>
              <p>کارشناسان رنگ صادقی برای انتخاب متریال، متراژ و ابزار کنار شما هستند.</p>
              <a class="text-link" href="{{ rs_page_url('contact') }}">درخواست مشاوره <svg><use href="#i-chevron-left"/></svg></a>
            </div>
          </div>
        </aside>
      </div>

      @if(!empty($crossSells))
        <section class="page-section">
          <div class="section-head">
            <div><h2>محصولات پیشنهادی</h2><p>بر اساس سبد خرید شما</p></div>
            <a class="section-link" href="{{ rs_page_url('shop') }}">مشاهده همه <svg><use href="#i-chevron-left"/></svg></a>
          </div>
          <div class="carousel-wrap">
            <div class="product-track" id="cart-suggested">
              @foreach($crossSells as $cs)
                @include('partials.product-card', ['product' => $cs, 'wishlistIds' => $wishlistIds])
              @endforeach
            </div>
          </div>
        </section>
      @endif
    </div>
  </main>

  <div class="cart-mobile-bar mobile-only {{ !$hasItems ? 'is-empty' : '' }}">
    <div class="cart-mobile-total">
      <span>مبلغ قابل پرداخت</span>
      <b id="cart-mobile-total">{{ $cartPayload['total_fa'] ?? '۰ تومان' }}</b>
    </div>
    <a class="btn btn-primary" href="{{ rs_page_url('checkout') }}" id="cart-mobile-checkout">ادامه فرآیند خرید</a>
  </div>
@endsection
