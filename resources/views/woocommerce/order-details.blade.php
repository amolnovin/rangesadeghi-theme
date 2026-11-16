@extends('layouts.app')

@section('content')
  <main class="page-main" id="main-content">
    <div class="page-container">
      <nav class="breadcrumb" aria-label="مسیر صفحه">
        <a href="{{ home_url('/') }}">خانه</a><svg><use href="#i-chevron-left"/></svg>
        <a href="{{ rs_page_url('account') }}#orders">سفارش‌های من</a><svg><use href="#i-chevron-left"/></svg>
        <span class="current">جزئیات و پیگیری سفارش</span>
      </nav>

      @if(empty($orderDetail))
        <section class="od-card" style="max-width:640px;margin:24px auto;padding:28px">
          <header class="od-card-head">
            <h2><span class="od-card-icon"><svg><use href="#i-box"/></svg></span>پیگیری وضعیت سفارش</h2>
          </header>
          @if(!empty($orderAccessDenied))
            <p class="co-inline-error" style="margin-bottom:14px">برای مشاهده جزئیات این سفارش، وارد حساب کاربری خود شوید یا کلید سفارش معتبر وارد کنید.</p>
          @else
            <p style="margin-bottom:16px;color:var(--muted)">شماره سفارش خود را وارد کنید یا از بخش «سفارش‌های من» در حساب کاربری روی سفارش مورد نظرتان کلیک نمایید.</p>
          @endif
          <form method="get" action="{{ rs_page_url('order-details') }}" class="form-grid">
            <div class="field full">
              <label for="track-order-id">شماره سفارش (مثلاً 1042)</label>
              <input id="track-order-id" name="order_id" inputmode="numeric" required placeholder="شماره سفارش را وارد کنید">
            </div>
            <div>
              <button type="submit" class="btn btn-primary"><svg><use href="#i-search"/></svg>بررسی وضعیت سفارش</button>
              <a href="{{ rs_page_url('account') }}" class="btn btn-outline">ورود به حساب کاربری</a>
            </div>
          </form>
        </section>
      @else
        @php $stStep = (int) ($orderDetail['step_index'] ?? 2); @endphp

        <section class="od-hero" aria-labelledby="od-title">
          <div class="od-hero-id">
            <span class="status-chip is-shipping" id="od-status"><svg><use href="#i-truck"/></svg>{{ $orderDetail['status_label'] }}</span>
            <h1 id="od-title">جزئیات سفارش <b id="od-code">{{ $orderDetail['number_fa'] }}</b></h1>
            <p>
              ثبت‌شده در <span id="od-date">{{ $orderDetail['date_fa'] }}</span>
              <span class="od-dot" aria-hidden="true"></span>
              <span id="od-count">{{ $orderDetail['items_count_fa'] }} عدد کالا</span>
              <span class="od-dot" aria-hidden="true"></span>
              مبلغ کل <b id="od-total">{{ $orderDetail['total_fa'] }} تومان</b>
            </p>
          </div>
          <div class="od-hero-actions">
            <button class="btn btn-outline btn-sm" type="button" data-copy="{{ $orderDetail['number_fa'] }}"><svg><use href="#i-link"/></svg>کپی کد سفارش</button>
            <button class="btn btn-outline btn-sm" type="button" data-print><svg><use href="#i-download"/></svg>دریافت فاکتور</button>
            <a class="btn btn-primary btn-sm" href="tel:{{ $phoneTel }}"><svg><use href="#i-headset"/></svg>پشتیبانی سفارش</a>
          </div>
        </section>

        <section class="od-track" id="od-track" aria-labelledby="od-track-title">
          <header class="od-track-head">
            <div>
              <h2 id="od-track-title">وضعیت مرسوله</h2>
              <p id="od-track-note">مرسوله از طریق {{ $orderDetail['carrier_name'] }} پردازش و ارسال می‌شود.</p>
            </div>
            @if(!empty($orderDetail['tracking_code']))
              <div class="od-track-code">
                <span>کد رهگیری مرسوله</span>
                <b dir="ltr" id="od-tracking">{{ $orderDetail['tracking_code'] }}</b>
                <button type="button" data-copy="{{ $orderDetail['tracking_code'] }}" aria-label="کپی کد رهگیری"><svg><use href="#i-link"/></svg></button>
              </div>
            @endif
          </header>

          <ol class="od-steps" id="od-steps">
            <li class="{{ $stStep >= 1 ? 'is-done' : '' }}"><i><svg><use href="#i-check"/></svg></i><span><b>ثبت سفارش</b><small>{{ $orderDetail['date_fa'] }}</small></span></li>
            <li class="{{ $stStep >= 2 ? 'is-done' : ($stStep === 1 ? 'is-active' : '') }}"><i><svg><use href="#i-card"/></svg></i><span><b>تأیید پرداخت</b><small>{{ $orderDetail['payment_method'] }}</small></span></li>
            <li class="{{ $stStep >= 3 ? 'is-done' : ($stStep === 2 ? 'is-active' : '') }}"><i><svg><use href="#i-box"/></svg></i><span><b>آماده‌سازی و بسته‌بندی</b><small>انبار مرکزی آمل</small></span></li>
            <li class="{{ $stStep >= 4 ? 'is-done' : ($stStep === 3 ? 'is-active' : '') }}"><i><svg><use href="#i-truck"/></svg></i><span><b>در حال ارسال</b><small>{{ $orderDetail['carrier_name'] }}</small></span></li>
            <li class="{{ $stStep >= 5 ? 'is-done' : '' }}"><i><svg><use href="#i-home"/></svg></i><span><b>تحویل به شما</b><small>{{ $orderDetail['delivery_day'] ?: 'طبق بازه انتخابی' }}</small></span></li>
          </ol>

          <div class="od-track-foot">
            <span><svg><use href="#i-clock"/></svg>بازه تحویل: {{ $orderDetail['delivery_day'] }} ساعت {{ $orderDetail['delivery_slot'] }}</span>
            <span><svg><use href="#i-pin"/></svg>{{ $orderDetail['address_full'] }}</span>
          </div>
        </section>

        <nav class="od-subnav" aria-label="بخش‌های سفارش">
          <a class="is-active" href="#od-delivery">جزئیات سفارش</a>
          <a href="#od-items">اقلام و فاکتور</a>
        </nav>

        <div class="od-layout">
          <div class="od-main">
            <section class="od-card" id="od-delivery" aria-labelledby="od-delivery-title">
              <header class="od-card-head">
                <h2 id="od-delivery-title"><span class="od-card-icon"><svg><use href="#i-truck"/></svg></span>اطلاعات ارسال و تحویل</h2>
              </header>
              <dl class="od-info">
                <div><dt>گیرنده</dt><dd>{{ $orderDetail['receiver_name'] }}</dd></div>
                <div><dt>شماره تماس</dt><dd><bdo dir="ltr">{{ $orderDetail['receiver_phone_fa'] }}</bdo></dd></div>
                <div><dt>شیوه ارسال</dt><dd>{{ $orderDetail['carrier_name'] }}</dd></div>
                <div><dt>بازه زمانی تحویل</dt><dd>{{ $orderDetail['delivery_day'] }} — ساعت {{ $orderDetail['delivery_slot'] }}</dd></div>
                <div class="wide"><dt>نشانی تحویل</dt><dd>{{ $orderDetail['address_full'] }}</dd></div>
                <div><dt>کد پستی</dt><dd><bdo dir="ltr">{{ $orderDetail['postcode_fa'] ?: '—' }}</bdo></dd></div>
                <div><dt>هزینه ارسال</dt><dd>{{ $orderDetail['shipping_fa'] }}</dd></div>
              </dl>
            </section>

            <section class="od-card" aria-labelledby="od-payment-title">
              <header class="od-card-head">
                <h2 id="od-payment-title"><span class="od-card-icon"><svg><use href="#i-card"/></svg></span>اطلاعات پرداخت</h2>
                <span class="od-badge is-paid"><svg><use href="#i-check-circle"/></svg>{{ $orderDetail['status_label'] }}</span>
              </header>
              <dl class="od-info">
                <div><dt>روش پرداخت</dt><dd>{{ $orderDetail['payment_method'] }}</dd></div>
                <div><dt>مبلغ کل</dt><dd><b>{{ $orderDetail['total_fa'] }} تومان</b></dd></div>
                <div><dt>شماره پیگیری</dt><dd><bdo dir="ltr">{{ rs_fa($orderDetail['transaction_id']) }}</bdo></dd></div>
                <div><dt>فاکتور رسمی</dt><dd><button class="text-link" type="button" data-print><svg><use href="#i-download"/></svg>چاپ / دریافت فاکتور</button></dd></div>
              </dl>
            </section>

            <section class="od-card" id="od-items" aria-labelledby="od-items-title">
              <header class="od-card-head">
                <h2 id="od-items-title"><span class="od-card-icon"><svg><use href="#i-bag"/></svg></span>اقلام سفارش</h2>
                <span class="panel-hint">{{ $orderDetail['items_count_fa'] }} عدد کالا</span>
              </header>
              <div class="od-items" id="od-items-list">
                @foreach($orderDetail['items'] as $oi)
                  <article class="od-item">
                    <a class="od-item-img" href="{{ $oi['url'] }}"><img src="{{ $oi['image'] }}" alt="{{ $oi['title'] }}" loading="lazy"></a>
                    <div class="od-item-body">
                      <a class="od-item-title" href="{{ $oi['url'] }}">{{ $oi['title'] }}</a>
                      <div class="od-item-meta"><span>فروشگاه رنگ صادقی</span><span aria-hidden="true">•</span><span>ارسال از انبار آمل</span><span aria-hidden="true">•</span><span>کد {{ $oi['sku_fa'] }}</span></div>
                      <div class="od-item-actions">
                        <a class="cart-line-btn" href="{{ $oi['url'] }}"><svg><use href="#i-eye"/></svg>مشاهده محصول</a>
                      </div>
                    </div>
                    <div class="od-item-price">
                      <b>{{ $oi['total_fa'] }}</b>
                      <small>تومان × {{ $oi['qty_fa'] }} عدد</small>
                    </div>
                  </article>
                @endforeach
              </div>

              <div class="od-invoice">
                <div class="od-invoice-head">
                  <h3>فاکتور خرید</h3>
                  <span class="panel-hint">شماره فاکتور <bdo dir="ltr">{{ $orderDetail['number_fa'] }}</bdo></span>
                  <button class="btn btn-outline btn-sm" type="button" data-print><svg><use href="#i-download"/></svg>چاپ / دانلود فاکتور</button>
                </div>
                <div class="od-table-wrap">
                  <table class="od-table">
                    <thead>
                      <tr><th scope="col">#</th><th scope="col">کالا</th><th scope="col">تعداد</th><th scope="col">قیمت واحد</th><th scope="col">جمع</th></tr>
                    </thead>
                    <tbody>
                      @foreach($orderDetail['items'] as $idx => $oi)
                        <tr>
                          <td>{{ rs_fa($idx + 1) }}</td>
                          <td>{{ $oi['title'] }}</td>
                          <td>{{ $oi['qty_fa'] }}</td>
                          <td>{{ $oi['unit_fa'] }}</td>
                          <td>{{ $oi['total_fa'] }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
                <div class="od-totals">
                  <div><span>جمع کالاها</span><b>{{ $orderDetail['subtotal_fa'] }} تومان</b></div>
                  @if($orderDetail['discount_fa'] !== '۰')
                    <div class="is-discount"><span>تخفیف</span><b>{{ $orderDetail['discount_fa'] }} − تومان</b></div>
                  @endif
                  <div><span>هزینه ارسال</span><b>{{ $orderDetail['shipping_fa'] }}</b></div>
                  <div class="od-total-row"><span>مبلغ نهایی</span><b>{{ $orderDetail['total_fa'] }} تومان</b></div>
                </div>
              </div>
            </section>
          </div>

          <aside class="od-side">
            <section class="od-card od-summary" aria-labelledby="od-summary-title">
              <h2 id="od-summary-title">خلاصه پرداخت</h2>
              <div class="od-sum-row"><span>جمع کالاها ({{ $orderDetail['items_count_fa'] }} عدد)</span><b>{{ $orderDetail['subtotal_fa'] }} تومان</b></div>
              <div class="od-sum-row"><span>هزینه ارسال</span><b>{{ $orderDetail['shipping_fa'] }}</b></div>
              <div class="od-total-row"><span>مبلغ کل</span><b>{{ $orderDetail['total_fa'] }} تومان</b></div>
              <div class="od-actions">
                @if($orderDetail['can_cancel'])
                  <button class="btn btn-outline btn-sm" type="button" data-cancel-order="{{ $orderDetail['id'] }}"><svg><use href="#i-close"/></svg>لغو سفارش</button>
                @endif
                <button class="btn btn-outline btn-sm" type="button" data-reorder="{{ $orderDetail['id'] }}"><svg><use href="#i-refresh"/></svg>خرید مجدد</button>
                <button class="btn btn-outline btn-sm" type="button" data-print><svg><use href="#i-download"/></svg>چاپ فاکتور</button>
              </div>
            </section>

            <section class="od-card od-help">
              <span class="help-icon"><svg><use href="#i-headset"/></svg></span>
              <h3>پشتیبانی این سفارش</h3>
              <p>{{ $workingHoursFa }} — کارشناسان فروشگاه رنگ صادقی پاسخگوی شما هستند.</p>
              <a class="btn btn-primary btn-block" href="tel:{{ $phoneTel }}">{{ $phoneDisplay }}</a>
              <a class="text-link" href="{{ rs_page_url('contact') }}">ارسال پیام به پشتیبانی <svg><use href="#i-chevron-left"/></svg></a>
            </section>
          </aside>
        </div>
      @endif
    </div>
  </main>
@endsection
