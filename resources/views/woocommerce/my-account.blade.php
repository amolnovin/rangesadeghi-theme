@extends('layouts.app')

@section('content')
  <main class="page-main" id="main-content">
    <div class="page-container">
      <nav class="breadcrumb" aria-label="مسیر صفحه">
        <a href="{{ home_url('/') }}">خانه</a><svg><use href="#i-chevron-left"/></svg><span class="current">حساب کاربری</span>
      </nav>

      @if(empty($accountUser))
        <section class="od-card" style="max-width:520px;margin:28px auto;padding:28px">
          <header class="od-card-head">
            <h2><span class="od-card-icon"><svg><use href="#i-user"/></svg></span>ورود به حساب کاربری</h2>
          </header>
          @if(rs_is_wc())
            {!! do_shortcode('[woocommerce_my_account]') !!}
          @else
            <form action="{{ wp_login_url(rs_page_url('account')) }}" method="post" class="form-grid">
              <div class="field full"><label for="wp-log">نام کاربری یا ایمیل</label><input id="wp-log" name="log" required></div>
              <div class="field full"><label for="wp-pwd">رمز عبور</label><input id="wp-pwd" name="pwd" type="password" required></div>
              <div><button type="submit" class="btn btn-primary">ورود به حساب</button></div>
            </form>
          @endif
        </section>
      @else
        @php
          $activeOrdersCount = count(array_filter($userOrders, static fn(array $o): bool => $o['tab_group'] === 'current'));
          $deliveredOrdersCount = count(array_filter($userOrders, static fn(array $o): bool => $o['tab_group'] === 'delivered'));
          $latestOrder = $userOrders[0] ?? null;
        @endphp

        <header class="account-hero">
          <div class="account-hero-user">
            <span class="account-avatar" aria-hidden="true">{{ $accountUser['initials'] }}</span>
            <div class="account-hero-id">
              <span class="account-badge"><svg><use href="#i-award"/></svg>مشتری رنگ صادقی</span>
              <h1>{{ $accountUser['name'] }}</h1>
              <p>
                @if(!empty($accountUser['phone_fa']))
                  <bdo dir="ltr">{{ $accountUser['phone_fa'] }}</bdo> •
                @endif
                <a href="mailto:{{ $accountUser['email'] }}">{{ $accountUser['email'] }}</a>
              </p>
            </div>
            <div class="account-hero-actions">
              <a class="btn btn-soft btn-sm" href="{{ rs_page_url('cart') }}"><svg><use href="#i-bag"/></svg>سبد خرید</a>
              <a class="btn btn-outline btn-sm" href="{{ $accountUser['logout_url'] }}"><svg><use href="#i-logout"/></svg>خروج</a>
            </div>
          </div>

          <div class="account-quick">
            <button type="button" class="quick-card" data-account-tab="orders">
              <span class="quick-icon"><svg><use href="#i-box"/></svg></span>
              <b id="quick-active-orders">{{ rs_fa($activeOrdersCount) }}</b>
              <span>سفارش جاری</span>
            </button>
            <button type="button" class="quick-card" data-account-tab="orders">
              <span class="quick-icon"><svg><use href="#i-check-circle"/></svg></span>
              <b>{{ rs_fa($deliveredOrdersCount) }}</b>
              <span>سفارش تحویل‌شده</span>
            </button>
            <button type="button" class="quick-card" data-account-tab="favorites">
              <span class="quick-icon"><svg><use href="#i-heart"/></svg></span>
              <b id="quick-wishlist">{{ rs_fa(count($wishlistProducts)) }}</b>
              <span>علاقه‌مندی</span>
            </button>
            <button type="button" class="quick-card" data-account-tab="wallet">
              <span class="quick-icon"><svg><use href="#i-wallet"/></svg></span>
              <b>{{ $accountUser['points_fa'] }}</b>
              <span>امتیاز وفاداری</span>
            </button>
          </div>
        </header>

        <div class="account-layout">
          <aside class="account-side" aria-label="منوی حساب کاربری">
            <nav class="account-nav">
              <button type="button" class="active" data-account-tab="overview"><svg><use href="#i-grid"/></svg>پیشخوان</button>
              <button type="button" data-account-tab="orders"><svg><use href="#i-box"/></svg>سفارش‌های من<b>{{ $accountUser['orders_count_fa'] }}</b></button>
              <button type="button" data-account-tab="addresses"><svg><use href="#i-pin"/></svg>آدرس‌های من</button>
              <button type="button" data-account-tab="favorites"><svg><use href="#i-heart"/></svg>علاقه‌مندی‌ها</button>
              <button type="button" data-account-tab="wallet"><svg><use href="#i-wallet"/></svg>امتیاز و کد تخفیف</button>
              <button type="button" data-account-tab="profile"><svg><use href="#i-user"/></svg>اطلاعات حساب</button>
              <button type="button" data-account-tab="support"><svg><use href="#i-headset"/></svg>پشتیبانی</button>
              <a class="account-logout" href="{{ $accountUser['logout_url'] }}"><svg><use href="#i-logout"/></svg>خروج از حساب</a>
            </nav>
            <div class="account-side-help">
              <h3>نیاز به راهنمایی دارید؟</h3>
              <p>{{ $workingHoursFa }}</p>
              <a class="btn btn-primary btn-sm btn-block" href="tel:{{ $phoneTel }}"><svg><use href="#i-phone"/></svg>{{ $phoneDisplay }}</a>
            </div>
          </aside>

          <div class="account-main">
            {{-- پیشخوان --}}
            <section class="account-panel active" id="panel-overview" aria-label="پیشخوان">
              @if($latestOrder)
                <article class="track-card">
                  <div class="track-head">
                    <div>
                      <span class="track-label">آخرین سفارش</span>
                      <h2>سفارش {{ $latestOrder['number_fa'] }}</h2>
                      <p>ثبت شده در {{ $latestOrder['date_fa'] }} • مبلغ {{ $latestOrder['total_fa'] }} تومان</p>
                    </div>
                    <a class="btn btn-outline btn-sm" href="{{ $latestOrder['detail_url'] }}">جزئیات سفارش</a>
                  </div>
                  <div class="track-steps">
                    <span class="{{ $latestOrder['step_index'] >= 1 ? 'done' : '' }}"><i><svg><use href="#i-check"/></svg></i>ثبت سفارش</span>
                    <span class="{{ $latestOrder['step_index'] >= 3 ? 'done' : ($latestOrder['step_index'] === 2 ? 'current' : '') }}"><i><svg><use href="#i-check"/></svg></i>آماده‌سازی</span>
                    <span class="{{ $latestOrder['step_index'] >= 4 ? 'done' : ($latestOrder['step_index'] === 3 ? 'current' : '') }}"><i><svg><use href="#i-truck"/></svg></i>در حال ارسال</span>
                    <span class="{{ $latestOrder['step_index'] >= 5 ? 'done' : '' }}"><i><svg><use href="#i-home"/></svg></i>تحویل به شما</span>
                  </div>
                  <div class="track-items">
                    @foreach(array_slice($latestOrder['items'], 0, 4) as $ti)
                      <img src="{{ $ti['image'] }}" alt="{{ $ti['title'] }}">
                    @endforeach
                    <span>{{ $latestOrder['items_count_fa'] }} کالا در این سفارش</span>
                  </div>
                </article>
              @endif

              <div class="account-cards">
                <article class="mini-card">
                  <h3><svg><use href="#i-pin"/></svg>آدرس پیش‌فرض</h3>
                  <p>{{ !empty($savedAddresses[0]) ? (($savedAddresses[0]['province_label'] ?? $savedAddresses[0]['province']) . '، ' . $savedAddresses[0]['city'] . '، ' . $savedAddresses[0]['address']) : 'هنوز آدرسی ثبت نکرده‌اید.' }}</p>
                  <button class="text-link" type="button" data-account-tab="addresses">مدیریت آدرس‌ها <svg><use href="#i-chevron-left"/></svg></button>
                </article>
                <article class="mini-card">
                  <h3><svg><use href="#i-gift"/></svg>باشگاه مشتریان</h3>
                  <p>مجموع خریدهای شما: {{ $accountUser['total_spent_fa'] }} تومان • امتیاز فعلی: {{ $accountUser['points_fa'] }}</p>
                  <a class="text-link" href="{{ add_query_arg('sale', '1', rs_page_url('shop')) }}">مشاهده پیشنهادهای ویژه <svg><use href="#i-chevron-left"/></svg></a>
                </article>
              </div>

              <div class="panel-head">
                <h2>علاقه‌مندی‌های من</h2>
                <button class="text-link" type="button" data-account-tab="favorites">مشاهده همه <svg><use href="#i-chevron-left"/></svg></button>
              </div>
              <div class="favorites-strip" id="overview-favorites">
                @forelse(array_slice($wishlistProducts, 0, 4) as $wp)
                  @include('partials.product-card', ['product' => $wp, 'wishlistIds' => $wishlistIds])
                @empty
                  <p style="color:var(--muted)">هنوز کالایی به علاقه‌مندی‌ها اضافه نکرده‌اید.</p>
                @endforelse
              </div>
            </section>

            {{-- سفارش‌ها (با تگ اصلاح‌شده <article class="order-card">) --}}
            <section class="account-panel" id="panel-orders" aria-label="سفارش‌های من">
              <div class="panel-head"><h2>سفارش‌های من</h2><span class="panel-hint">{{ $accountUser['orders_count_fa'] }} سفارش ثبت‌شده</span></div>

              @forelse($userOrders as $ord)
                <article class="order-card {{ $ord['tab_group'] === 'delivered' ? 'is-done' : '' }}">
                  <header class="order-head">
                    <div><b>{{ $ord['number_fa'] }}</b><span>{{ $ord['date_fa'] }}</span></div>
                    <span class="status-chip {{ $ord['tab_group'] === 'delivered' ? 'is-delivered' : 'is-shipping' }}">{{ $ord['status_label'] }}</span>
                  </header>
                  <div class="order-body">
                    <div class="order-thumbs">
                      @foreach(array_slice($ord['items'], 0, 4) as $oi)
                        <img src="{{ $oi['image'] }}" alt="{{ $oi['title'] }}">
                      @endforeach
                    </div>
                    <p class="order-desc">{{ implode('، ', array_column(array_slice($ord['items'], 0, 3), 'title')) }}</p>
                    <div class="order-price"><span>مبلغ کل</span><b>{{ $ord['total_fa'] }} تومان</b></div>
                  </div>
                  <footer class="order-foot">
                    <a class="btn btn-outline btn-sm" href="{{ $ord['detail_url'] }}"><svg><use href="#i-box"/></svg>جزئیات سفارش</a>
                    <button class="btn btn-ghost btn-sm" type="button" data-reorder="{{ $ord['id'] }}"><svg><use href="#i-refresh"/></svg>خرید مجدد</button>
                    @if($ord['can_cancel'])
                      <button class="btn btn-ghost btn-sm" type="button" data-cancel-order="{{ $ord['id'] }}"><svg><use href="#i-close"/></svg>لغو سفارش</button>
                    @endif
                  </footer>
                </article>
              @empty
                <div class="empty-state">
                  <svg><use href="#i-box"/></svg>
                  <h3>هنوز سفارشی ثبت نکرده‌اید</h3>
                  <a class="btn btn-primary btn-sm" href="{{ rs_page_url('shop') }}">شروع خرید</a>
                </div>
              @endforelse
            </section>

            {{-- آدرس‌ها --}}
            <section class="account-panel" id="panel-addresses" aria-label="آدرس‌های من">
              <div class="panel-head"><h2>آدرس‌های من</h2><a class="text-link" href="{{ wc_get_endpoint_url('edit-address', '', wc_get_page_permalink('myaccount')) }}">ویرایش آدرس‌های صورتحساب و ارسال ووکامرس</a></div>
              <div class="address-grid" id="account-address-grid">
                @forelse($savedAddresses as $addr)
                  <article class="address-card {{ !empty($addr['isDefault']) ? 'is-default' : '' }}" data-address-card="{{ $addr['id'] }}">
                    <header>
                      @if(!empty($addr['isDefault']))<span class="default-chip">پیش‌فرض</span>@endif
                      <b>{{ $addr['title'] }}</b>
                    </header>
                    <p>{{ $addr['province_label'] ?? $addr['province'] }}، {{ $addr['city'] }}، {{ $addr['address'] }}</p>
                    <footer>
                      <span><bdo dir="ltr">{{ rs_fa($addr['phone']) }}</bdo></span>
                      <button type="button" data-delete-address="{{ $addr['id'] }}"><svg><use href="#i-trash"/></svg>حذف</button>
                    </footer>
                  </article>
                @empty
                  <p style="color:var(--muted)">آدرسی ثبت نشده است.</p>
                @endforelse
              </div>

              <details class="address-form" open>
                <summary><svg><use href="#i-plus"/></svg>افزودن آدرس جدید</summary>
                <form id="account-new-address-form">
                  <div class="form-grid">
                    <div class="field"><label for="a-title">عنوان آدرس <span>*</span></label><input id="a-title" name="title" required placeholder="مثلاً محل کار"></div>
                    <div class="field"><label for="a-phone">شماره تماس <span>*</span></label><input id="a-phone" name="phone" type="tel" required value="{{ $accountUser['phone'] }}"></div>
                    <div class="field"><label for="a-province">استان <span>*</span></label><input id="a-province" name="province" required value="مازندران"></div>
                    <div class="field"><label for="a-city">شهر <span>*</span></label><input id="a-city" name="city" required value="آمل"></div>
                    <div class="field"><label for="a-postal">کد پستی</label><input id="a-postal" name="postal" inputmode="numeric"></div>
                    <div class="field full"><label for="a-address">نشانی کامل <span>*</span></label><textarea id="a-address" name="address" required placeholder="خیابان، کوچه، پلاک، واحد"></textarea></div>
                  </div>
                  <button class="btn btn-primary" type="submit" style="margin-top:12px">ثبت آدرس</button>
                </form>
              </details>
            </section>

            {{-- علاقه‌مندی‌ها --}}
            <section class="account-panel" id="panel-favorites" aria-label="علاقه‌مندی‌ها">
              <div class="panel-head"><h2>علاقه‌مندی‌های من</h2><span class="panel-hint" id="favorites-count">{{ rs_fa(count($wishlistProducts)) }} کالا</span></div>
              <div id="account-favorites" class="favorites-grid">
                @forelse($wishlistProducts as $wp)
                  @include('partials.product-card', ['product' => $wp, 'wishlistIds' => $wishlistIds])
                @empty
                  <p style="color:var(--muted)">لیست علاقه‌مندی‌های شما خالی است.</p>
                @endforelse
              </div>
            </section>

            {{-- امتیاز و کد تخفیف --}}
            <section class="account-panel" id="panel-wallet" aria-label="امتیاز و کد تخفیف">
              <div class="panel-head"><h2>امتیاز وفاداری و کد تخفیف</h2></div>
              <div class="wallet-grid">
                <article class="wallet-card">
                  <span class="wallet-label">امتیاز وفاداری شما</span>
                  <b>{{ $accountUser['points_fa'] }} <small>امتیاز</small></b>
                </article>
                <article class="wallet-card is-gift">
                  <span class="wallet-label">مجموع خریدهای موفق</span>
                  <b>{{ $accountUser['total_spent_fa'] }} <small>تومان</small></b>
                </article>
              </div>
            </section>

            {{-- اطلاعات حساب --}}
            <section class="account-panel" id="panel-profile" aria-label="اطلاعات حساب">
              <div class="panel-head"><h2>اطلاعات حساب</h2><a class="text-link" href="{{ wc_get_endpoint_url('edit-account', '', wc_get_page_permalink('myaccount')) }}">تغییر رمز عبور و اطلاعات حساب</a></div>
              <form class="profile-form" id="account-profile-form">
                <div class="form-grid">
                  <div class="field"><label for="p-name">نام و نام خانوادگی <span>*</span></label><input id="p-name" name="fullname" required value="{{ $accountUser['name'] }}"></div>
                  <div class="field"><label for="p-phone">شماره موبایل <span>*</span></label><input id="p-phone" name="phone" type="tel" required value="{{ $accountUser['phone'] }}"></div>
                  <div class="field"><label for="p-email">ایمیل</label><input id="p-email" name="email" type="email" value="{{ $accountUser['email'] }}"></div>
                  <div class="field"><label for="p-code">کد ملی</label><input id="p-code" name="national_code" inputmode="numeric" value="{{ $accountUser['national_code'] }}"></div>
                  <div class="field"><label for="p-birth">تاریخ تولد</label><input id="p-birth" name="birth_date" value="{{ $accountUser['birth_date'] }}"></div>
                  <div class="field"><label for="p-job">شغل / حوزه فعالیت</label><input id="p-job" name="job" value="{{ $accountUser['job'] }}"></div>
                </div>
                <button class="btn btn-primary" type="submit" style="margin-top:14px"><svg><use href="#i-check"/></svg>ذخیره تغییرات</button>
              </form>
            </section>

            {{-- پشتیبانی --}}
            <section class="account-panel" id="panel-support" aria-label="پشتیبانی">
              <div class="panel-head"><h2>پشتیبانی</h2></div>
              <div class="support-grid">
                <a class="support-tile" href="tel:{{ $phoneTel }}"><span class="quick-icon"><svg><use href="#i-phone"/></svg></span><b>تماس تلفنی</b><span>{{ $phoneDisplay }}</span></a>
                <a class="support-tile" href="{{ $general['whatsapp'] ?? '#' }}" target="_blank" rel="noopener"><span class="quick-icon"><svg><use href="#i-whatsapp"/></svg></span><b>واتساپ</b><span>پاسخ سریع در ساعات کاری</span></a>
                <a class="support-tile" href="{{ rs_page_url('contact') }}"><span class="quick-icon"><svg><use href="#i-mail"/></svg></span><b>فرم تماس</b><span>پاسخ حداکثر تا ۲۴ ساعت</span></a>
              </div>
            </section>
          </div>
        </div>
      @endif
    </div>
  </main>
@endsection
