@extends('layouts.app')

@section('content')
  @php
    $hasItems = !empty($cartPayload['items']);
    $defaultAddr = $savedAddresses[0] ?? null;
    $wcCheckoutUrl = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : rs_page_url('checkout');
  @endphp

  <main class="page-main" id="main-content">
    <div class="page-container">
      <nav class="breadcrumb" aria-label="مسیر صفحه">
        <a href="{{ home_url('/') }}">خانه</a><svg><use href="#i-chevron-left"/></svg>
        <a href="{{ rs_page_url('cart') }}">سبد خرید</a><svg><use href="#i-chevron-left"/></svg>
        <span class="current">تسویه حساب</span>
      </nav>

      @if(!$hasItems)
        <div class="co-empty" id="co-empty">
          <svg><use href="#i-bag"/></svg>
          <h1>سبد خرید شما خالی است</h1>
          <p>برای تسویه حساب ابتدا کالاهای مورد نظرتان را به سبد خرید اضافه کنید.</p>
          <div>
            <a class="btn btn-primary" href="{{ rs_page_url('shop') }}"><svg><use href="#i-grid"/></svg>رفتن به فروشگاه</a>
            <a class="btn btn-outline" href="{{ rs_page_url('account') }}#orders">سفارش‌های من</a>
          </div>
        </div>
      @else
        <ol class="co-steps" id="co-steps" style="--p:0.25" aria-label="مراحل خرید">
          <li class="is-done" data-step-key="cart"><a class="co-step-btn" href="{{ rs_page_url('cart') }}"><i><svg><use href="#i-bag"/></svg><em>۱</em></i><span><b>سبد خرید</b><small>بررسی کالاها</small></span></a></li>
          <li class="is-active" data-step-key="address" aria-current="step"><button class="co-step-btn" type="button" data-goto="address"><i><svg><use href="#i-pin"/></svg><em>۲</em></i><span><b>آدرس تحویل</b><small>گیرنده و نشانی</small></span></button></li>
          <li data-step-key="shipping"><button class="co-step-btn" type="button" data-goto="shipping"><i><svg><use href="#i-truck"/></svg><em>۳</em></i><span><b>شیوه ارسال</b><small>روش و زمان تحویل</small></span></button></li>
          <li data-step-key="payment"><button class="co-step-btn" type="button" data-goto="payment"><i><svg><use href="#i-card"/></svg><em>۴</em></i><span><b>پرداخت</b><small>روش پرداخت و تخفیف</small></span></button></li>
          <li data-step-key="done"><button class="co-step-btn" type="button" data-goto="done" disabled><i><svg><use href="#i-check"/></svg><em>۵</em></i><span><b>تکمیل سفارش</b><small>ثبت نهایی</small></span></button></li>
        </ol>

        <form name="checkout" method="post" class="checkout woocommerce-checkout cart-layout co-layout" id="co-layout" action="{{ esc_url($wcCheckoutUrl) }}" enctype="multipart/form-data">
          @php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); @endphp
          <input type="hidden" name="billing_country" value="IR">
          <input type="hidden" name="shipping_country" value="IR">
          <input type="hidden" name="billing_first_name" id="wc_billing_first_name" value="{{ $defaultAddr['name'] ?? ($accountUser['name'] ?? '') }}">
          <input type="hidden" name="billing_last_name" value="">
          <input type="hidden" name="billing_phone" id="wc_billing_phone" value="{{ $defaultAddr['phone'] ?? ($accountUser['phone'] ?? '') }}">
          <input type="hidden" name="billing_email" value="{{ $accountUser['email'] ?? 'guest@rangsadeghy.com' }}">
          <input type="hidden" name="billing_state" id="wc_billing_state" value="{{ $defaultAddr['province'] ?? 'مازندران' }}">
          <input type="hidden" name="billing_city" id="wc_billing_city" value="{{ $defaultAddr['city'] ?? 'آمل' }}">
          <input type="hidden" name="billing_postcode" id="wc_billing_postcode" value="{{ $defaultAddr['postal'] ?? '' }}">
          <input type="hidden" name="billing_address_1" id="wc_billing_address_1" value="{{ $defaultAddr['address'] ?? '' }}">
          <input type="hidden" name="rs_delivery_day" id="wc_rs_delivery_day" value="{{ $deliveryDays[0]['value'] ?? '' }}">
          <input type="hidden" name="rs_delivery_slot" id="wc_rs_delivery_slot" value="{{ $deliverySlots[0] ?? '۹ تا ۱۲' }}">

          <div class="co-main">
            {{-- ========== مرحله ۱: آدرس و گیرنده ========== --}}
            <section class="co-step is-active" id="co-step-address" data-step="address" aria-labelledby="co-address-title">
              <div class="co-card">
                <header class="co-card-head">
                  <span class="co-card-num">۱</span>
                  <div class="co-card-title">
                    <h1 id="co-address-title">آدرس تحویل سفارش</h1>
                    <p>یکی از آدرس‌های ذخیره‌شده را انتخاب کنید یا آدرس جدید ثبت کنید.</p>
                  </div>
                  @if($isLoggedIn)
                    <a class="text-link co-head-link" href="{{ rs_page_url('account') }}#addresses">مدیریت آدرس‌ها <svg><use href="#i-chevron-left"/></svg></a>
                  @endif
                </header>

                <div class="co-address-list" id="co-address-list" role="radiogroup" aria-label="انتخاب آدرس تحویل">
                  @foreach($savedAddresses as $aIdx => $addr)
                    <label class="co-address {{ $aIdx === 0 ? 'is-selected' : '' }}"
                      data-addr-name="{{ $addr['name'] }}"
                      data-addr-phone="{{ $addr['phone'] }}"
                      data-addr-province="{{ $addr['province'] }}"
                      data-addr-city="{{ $addr['city'] }}"
                      data-addr-postal="{{ $addr['postal'] }}"
                      data-addr-full="{{ $addr['address'] }}">
                      <input type="radio" name="rs_selected_address" value="{{ $addr['id'] }}" {{ $aIdx === 0 ? 'checked' : '' }}>
                      <span class="co-radio" aria-hidden="true"></span>
                      <span class="co-address-body">
                        <b>{{ $addr['title'] }} @if(!empty($addr['isDefault']))<span class="default-chip">پیش‌فرض</span>@endif</b>
                        <span class="co-address-text">{{ $addr['province'] }}، {{ $addr['city'] }}، {{ $addr['address'] }}</span>
                        <span class="co-address-meta">
                          <span><svg><use href="#i-user"/></svg>{{ $addr['name'] }}</span>
                          <span><svg><use href="#i-phone"/></svg><bdo dir="ltr">{{ rs_fa($addr['phone']) }}</bdo></span>
                          @if(!empty($addr['postal']))
                            <span><svg><use href="#i-mail"/></svg>کد پستی <bdo dir="ltr">{{ rs_fa($addr['postal']) }}</bdo></span>
                          @endif
                        </span>
                      </span>
                      @if(trim($addr['city']) === 'آمل')
                        <span class="co-address-flag"><svg><use href="#i-truck"/></svg>امکان ارسال با پیک</span>
                      @endif
                    </label>
                  @endforeach
                </div>

                <p class="co-inline-error" id="co-address-error" hidden>لطفاً یک آدرس معتبر برای ارسال انتخاب یا ثبت کنید.</p>

                <button class="co-add-address" type="button" id="co-add-address" aria-expanded="{{ empty($savedAddresses) ? 'true' : 'false' }}" aria-controls="co-address-form">
                  <span><svg><use href="#i-plus"/></svg></span>افزودن آدرس جدید
                </button>

                <div class="co-address-form" id="co-address-form" {!! !empty($savedAddresses) ? 'hidden' : '' !!}>
                  <h3><svg><use href="#i-pin"/></svg>ثبت آدرس تحویل</h3>
                  <div class="form-grid">
                    <div class="field"><label for="na-title">عنوان آدرس <span>*</span></label><input id="na-title" placeholder="مثلاً: کارگاه، منزل" value="منزل"></div>
                    <div class="field"><label for="na-name">نام و نام خانوادگی گیرنده <span>*</span></label><input id="na-name" value="{{ $accountUser['name'] ?? '' }}" placeholder="مثلاً: علی رضایی"></div>
                    <div class="field"><label for="na-phone">شماره موبایل <span>*</span></label><input id="na-phone" type="tel" inputmode="numeric" value="{{ $accountUser['phone'] ?? '' }}" placeholder="۰۹۱۲۳۴۵۶۷۸۹" dir="ltr"></div>
                    <div class="field">
                      <label for="na-province">استان <span>*</span></label>
                      <select id="na-province">
                        <option selected>مازندران</option><option>تهران</option><option>گیلان</option><option>گلستان</option><option>البرز</option><option>اصفهان</option><option>خراسان رضوی</option><option>فارس</option><option>آذربایجان شرقی</option><option>قم</option><option>سمنان</option><option>کرمان</option><option>خوزستان</option><option>یزد</option>
                      </select>
                    </div>
                    <div class="field"><label for="na-city">شهر <span>*</span></label><input id="na-city" placeholder="مثلاً: آمل" value="آمل"></div>
                    <div class="field"><label for="na-postal">کد پستی ۱۰ رقمی <span>*</span></label><input id="na-postal" inputmode="numeric" placeholder="بدون خط تیره" dir="ltr"></div>
                    <div class="field full"><label for="na-address">نشانی کامل <span>*</span></label><textarea id="na-address" rows="3" placeholder="خیابان، کوچه، پلاک، واحد"></textarea></div>
                  </div>
                  <div class="co-form-actions">
                    <button class="btn btn-primary" type="button" id="co-save-address-btn"><svg><use href="#i-check"/></svg>ثبت و انتخاب این آدرس</button>
                    <button class="btn btn-outline" type="button" id="co-cancel-address">انصراف</button>
                  </div>
                </div>
              </div>

              <div class="co-card">
                <header class="co-card-head">
                  <span class="co-card-icon"><svg><use href="#i-user"/></svg></span>
                  <div class="co-card-title"><h2>مشخصات گیرنده</h2><p>سفارش به چه کسی تحویل داده شود؟</p></div>
                </header>
                <label class="co-check">
                  <input type="checkbox" id="co-receiver-self" checked>
                  <span class="co-check-box"><svg><use href="#i-check"/></svg></span>
                  <span>گیرنده سفارش خودم هستم <small id="co-self-info">{{ !empty($defaultAddr) ? ('(' . $defaultAddr['name'] . ' — ' . rs_fa($defaultAddr['phone']) . ')') : '' }}</small></span>
                </label>
                <div class="form-grid co-receiver" id="co-receiver" hidden>
                  <div class="field"><label for="rc-name">نام و نام خانوادگی گیرنده <span>*</span></label><input id="rc-name" name="rs_receiver_name" placeholder="نام گیرنده"></div>
                  <div class="field"><label for="rc-phone">شماره موبایل گیرنده <span>*</span></label><input id="rc-phone" name="rs_receiver_phone" type="tel" inputmode="numeric" placeholder="۰۹۱۲۳۴۵۶۷۸۹" dir="ltr"></div>
                </div>
              </div>
            </section>

            {{-- ========== مرحله ۲: شیوه و زمان ارسال ========== --}}
            <section class="co-step" id="co-step-shipping" data-step="shipping" aria-labelledby="co-ship-title" hidden>
              <div class="co-card">
                <header class="co-card-head">
                  <span class="co-card-num">۲</span>
                  <div class="co-card-title">
                    <h1 id="co-ship-title">شیوه ارسال</h1>
                    <p>ارسال به: <b id="co-ship-to">{{ $defaultAddr['city'] ?? 'آمل' }}</b></p>
                  </div>
                  <button class="text-link co-head-link" type="button" data-goto="address">تغییر آدرس <svg><use href="#i-chevron-left"/></svg></button>
                </header>
                <div class="co-options" id="co-methods" role="radiogroup" aria-label="شیوه ارسال">
                  <label class="co-option is-selected">
                    <input type="radio" name="rs_ship_method" value="post" checked>
                    <span class="co-radio" aria-hidden="true"></span>
                    <span class="co-option-icon"><svg><use href="#i-truck"/></svg></span>
                    <span class="co-option-body"><b>پست پیشتاز سراسر کشور</b><small>بسته‌بندی ایمن ویژه گالن و ظروف رنگ همراه کد رهگیری</small></span>
                    <span class="co-option-side"><b>{{ $cartPayload['shipping_fa'] }}</b><small>۲ تا ۴ روز کاری</small></span>
                  </label>
                  <label class="co-option">
                    <input type="radio" name="rs_ship_method" value="courier">
                    <span class="co-radio" aria-hidden="true"></span>
                    <span class="co-option-icon"><svg><use href="#i-pin"/></svg></span>
                    <span class="co-option-body"><b>پیک اختصاصی رنگ صادقی (شهر آمل)</b><small>ارسال مستقیم از انبار در بازه زمانی انتخابی شما</small></span>
                    <span class="co-option-side"><b>پس‌کرایه / رایگان بالای سقف</b><small>همان روز</small></span>
                  </label>
                  <label class="co-option">
                    <input type="radio" name="rs_ship_method" value="pickup">
                    <span class="co-radio" aria-hidden="true"></span>
                    <span class="co-option-icon"><svg><use href="#i-store"/></svg></span>
                    <span class="co-option-body"><b>تحویل حضوری از فروشگاه آمل</b><small>خیابان طالب آملی، نبش دریای ۳۰/۱، پلاک ۴۴۴</small></span>
                    <span class="co-option-side"><b>رایگان</b><small>آماده تحویل در ۲ ساعت</small></span>
                  </label>
                </div>
              </div>

              @if(!empty($deliveryDays))
                <div class="co-card" id="co-slot-card">
                  <header class="co-card-head">
                    <span class="co-card-icon"><svg><use href="#i-calendar"/></svg></span>
                    <div class="co-card-title"><h2>زمان تحویل</h2><p id="co-slot-hint">روز و بازه زمانی دلخواه را انتخاب کنید.</p></div>
                  </header>
                  <div class="co-days" id="co-days" role="radiogroup" aria-label="روز تحویل">
                    @foreach($deliveryDays as $dIdx => $day)
                      <button type="button" role="radio" class="co-day {{ $dIdx === 0 ? 'is-selected' : '' }}" aria-checked="{{ $dIdx === 0 ? 'true' : 'false' }}" data-day-val="{{ $day['value'] }}">
                        <b>{{ $day['weekday'] }}</b><small>{{ $day['date'] }}</small>
                      </button>
                    @endforeach
                  </div>
                  <div class="co-slots" id="co-slots" role="radiogroup" aria-label="بازه زمانی تحویل">
                    @foreach($deliverySlots as $sIdx => $slot)
                      <button type="button" role="radio" class="co-slot {{ $sIdx === 0 ? 'is-selected' : '' }}" aria-checked="{{ $sIdx === 0 ? 'true' : 'false' }}" data-slot-val="{{ $slot }}">
                        <span>ساعت {{ rs_fa($slot) }}</span>
                      </button>
                    @endforeach
                  </div>
                </div>
              @endif

              <div class="co-card">
                <header class="co-card-head">
                  <span class="co-card-icon"><svg><use href="#i-box"/></svg></span>
                  <div class="co-card-title"><h2>مرسوله شما</h2><p>ارسال از انبار مرکزی رنگ صادقی — آمل</p></div>
                </header>
                <div class="co-shipment" id="co-shipment">
                  @foreach($cartPayload['items'] as $ci)
                    <div class="co-ship-item">
                      <img src="{{ $ci['image'] }}" alt="{{ $ci['title'] }}">
                      <span>×{{ $ci['quantity_fa'] }}</span>
                    </div>
                  @endforeach
                </div>

                @if($giftWrapEnabled)
                  <label class="co-check">
                    <input type="checkbox" id="co-gift" name="rs_gift_wrap" value="1">
                    <span class="co-check-box"><svg><use href="#i-check"/></svg></span>
                    <span><svg class="co-check-icon"><use href="#i-gift"/></svg>بسته‌بندی هدیه همراه کارت پیام <small>(+ {{ $giftWrapCostFa }} تومان)</small></span>
                  </label>
                @endif

                <div class="field co-note">
                  <label for="co-note">توضیحات سفارش <small>(اختیاری)</small></label>
                  <textarea id="co-note" name="order_comments" rows="3" maxlength="300" placeholder="مثلاً: قبل از ارسال تماس بگیرید، یا کد رنگ مورد نظرتان را بنویسید…"></textarea>
                </div>
              </div>
            </section>

            {{-- ========== مرحله ۳: پرداخت ========== --}}
            <section class="co-step" id="co-step-payment" data-step="payment" aria-labelledby="co-pay-title" hidden>
              <div class="co-card">
                <header class="co-card-head">
                  <span class="co-card-num">۳</span>
                  <div class="co-card-title"><h1 id="co-pay-title">روش پرداخت</h1><p>تمام پرداخت‌ها از طریق درگاه امن و رمزنگاری‌شده انجام می‌شود.</p></div>
                </header>
                <div class="co-options" id="co-payments" role="radiogroup" aria-label="روش پرداخت">
                  @foreach($paymentGateways as $gIdx => $gw)
                    <label class="co-option {{ $gIdx === 0 ? 'is-selected' : '' }}">
                      <input type="radio" name="payment_method" value="{{ $gw['id'] }}" {{ $gIdx === 0 ? 'checked' : '' }}>
                      <span class="co-radio" aria-hidden="true"></span>
                      <span class="co-option-icon"><svg><use href="#i-card"/></svg></span>
                      <span class="co-option-body"><b>{{ $gw['title'] }}</b><small>{{ $gw['desc'] }}</small></span>
                    </label>
                  @endforeach
                </div>
              </div>

              <div class="co-card">
                <header class="co-card-head">
                  <span class="co-card-icon"><svg><use href="#i-ticket"/></svg></span>
                  <div class="co-card-title"><h2>کد تخفیف</h2><p>کد تخفیف خود را وارد کنید.</p></div>
                </header>
                <div class="coupon co-coupon" id="co-coupon-form">
                  <input id="co-coupon-input" type="text" placeholder="کد تخفیف" aria-label="کد تخفیف" autocomplete="off" dir="ltr">
                  <button type="button" id="co-coupon-btn">ثبت کد</button>
                </div>
                <p class="coupon-note" id="co-coupon-note"></p>
              </div>

              <div class="co-card">
                <header class="co-card-head">
                  <span class="co-card-icon"><svg><use href="#i-check-circle"/></svg></span>
                  <div class="co-card-title"><h2>بازبینی نهایی سفارش</h2><p>پیش از پرداخت، اطلاعات زیر را بررسی کنید.</p></div>
                </header>
                <label class="co-check">
                  <input type="checkbox" id="co-invoice" name="rs_official_invoice" value="1">
                  <span class="co-check-box"><svg><use href="#i-check"/></svg></span>
                  <span>فاکتور رسمی همراه مرسوله ارسال شود</span>
                </label>
                <label class="co-check co-terms">
                  <input type="checkbox" id="co-terms" name="terms" value="1" checked>
                  <span class="co-check-box"><svg><use href="#i-check"/></svg></span>
                  <span><a href="{{ rs_page_url('contact') }}">قوانین و مقررات</a> و <a href="{{ rs_page_url('contact') }}">شرایط بازگشت کالا</a> فروشگاه رنگ صادقی را خوانده‌ام و می‌پذیرم.</span>
                </label>
                <p class="co-inline-error" id="co-terms-error" hidden>برای ثبت سفارش، پذیرش قوانین و مقررات الزامی است.</p>
              </div>
            </section>

            <div class="co-nav" id="co-nav">
              <button class="btn btn-outline" type="button" id="co-back"><svg><use href="#i-chevron-right"/></svg><span>بازگشت به سبد خرید</span></button>
              <button class="btn btn-primary" type="button" id="co-next"><span>ادامه</span><svg><use href="#i-chevron-left"/></svg></button>
            </div>
          </div>

          <aside class="cart-side co-side" id="co-side" aria-label="خلاصه سفارش">
            <div class="summary-card">
              <h2>خلاصه سفارش <small id="co-count">({{ $cartPayload['count_fa'] }} کالا)</small></h2>
              <details class="co-items" id="co-items" open>
                <summary><span>کالاهای سفارش</span><svg><use href="#i-chevron-down"/></svg></summary>
                <div class="co-items-list" id="co-items-list">
                  @foreach($cartPayload['items'] as $ci)
                    <div class="co-mini-item">
                      <img src="{{ $ci['image'] }}" alt="{{ $ci['title'] }}">
                      <div><b>{{ $ci['title'] }}</b><small>{{ $ci['quantity_fa'] }} عدد — {{ $ci['line_total_fa'] }} تومان</small></div>
                    </div>
                  @endforeach
                </div>
              </details>
              <div class="summary-row"><span>قیمت کالاها</span><b id="co-sum-original">{{ $cartPayload['original_fa'] }}</b></div>
              <div class="summary-row is-discount"><span>تخفیف کالاها و کد تخفیف</span><b id="co-sum-discount">{{ $cartPayload['discount_fa'] }}</b></div>
              <div class="summary-row"><span>هزینه ارسال</span><b id="co-sum-shipping">{{ $cartPayload['shipping_fa'] }}</b></div>
              <div class="summary-total"><span>مبلغ قابل پرداخت</span><b id="co-sum-total">{{ $cartPayload['total_fa'] }}</b></div>
              <button class="btn btn-primary btn-block" type="button" id="co-side-next">ادامه</button>
              <ul class="summary-notes">
                <li><svg><use href="#i-shield"/></svg><span>پرداخت امن با درگاه‌های عضو شبکه شتاب</span></li>
                <li><svg><use href="#i-refresh"/></svg><span>۷ روز ضمانت بازگشت کالا</span></li>
                <li><svg><use href="#i-award"/></svg><span>تضمین اصالت و سلامت کالا</span></li>
              </ul>
            </div>
            <div class="summary-card co-trust">
              <div><svg><use href="#i-shield"/></svg><span>اتصال امن<br><b>SSL</b></span></div>
              <div><svg><use href="#i-award"/></svg><span>نماد<br><b>اعتماد</b></span></div>
              <div><svg><use href="#i-headset"/></svg><span>پشتیبانی<br><b dir="ltr">{{ $phoneDisplay }}</b></span></div>
            </div>
          </aside>
        </form>
      @endif
    </div>
  </main>

  @if($hasItems)
    <div class="cart-mobile-bar co-mobile-bar mobile-only" id="co-mobile-bar">
      <div class="cart-mobile-total">
        <span>مبلغ قابل پرداخت</span>
        <b id="co-mobile-total">{{ $cartPayload['total_fa'] }}</b>
      </div>
      <button class="btn btn-primary" type="button" id="co-mobile-next">ادامه</button>
    </div>

    <div class="co-processing" id="co-processing" hidden>
      <div class="co-processing-box" role="alertdialog" aria-modal="true" aria-labelledby="co-processing-title" aria-describedby="co-processing-text">
        <span class="co-spinner" aria-hidden="true"></span>
        <h3 id="co-processing-title">در حال ثبت سفارش و اتصال به درگاه پرداخت…</h3>
        <p id="co-processing-text">لطفاً تا پایان عملیات صفحه را نبندید.</p>
        <div class="co-processing-bar"><span></span></div>
      </div>
    </div>
  @endif
@endsection
