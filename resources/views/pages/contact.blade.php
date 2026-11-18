@extends('layouts.app')

@section('content')
  <main class="page-main" id="main-content">
    <div class="page-container">
      <nav class="breadcrumb" aria-label="مسیر صفحه">
        <a href="{{ home_url('/') }}">خانه</a><svg><use href="#i-chevron-left"/></svg><span class="current">تماس با ما</span>
      </nav>

      <header class="page-head">
        <h1>تماس با ما</h1>
        <p>برای مشاوره خرید، پیگیری سفارش یا هماهنگی بازدید حضوری از فروشگاه، از راه‌های زیر با ما در ارتباط باشید. کارشناسان رنگ صادقی هر روز در ساعات کاری پاسخگوی شما هستند.</p>
      </header>

      <div class="contact-cards">
        <article class="contact-card">
          <span class="ic"><svg><use href="#i-pin"/></svg></span>
          <h3>آدرس فروشگاه</h3>
          <p>{{ $addressFa }}</p>
          <small>نبش دریا ۳۰/۱ — طبقه همکف</small>
        </article>
        <article class="contact-card">
          <span class="ic"><svg><use href="#i-clock"/></svg></span>
          <h3>ساعت کاری</h3>
          <p>{{ $workingHoursFa }}</p>
          <small>جمعه‌ها تعطیل</small>
        </article>
        <article class="contact-card">
          <span class="ic"><svg><use href="#i-phone"/></svg></span>
          <h3>شماره‌های تماس</h3>
          <a href="tel:{{ $phoneTel }}">پشتیبانی: {{ $phoneDisplay }}</a>
          <small>پاسخگویی تلفنی و واتساپ</small>
        </article>
        <article class="contact-card">
          <span class="ic"><svg><use href="#i-mail"/></svg></span>
          <h3>ایمیل</h3>
          <a href="mailto:{{ $general['email'] ?? 'rangsadeghi@gmail.com' }}">{{ $general['email'] ?? 'rangsadeghi@gmail.com' }}</a>
          <small>پاسخ در حداکثر ۲۴ ساعت کاری</small>
        </article>
      </div>

      <div class="contact-layout">
        <section class="contact-form-card">
          <h2>پیام خود را بنویسید</h2>
          <p>فرم زیر را تکمیل کنید؛ کارشناسان ما در اولین فرصت با شما تماس می‌گیرند. برای پاسخ سریع‌تر می‌توانید شماره واتساپ هم ثبت کنید.</p>
          <form id="contact-form" novalidate>
            <div class="form-grid">
              <div class="field">
                <label for="fullname">نام و نام خانوادگی <span>*</span></label>
                <input id="fullname" name="fullname" required placeholder="مثلاً علی رضایی">
                <span class="error"></span>
              </div>
              <div class="field">
                <label for="mobile">شماره همراه <span>*</span></label>
                <input id="mobile" name="mobile" type="tel" required inputmode="numeric" placeholder="۰۹۱۲۳۴۵۶۷۸۹">
                <span class="error"></span>
              </div>
              <div class="field">
                <label for="subject">موضوع</label>
                <select id="subject" name="subject">
                  <option value="consult">مشاوره خرید محصول</option>
                  <option value="order">پیگیری سفارش</option>
                  <option value="price">استعلام قیمت و خرید عمده</option>
                  <option value="other">سایر موارد</option>
                </select>
                <span class="error"></span>
              </div>
              <div class="field">
                <label for="email">ایمیل</label>
                <input id="email" name="email" type="email" placeholder="you@example.com">
                <span class="error"></span>
              </div>
              <div class="field full">
                <label for="message">پیام شما <span>*</span></label>
                <textarea id="message" name="message" required placeholder="توضیح دهید چه محصولی نیاز دارید و متراژ یا کاربرد آن چیست…"></textarea>
                <span class="error"></span>
              </div>
            </div>
            <label class="checkline" style="margin:14px 0">
              <input type="checkbox" name="subscribe" value="1"> می‌خواهم از تخفیف‌ها و محصولات جدید رنگ صادقی باخبر شوم.
            </label>
              @if(class_exists('WP_MC_Captcha') && class_exists('WP_MC_Core') && \WP_MC_Core::get('enabled', 1)){!! \WP_MC_Captcha::output() !!}@endif
            <button class="btn btn-primary" type="submit"><svg><use href="#i-send"/></svg>ارسال پیام</button>
            <p class="form-note">اطلاعات شما فقط برای پاسخ به همین درخواست استفاده می‌شود و در اختیار شخص ثالث قرار نمی‌گیرد.</p>
          </form>
        </section>

        <aside class="contact-side">
          <div class="map-card">
            @if(is_numeric($general['map_latitude'] ?? '') && is_numeric($general['map_longitude'] ?? '') && abs((float) $general['map_latitude']) <= 90 && abs((float) $general['map_longitude']) <= 180)
              <div class="map-canvas rs-real-map"><iframe title="نقشه موقعیت فروشگاه" loading="lazy" referrerpolicy="no-referrer" src="{{ 'https://maps.google.com/maps?q=' . rawurlencode($general['map_latitude'] . ',' . $general['map_longitude']) . '&z=17&output=embed' }}"></iframe></div>
            @else
              <p class="rs-map-unavailable">برای مشاهده موقعیت دقیق، از پیوند مسیریابی استفاده کنید.</p>
            @endif
            <div class="map-foot">
              <span>{{ $addressShortFa }}</span>
              <a class="text-link" href="{{ $general['map_url'] ?? '#' }}" target="_blank" rel="noopener">مسیریابی <svg><use href="#i-chevron-left"/></svg></a>
            </div>
          </div>

          <div class="hours-card">
            <h3><svg><use href="#i-clock"/></svg>ساعت پاسخگویی</h3>
            <div class="hours-row"><span>شنبه تا چهارشنبه</span><b>۶:۳۰ — ۲۰:۳۰</b></div>
            <div class="hours-row"><span>پنج‌شنبه</span><b>۶:۳۰ — ۲۰:۳۰</b></div>
            <div class="hours-row"><span>جمعه</span><b>تعطیل</b></div>
            <div class="hours-row"><span>پاسخگویی واتساپ</span><b>۲۴ ساعته</b></div>
          </div>

          <div class="hours-card">
            <h3><svg><use href="#i-share"/></svg>ما را دنبال کنید</h3>
            <div class="contact-socials">
              @if(!empty($general['instagram']))<a href="{{ $general['instagram'] }}" target="_blank" rel="noopener" aria-label="اینستاگرام"><svg><use href="#i-instagram"/></svg></a>@endif
              @if(!empty($general['whatsapp']))<a href="{{ $general['whatsapp'] }}" target="_blank" rel="noopener" aria-label="واتساپ"><svg><use href="#i-whatsapp"/></svg></a>@endif
              @if(!empty($general['telegram']))<a href="{{ $general['telegram'] }}" target="_blank" rel="noopener" aria-label="تلگرام"><svg><use href="#i-send"/></svg></a>@endif
              @if(!empty($general['email']))<a href="mailto:{{ $general['email'] }}" aria-label="ایمیل"><svg><use href="#i-mail"/></svg></a>@endif
            </div>
          </div>
        </aside>
      </div>

      <section class="page-section">
        <div class="section-head">
          <div><h2>پرسش‌های پرتکرار</h2><p>پاسخ سوال‌های رایج درباره خرید و ارسال</p></div>
        </div>
        <div class="faq-list">
          <details class="faq-item" open><summary>آیا امکان خرید حضوری از فروشگاه وجود دارد؟<svg><use href="#i-chevron-down"/></svg></summary><p>بله؛ فروشگاه رنگ صادقی در آمل، خیابان طالب آملی، نبش دریا ۳۰/۱ از شنبه تا پنج‌شنبه بین ساعت ۶:۳۰ تا ۲۰:۳۰ پذیرای شماست.</p></details>
          <details class="faq-item"><summary>هزینه و زمان ارسال چقدر است؟<svg><use href="#i-chevron-down"/></svg></summary><p>ارسال به سراسر کشور با پست پیشتاز و باربری انجام می‌شود؛ زمان تحویل بین ۲ تا ۵ روز کاری است. برای سفارش‌های بالای ۱۰ میلیون تومان ارسال رایگان است.</p></details>
          <details class="faq-item"><summary>برای انتخاب رنگ یا متریال مشاوره می‌دهید؟<svg><use href="#i-chevron-down"/></svg></summary><p>بله؛ کارشناسان ما بر اساس نوع سطح، متراژ و شرایط اجرا، متریال و مقدار مورد نیاز را پیشنهاد می‌دهند.</p></details>
          <details class="faq-item"><summary>امکان مرجوعی کالا وجود دارد؟<svg><use href="#i-chevron-down"/></svg></summary><p>کالاهای سالم و پلمب‌نشده تا ۷ روز پس از تحویل قابل بازگشت هستند.</p></details>
        </div>
      </section>
    </div>
  </main>
@endsection
