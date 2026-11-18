<div class="rs-admin-wrap" dir="rtl">
  <header class="rs-admin-header">
    <div class="rs-admin-brand">
      <div class="rs-admin-badge">RS</div>
      <div>
        <h1>پنل تنظیمات حرفه‌ای قالب رنگ صادقی</h1>
        <p>معماری مدرن Roots Sage (بدون نیاز به Composer) • نسخه فعلی: <strong id="rs-current-ver">{{ $themeVersion }}</strong> • ووکامرس: <strong>{{ $wcVersion }}</strong></p>
      </div>
    </div>
    <div class="rs-admin-actions">
      <button type="button" class="rs-btn rs-btn-soft" id="rs-flush-cache-btn">پاکسازی کش قالب</button>
      <button type="button" class="rs-btn rs-btn-outline" id="rs-seed-demo-btn">درون‌ریزی محتوای دمو (۱ کلیک)</button>
      <button type="button" class="rs-btn rs-btn-primary" id="rs-save-options-btn">ذخیره همه تنظیمات</button>
    </div>
  </header>

  <div class="rs-admin-notice" id="rs-admin-notice" hidden></div>

  <div class="rs-admin-body">
    <aside class="rs-admin-tabs" role="tablist">
      <button type="button" class="{{ $activeTab === 'general' ? 'is-active' : '' }}" data-rs-tab="general">۱. عمومی، برند و تماس</button>
      <button type="button" class="{{ $activeTab === 'home' ? 'is-active' : '' }}" data-rs-tab="home">۲. بخش‌های صفحه اصلی</button>
      <button type="button" class="{{ $activeTab === 'shop' ? 'is-active' : '' }}" data-rs-tab="shop">۳. تنظیمات فروشگاه</button>
      <button type="button" class="{{ $activeTab === 'product' ? 'is-active' : '' }}" data-rs-tab="product">۴. تنظیمات تک‌محصول</button>
      <button type="button" class="{{ $activeTab === 'seo' ? 'is-active' : '' }}" data-rs-tab="seo">۵. سئو پیشرفته (SEO)</button>
      <button type="button" class="{{ $activeTab === 'schema' ? 'is-active' : '' }}" data-rs-tab="schema">۶. اسکیماها (JSON-LD)</button>
      <button type="button" class="{{ $activeTab === 'performance' ? 'is-active' : '' }}" data-rs-tab="performance">۷. بهینه‌سازی سرعت سایت</button>
      <button type="button" class="{{ $activeTab === 'updater' ? 'is-active' : '' }}" data-rs-tab="updater">۸. بروزرسانی قالب با پچ</button>
    </aside>

    <form id="rs-options-form" class="rs-admin-content" onsubmit="return false;">

      {{-- ==================== تب ۱: عمومی ==================== --}}
      <section class="rs-tab-panel {{ $activeTab === 'general' ? 'is-active' : '' }}" data-rs-panel="general">
        <div class="rs-card">
          <h2>هویت بصری، لوگو و رنگ‌بندی</h2>
          <div class="rs-grid-2">
            <div class="rs-field">
              <label>لوگو دسکتاپ</label>
              <div class="rs-media-row">
                <input type="text" data-opt="general.logo_desktop" value="{{ $opts['general']['logo_desktop'] ?? '' }}" dir="ltr">
                <button type="button" class="rs-btn rs-btn-soft rs-media-pick">انتخاب تصویر</button>
              </div>
            </div>
            <div class="rs-field">
              <label>لوگو موبایل</label>
              <div class="rs-media-row">
                <input type="text" data-opt="general.logo_mobile" value="{{ $opts['general']['logo_mobile'] ?? '' }}" dir="ltr">
                <button type="button" class="rs-btn rs-btn-soft rs-media-pick">انتخاب تصویر</button>
              </div>
            </div>
            <div class="rs-field">
              <label>فاوآیکون سایت</label>
              <div class="rs-media-row">
                <input type="text" data-opt="general.favicon" value="{{ $opts['general']['favicon'] ?? '' }}" dir="ltr">
                <button type="button" class="rs-btn rs-btn-soft rs-media-pick">انتخاب تصویر</button>
              </div>
            </div>
            <div class="rs-field rs-colors-inline">
              <div><label>رنگ اصلی برند</label><input type="color" data-opt="general.brand_color" value="{{ $opts['general']['brand_color'] ?? '#df3639' }}"></div>
              <div><label>رنگ تیره هاور</label><input type="color" data-opt="general.brand_color_dark" value="{{ $opts['general']['brand_color_dark'] ?? '#b92427' }}"></div>
              <div><label>رنگ طلایی مکمل</label><input type="color" data-opt="general.gold_color" value="{{ $opts['general']['gold_color'] ?? '#b08948' }}"></div>
            </div>
          </div>
          <div class="rs-toggles">
            <label class="rs-switch"><input type="checkbox" data-opt="general.utility_bar_enabled" {{ !empty($opts['general']['utility_bar_enabled']) ? 'checked' : '' }}><span>نمایش نوار خدمات بالای هدر</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="general.sticky_header_enabled" {{ !empty($opts['general']['sticky_header_enabled']) ? 'checked' : '' }}><span>هدر چسبان هوشمند (Sticky Header)</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="general.deal_nav_enabled" {{ !empty($opts['general']['deal_nav_enabled']) ? 'checked' : '' }}><span>دکمه «تخفیف‌های امروز» در نوار منو</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="general.mobile_toolbar_enabled" {{ !empty($opts['general']['mobile_toolbar_enabled']) ? 'checked' : '' }}><span>نوار ابزار پایین صفحه در موبایل</span></label>
          </div>
          <div class="rs-grid-2" style="margin-top:14px">
            <div class="rs-field"><label>پیام ارسال رایگان در بالای هدر</label><input type="text" data-opt="general.free_shipping_note" value="{{ $opts['general']['free_shipping_note'] ?? '' }}"></div>
            <div class="rs-field"><label>عبارات محبوب جستجو (با ویرگول لاتین , جدا کنید)</label><input type="text" data-opt="general.popular_searches" value="{{ $opts['general']['popular_searches'] ?? '' }}"></div>
            <div class="rs-field"><label>برچسب پیشنهاد مگامنو</label><input type="text" data-opt="general.mega_highlight_tag" value="{{ $opts['general']['mega_highlight_tag'] ?? '' }}"></div>
            <div class="rs-field"><label>عنوان پیشنهاد مگامنو</label><input type="text" data-opt="general.mega_highlight_title" value="{{ $opts['general']['mega_highlight_title'] ?? '' }}"></div>
          </div>
        </div>

        <div class="rs-card">
          <h2>اطلاعات تماس، شبکه‌های اجتماعی و فوتر</h2>
          <div class="rs-grid-2">
            <div class="rs-field"><label>شماره تماس (لاتین برای لینک tel)</label><input type="text" data-opt="general.phone" value="{{ $opts['general']['phone'] ?? '' }}" dir="ltr"></div>
            <div class="rs-field"><label>نمایش شماره تماس (فارسی)</label><input type="text" data-opt="general.phone_display" value="{{ $opts['general']['phone_display'] ?? '' }}"></div>
            <div class="rs-field"><label>ایمیل رسمی</label><input type="email" data-opt="general.email" value="{{ $opts['general']['email'] ?? '' }}" dir="ltr"></div>
            <div class="rs-field"><label>ساعات کاری</label><input type="text" data-opt="general.work_hours" value="{{ $opts['general']['work_hours'] ?? '' }}"></div>
            <div class="rs-field"><label>نشانی کامل</label><input type="text" data-opt="general.address" value="{{ $opts['general']['address'] ?? '' }}"></div>
            <div class="rs-field"><label>نقشه صفحه تماس</label><select data-opt="general.map_provider"><option value="google" {{ ($opts['general']['map_provider'] ?? 'google') === 'google' ? 'selected' : '' }}>گوگل‌مپ (مکان ارسالی فروشگاه)</option><option value="neshan" {{ ($opts['general']['map_provider'] ?? '') === 'neshan' ? 'selected' : '' }}>نشان (مکان ارسالی فروشگاه)</option><option value="coordinates" {{ ($opts['general']['map_provider'] ?? '') === 'coordinates' ? 'selected' : '' }}>مختصات سفارشی</option></select><small>برای استفاده از مختصات دلخواه، گزینه مختصات سفارشی را انتخاب کنید.</small></div>
            <div class="rs-field"><label>عرض جغرافیایی دقیق فروشگاه (latitude)</label><input type="text" data-opt="general.map_latitude" value="{{ $opts['general']['map_latitude'] ?? '' }}" dir="ltr"></div>
            <div class="rs-field"><label>طول جغرافیایی دقیق فروشگاه (longitude)</label><input type="text" data-opt="general.map_longitude" value="{{ $opts['general']['map_longitude'] ?? '' }}" dir="ltr"><small>فقط برای حالت مختصات سفارشی استفاده می‌شود.</small></div>
            <div class="rs-field"><label>تصویر رسمی اینماد</label><div class="rs-media-row"><input type="text" data-opt="general.enamad_image" value="{{ $opts['general']['enamad_image'] ?? '' }}" dir="ltr"><button type="button" class="rs-btn rs-btn-soft rs-media-pick">انتخاب تصویر</button></div><small>در صورت درج کد رسمی اینماد، همان کد اولویت دارد.</small></div>
            <div class="rs-field"><label>نشانی کوتاه (کارت صفحه اصلی)</label><input type="text" data-opt="general.address_short" value="{{ $opts['general']['address_short'] ?? '' }}"></div>
            <div class="rs-field"><label>لینک اینستاگرام</label><input type="url" data-opt="general.instagram" value="{{ $opts['general']['instagram'] ?? '' }}" dir="ltr"></div>
            <div class="rs-field"><label>لینک واتساپ</label><input type="url" data-opt="general.whatsapp" value="{{ $opts['general']['whatsapp'] ?? '' }}" dir="ltr"></div>
            <div class="rs-field"><label>لینک تلگرام</label><input type="url" data-opt="general.telegram" value="{{ $opts['general']['telegram'] ?? '' }}" dir="ltr"></div>
            <div class="rs-field"><label>لینک مسیریابی نقشه</label><input type="url" data-opt="general.map_url" value="{{ $opts['general']['map_url'] ?? '' }}" dir="ltr"></div>
            <div class="rs-field rs-full"><label>متن معرفی فروشگاه در فوتر</label><textarea rows="3" data-opt="general.footer_about">{{ $opts['general']['footer_about'] ?? '' }}</textarea></div>
            <div class="rs-field"><label>لینک مستقیم اینماد</label><input type="url" data-opt="general.enamad_url" value="{{ $opts['general']['enamad_url'] ?? '' }}" dir="ltr"></div>
            <div class="rs-field"><label>متن کپی‌رایت فوتر</label><input type="text" data-opt="general.copyright_text" value="{{ $opts['general']['copyright_text'] ?? '' }}"></div>
            <div class="rs-field rs-full"><label>کد کامل HTML اینماد (اختیاری — در صورت پر بودن جایگزین دکمه پیش‌فرض اینماد می‌شود)</label><textarea rows="2" data-opt="general.enamad_code" dir="ltr">{{ $opts['general']['enamad_code'] ?? '' }}</textarea></div>
          </div>
        </div>

        <div class="rs-card">
          <h2>پشتیبان‌گیری تنظیمات و صندوق پیام‌ها</h2>
          <p class="rs-muted">تعداد اعضای ثبت‌شده در خبرنامه: <strong>{{ rs_fa($newsletterCount) }} نفر</strong></p>
          <div class="rs-btn-row">
            <button type="button" class="rs-btn rs-btn-outline" id="rs-export-json-btn">دانلود فایل JSON تنظیمات</button>
            <label class="rs-btn rs-btn-soft" style="cursor:pointer">
              درون‌ریزی فایل JSON تنظیمات
              <input type="file" id="rs-import-json-input" accept=".json" hidden>
            </label>
            <button type="button" class="rs-btn rs-btn-danger" id="rs-reset-options-btn">بازنشانی به پیش‌فرض کارخانه</button>
          </div>

          @if(!empty($contactMessages))
            <h3 style="margin-top:18px;font-size:14px">آخرین پیام‌های دریافتی از فرم تماس با ما</h3>
            <table class="rs-table">
              <thead><tr><th>نام</th><th>موبایل</th><th>موضوع</th><th>پیام</th><th>تاریخ</th></tr></thead>
              <tbody>
                @foreach($contactMessages as $msg)
                  <tr>
                    <td>{{ $msg['name'] ?? '' }}</td>
                    <td dir="ltr">{{ $msg['mobile'] ?? '' }}</td>
                    <td>{{ $msg['subject'] ?? '' }}</td>
                    <td>{{ $msg['message'] ?? '' }}</td>
                    <td dir="ltr">{{ $msg['date'] ?? '' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif
        </div>
      </section>

      {{-- ==================== تب ۲: صفحه اصلی ==================== --}}
      <section class="rs-tab-panel {{ $activeTab === 'home' ? 'is-active' : '' }}" data-rs-panel="home">
        <div class="rs-card">
          <h2>چیدمان و نمایش بخش‌های صفحه اصلی</h2>
          <div class="rs-toggles">
            <label class="rs-switch"><input type="checkbox" data-opt="home.demo_fallback_when_empty" {{ !empty($opts['home']['demo_fallback_when_empty']) ? 'checked' : '' }}><span>نمایش هوشمند محصولات دمو در صورت خالی بودن اولیه ووکامرس</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="home.hero_enabled" {{ !empty($opts['home']['hero_enabled']) ? 'checked' : '' }}><span>۱. اسلایدر هیرو و کارت‌های کناری</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="home.categories_enabled" {{ !empty($opts['home']['categories_enabled']) ? 'checked' : '' }}><span>۲. نوار دسته‌بندی‌ها</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="home.deal_enabled" {{ !empty($opts['home']['deal_enabled']) ? 'checked' : '' }}><span>۳. پیشنهاد شگفت‌انگیز و شمارش معکوس</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="home.services_enabled" {{ !empty($opts['home']['services_enabled']) ? 'checked' : '' }}><span>۴. نوار خدمات فروشگاه</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="home.projects_enabled" {{ !empty($opts['home']['projects_enabled']) ? 'checked' : '' }}><span>۵. خرید بر اساس پروژه</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="home.bestsellers_enabled" {{ !empty($opts['home']['bestsellers_enabled']) ? 'checked' : '' }}><span>۶. پرفروش‌ترین محصولات</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="home.brands_enabled" {{ !empty($opts['home']['brands_enabled']) ? 'checked' : '' }}><span>۷. نوار برندهای معتبر</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="home.palette_enabled" {{ !empty($opts['home']['palette_enabled']) ? 'checked' : '' }}><span>۸. راهنمای پالت رنگ هفته</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="home.newest_enabled" {{ !empty($opts['home']['newest_enabled']) ? 'checked' : '' }}><span>۹. جدیدترین محصولات</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="home.blog_enabled" {{ !empty($opts['home']['blog_enabled']) ? 'checked' : '' }}><span>۱۰. مقالات مجله رنگ صادقی</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="home.closing_enabled" {{ !empty($opts['home']['closing_enabled']) ? 'checked' : '' }}><span>۱۱. کارت فروشگاه حضوری و خبرنامه</span></label>
          </div>
          <div class="rs-field" style="margin-top:14px">
            <label>ترتیب قرارگیری بخش‌ها (با ویرگول جدا کنید)</label>
            <input type="text" data-opt="home.section_order" value="{{ $opts['home']['section_order'] ?? 'hero,categories,deal,services,projects,bestsellers,brands,palette,newest,blog,closing' }}" dir="ltr">
            <small class="rs-hint">کلیدهای مجاز: hero, categories, deal, services, projects, bestsellers, brands, palette, newest, blog, closing</small>
          </div>
        </div>

        <div class="rs-card">
          <h2>تنظیمات اسلایدر هیرو (اسلایدها + بنرهای کناری)</h2>
          <div class="rs-grid-2">
            <div class="rs-field"><label>فاصله تعویض خودکار اسلاید (میلی‌ثانیه)</label><input type="number" data-opt="home.hero_autoplay_ms" value="{{ $opts['home']['hero_autoplay_ms'] ?? 5000 }}" dir="ltr"></div>
          </div>
          <div class="rs-field" style="margin-top:10px">
            <label>اسلایدهای هیرو</label>
            <textarea rows="7" data-opt-json="home.hero_slides" dir="ltr">{{ wp_json_encode($opts['home']['hero_slides'] ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</textarea>
          </div>
          <div class="rs-field" style="margin-top:10px">
            <label>بنرهای کناری اسلایدر دسکتاپ</label>
            <textarea rows="6" data-opt-json="home.hero_side_cards" dir="ltr">{{ wp_json_encode($opts['home']['hero_side_cards'] ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</textarea>
          </div>
        </div>

        <div class="rs-card">
          <h2>پیشنهاد شگفت‌انگیز، ریل‌های محصول و پالت رنگ</h2>
          <div class="rs-field"><label>شناسه دسته‌های صفحه اصلی (به ترتیب، جداشده با ویرگول)</label><input type="text" data-opt="home.category_ids" value="{{ $opts['home']['category_ids'] ?? '' }}" dir="ltr"><small>شناسه خالی یعنی نمایش خودکار؛ نام، تعداد و تصویر از دسته ووکامرس خوانده می‌شود.</small></div>
          <div class="rs-grid-2">
            <div class="rs-field"><label>عنوان پیشنهاد شگفت‌انگیز</label><input type="text" data-opt="home.deal_title" value="{{ $opts['home']['deal_title'] ?? '' }}"></div>
            <div class="rs-field"><label>زیرعنوان پیشنهاد شگفت‌انگیز</label><input type="text" data-opt="home.deal_subtitle" value="{{ $opts['home']['deal_subtitle'] ?? '' }}"></div>
            <div class="rs-field">
              <label>منبع کالاهای پیشنهاد شگفت‌انگیز</label>
              <select data-opt="home.deal_source">
                <option value="on_sale" {{ ($opts['home']['deal_source'] ?? '') === 'on_sale' ? 'selected' : '' }}>کالاهای دارای تخفیف (On Sale)</option>
                <option value="featured" {{ ($opts['home']['deal_source'] ?? '') === 'featured' ? 'selected' : '' }}>کالاهای ویژه (Featured)</option>
                <option value="category" {{ ($opts['home']['deal_source'] ?? '') === 'category' ? 'selected' : '' }}>دسته‌بندی خاص</option>
              </select>
            </div>
            <div class="rs-field"><label>تعداد کالاها در ریل شگفت‌انگیز</label><input type="number" data-opt="home.deal_count" value="{{ $opts['home']['deal_count'] ?? 8 }}" dir="ltr"></div>
            <div class="rs-field"><label>زمان پایان شمارش معکوس (خالی = پایان خودکار هر شب)</label><input type="text" data-opt="home.deal_end_datetime" value="{{ $opts['home']['deal_end_datetime'] ?? '' }}" dir="ltr" placeholder="2026-12-30T23:59:59"></div>
            <div class="rs-field"><label>تعداد کالاهای پرفروش و جدید</label><input type="number" data-opt="home.bestsellers_count" value="{{ $opts['home']['bestsellers_count'] ?? 8 }}" dir="ltr"></div>
            <div class="rs-field"><label>عنوان بخش پالت رنگ</label><input type="text" data-opt="home.palette_title" value="{{ $opts['home']['palette_title'] ?? '' }}"></div>
            <div class="rs-field"><label>تعداد مقالات مجله در صفحه اول</label><input type="number" data-opt="home.blog_count" value="{{ $opts['home']['blog_count'] ?? 3 }}" dir="ltr"></div>
            <div class="rs-field rs-full"><label>توضیحات بخش پالت رنگ</label><textarea rows="2" data-opt="home.palette_desc">{{ $opts['home']['palette_desc'] ?? '' }}</textarea></div>
            <div class="rs-field rs-full"><label>کارت‌های پالت رنگ هفته (JSON)</label><textarea rows="5" data-opt-json="home.palette_swatches" dir="ltr">{{ wp_json_encode($opts['home']['palette_swatches'] ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</textarea></div>
            <div class="rs-field rs-full"><label>کارت‌های خرید بر اساس پروژه</label><textarea rows="5" data-opt-json="home.projects" dir="ltr">{{ wp_json_encode($opts['home']['projects'] ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</textarea></div>
          </div>
        </div>
      </section>

      {{-- ==================== تب ۳: فروشگاه ==================== --}}
      <section class="rs-tab-panel {{ $activeTab === 'shop' ? 'is-active' : '' }}" data-rs-panel="shop">
        <div class="rs-card">
          <h2>چیدمان آرشیو فروشگاه و فیلترهای کناری</h2>
          <div class="rs-grid-2">
            <div class="rs-field"><label>روش صفحه‌بندی فروشگاه</label><select data-opt="shop.pagination_mode"><option value="numbers" {{ ($opts['shop']['pagination_mode'] ?? 'numbers') === 'numbers' ? 'selected' : '' }}>شماره صفحه</option><option value="more" {{ ($opts['shop']['pagination_mode'] ?? '') === 'more' ? 'selected' : '' }}>نمایش بیشتر</option><option value="infinite" {{ ($opts['shop']['pagination_mode'] ?? '') === 'infinite' ? 'selected' : '' }}>اسکرول بی‌نهایت</option></select></div>
            <div class="rs-field"><label>تعداد محصول در هر صفحه</label><input type="number" data-opt="shop.per_page" value="{{ $opts['shop']['per_page'] ?? 9 }}" dir="ltr"></div>
            <div class="rs-field">
              <label>حالت نمایش پیش‌فرض</label>
              <select data-opt="shop.default_view">
                <option value="grid" {{ ($opts['shop']['default_view'] ?? 'grid') === 'grid' ? 'selected' : '' }}>شبکه‌ای (Grid)</option>
                <option value="list" {{ ($opts['shop']['default_view'] ?? '') === 'list' ? 'selected' : '' }}>لیستی (List)</option>
              </select>
            </div>
            <div class="rs-field">
              <label>مرتب‌سازی پیش‌فرض</label>
              <select data-opt="shop.default_sort">
                <option value="default" {{ ($opts['shop']['default_sort'] ?? '') === 'default' ? 'selected' : '' }}>پیش‌فرض فروشگاه</option>
                <option value="newest" {{ ($opts['shop']['default_sort'] ?? '') === 'newest' ? 'selected' : '' }}>جدیدترین</option>
                <option value="bestseller" {{ ($opts['shop']['default_sort'] ?? '') === 'bestseller' ? 'selected' : '' }}>پرفروش‌ترین</option>
                <option value="popular" {{ ($opts['shop']['default_sort'] ?? '') === 'popular' ? 'selected' : '' }}>محبوب‌ترین</option>
                <option value="cheap" {{ ($opts['shop']['default_sort'] ?? '') === 'cheap' ? 'selected' : '' }}>ارزان‌ترین</option>
                <option value="expensive" {{ ($opts['shop']['default_sort'] ?? '') === 'expensive' ? 'selected' : '' }}>گران‌ترین</option>
              </select>
            </div>
            <div class="rs-field"><label>آستانه هشدار «آخرین موجودی» (عدد)</label><input type="number" data-opt="shop.low_stock_threshold" value="{{ $opts['shop']['low_stock_threshold'] ?? 5 }}" dir="ltr"></div>
          </div>
          <div class="rs-toggles">
            <label class="rs-switch"><input type="checkbox" data-opt="shop.ajax_filtering" {{ !empty($opts['shop']['ajax_filtering']) ? 'checked' : '' }}><span>فیلتر زنده بدون رفرش صفحه (AJAX Filtering)</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="shop.filter_categories" {{ !empty($opts['shop']['filter_categories']) ? 'checked' : '' }}><span>نمایش فیلتر دسته‌بندی‌ها</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="shop.filter_brands" {{ !empty($opts['shop']['filter_brands']) ? 'checked' : '' }}><span>نمایش فیلتر برندها</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="shop.filter_price" {{ !empty($opts['shop']['filter_price']) ? 'checked' : '' }}><span>نمایش فیلتر محدوده قیمت</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="shop.filter_on_sale" {{ !empty($opts['shop']['filter_on_sale']) ? 'checked' : '' }}><span>گزینه «فقط کالاهای تخفیف‌دار»</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="shop.filter_in_stock" {{ !empty($opts['shop']['filter_in_stock']) ? 'checked' : '' }}><span>گزینه «فقط کالاهای موجود»</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="shop.sidebar_banner_enabled" {{ !empty($opts['shop']['sidebar_banner_enabled']) ? 'checked' : '' }}><span>نمایش بنر تبلیغاتی زیر فیلترها</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="shop.subcat_tiles_enabled" {{ !empty($opts['shop']['subcat_tiles_enabled']) ? 'checked' : '' }}><span>نمایش کاشی‌های زیردسته در پایین فروشگاه</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="shop.cta_band_enabled" {{ !empty($opts['shop']['cta_band_enabled']) ? 'checked' : '' }}><span>نمایش نوار مشاوره رایگان پایین فروشگاه</span></label>
          </div>
        </div>

        <div class="rs-card">
          <h2>تنظیمات ارسال، سبد خرید و تسویه حساب ۴ مرحله‌ای</h2>
          <div class="rs-grid-2">
            @foreach(['registered' => 'ثبت سفارش', 'payment' => 'تأیید پرداخت', 'preparing' => 'آماده‌سازی', 'shipping' => 'ارسال'] as $trackingKey => $trackingLabel)
              <div class="rs-field"><label>وضعیت ووکامرس برای {{ $trackingLabel }}</label>
                <select data-opt="shop.tracking_{{ $trackingKey }}">
                  <option value="">انتخاب نشده</option>
                  @foreach((rs_is_wc() ? wc_get_order_statuses() : []) as $statusKey => $statusName)
                    <option value="{{ $statusKey }}" {{ ($opts['shop']['tracking_' . $trackingKey] ?? '') === $statusKey ? 'selected' : '' }}>{{ $statusName }}</option>
                  @endforeach
                </select>
              </div>
            @endforeach
            <div class="rs-field rs-full"><small>هر وضعیت نشان‌دهنده رسیدن سفارش به همان مرحله و مراحل قبل از آن است. تحویل فقط پس از تأیید مشتری یا انتخاب وضعیت «تحویل شده» نمایش داده می‌شود؛ وضعیت‌های لغوشده، مسترد و ناموفق پیشرفت ندارند.</small></div>
            <div class="rs-field"><label>سقف ارسال رایگان (تومان)</label><input type="number" data-opt="shop.free_shipping_threshold" value="{{ $opts['shop']['free_shipping_threshold'] ?? 10000000 }}" dir="ltr"></div>
            <div class="rs-field"><label>هزینه بسته‌بندی هدیه (تومان)</label><input type="number" data-opt="shop.gift_wrap_cost" value="{{ $opts['shop']['gift_wrap_cost'] ?? 35000 }}" dir="ltr"></div>
            <div class="rs-field"><label>عنوان پیک فروشگاه</label><input type="text" data-opt="shop.courier_title" value="{{ $opts['shop']['courier_title'] ?? '' }}"></div>
            <div class="rs-field"><label>هزینه پیک (واحد پول ووکامرس)</label><input type="number" min="0" step="any" data-opt="shop.courier_cost" value="{{ $opts['shop']['courier_cost'] ?? 0 }}" dir="ltr"></div>
            <div class="rs-field rs-full"><label>شهرهای قابل ارسال با پیک (با ویرگول جدا کنید؛ خالی یعنی غیرفعال)</label><input type="text" data-opt="shop.courier_cities" value="{{ $opts['shop']['courier_cities'] ?? '' }}"></div>
            <div class="rs-field"><label>عنوان تحویل حضوری</label><input type="text" data-opt="shop.pickup_title" value="{{ $opts['shop']['pickup_title'] ?? '' }}"></div>
            <div class="rs-field"><label>هزینه تحویل حضوری (واحد پول ووکامرس)</label><input type="number" min="0" step="any" data-opt="shop.pickup_cost" value="{{ $opts['shop']['pickup_cost'] ?? 0 }}" dir="ltr"></div>
            <div class="rs-field rs-full"><label>نشانی محل تحویل حضوری</label><input type="text" data-opt="shop.pickup_address" value="{{ $opts['shop']['pickup_address'] ?? '' }}"></div>
            <div class="rs-field"><label>تعداد روزهای قابل انتخاب از امروز (۱ تا ۳۰)</label><input type="number" min="1" max="30" data-opt="shop.delivery_days_ahead" value="{{ $opts['shop']['delivery_days_ahead'] ?? 6 }}" dir="ltr"></div>
            <div class="rs-field"><label>زمان تقریبی پیش‌فرض سایر روش‌ها (اختیاری)</label><input type="text" data-opt="shop.shipping_eta_default" value="{{ $opts['shop']['shipping_eta_default'] ?? '' }}" placeholder="مثلاً ۲ تا ۴ روز کاری"></div>
            <div class="rs-field rs-full"><label>زمان تقریبی برای روش‌های ووکامرس؛ هر خط شناسه روش یا نرخ = توضیح</label><textarea rows="3" data-opt="shop.shipping_eta_map" placeholder="flat_rate:1=۲ تا ۴ روز کاری">{{ $opts['shop']['shipping_eta_map'] ?? '' }}</textarea><small>شناسه نرخ را از تنظیمات روش ارسال ووکامرس وارد کنید. بدون تنظیم، زمان نامشخص نمایش داده می‌شود.</small></div>
            <div class="rs-field rs-full"><label>بازه‌های زمانی تحویل سفارش (با ویرگول جدا کنید)</label><input type="text" data-opt="shop.delivery_slots" value="{{ $opts['shop']['delivery_slots'] ?? '' }}"></div>
          </div>
          <div class="rs-toggles">
            <label class="rs-switch"><input type="checkbox" data-opt="shop.courier_enabled" {{ !empty($opts['shop']['courier_enabled']) ? 'checked' : '' }}><span>فعال‌سازی پیک فروشگاه برای شهرهای مشخص‌شده</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="shop.pickup_enabled" {{ !empty($opts['shop']['pickup_enabled']) ? 'checked' : '' }}><span>فعال‌سازی تحویل حضوری از فروشگاه</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="shop.delivery_slots_enabled" {{ !empty($opts['shop']['delivery_slots_enabled']) ? 'checked' : '' }}><span>فعال‌سازی انتخاب روز و بازه زمانی برای پیک و تحویل حضوری (هر دو گزینه به این تنظیم نیاز دارند)</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="shop.gift_wrap_enabled" {{ !empty($opts['shop']['gift_wrap_enabled']) ? 'checked' : '' }}><span>گزینه بسته‌بندی هدیه در تسویه حساب</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="shop.official_invoice_enabled" {{ !empty($opts['shop']['official_invoice_enabled']) ? 'checked' : '' }}><span>گزینه درخواست فاکتور رسمی</span></label>
          </div>
        </div>
        <div class="rs-card">
          <h2>بازخورد تحویل و یادآوری پیامکی</h2>
          <p class="rs-muted">ابتدا وضعیت «ارسال» را در همین تب انتخاب و تنظیمات را ذخیره کنید؛ وضعیت تکمیل‌شده، لغوشده، مسترد، ناموفق و تحویل‌شده برای ارسال پیام استفاده نمی‌شوند. سپس اتصال «بازخورد تحویل رنگ صادقی» را در IPPanel Core فعال کنید. فعال‌سازی خودکار به صورت پیش‌فرض خاموش است.</p>
          <div class="rs-toggles"><label class="rs-switch"><input type="checkbox" data-opt="delivery_feedback.enabled" {{ !empty($opts['delivery_feedback']['enabled']) ? 'checked' : '' }}><span>ارسال خودکار ۷۲ ساعت بعد از ورود سفارش به وضعیت ارسال و یادآوری هر ۴ روز پس از پاسخ خیر</span></label></div>
          <div class="rs-grid-2">
            <div class="rs-field"><label>روش ارسال پیامک</label><select data-opt="delivery_feedback.mode"><option value="text" {{ ($opts['delivery_feedback']['mode'] ?? '') === 'text' ? 'selected' : '' }}>متن دستی</option><option value="pattern" {{ ($opts['delivery_feedback']['mode'] ?? '') === 'pattern' ? 'selected' : '' }}>پترن</option></select></div>
            <div class="rs-field"><label>کد پترن</label><input type="text" data-opt="delivery_feedback.pattern" value="{{ $opts['delivery_feedback']['pattern'] ?? '' }}" dir="ltr"><small>متغیرهای پترن: order_id و link</small></div>
            <div class="rs-field rs-full"><label>متن پیامک دستی</label><textarea rows="3" data-opt="delivery_feedback.text">{{ $opts['delivery_feedback']['text'] ?? '' }}</textarea><small>از {order_id} برای شماره و {link} برای پیوند استفاده کنید. درج هر دو متغیر الزامی است.</small></div>
            <div class="rs-field"><label for="rs-feedback-test-order">شماره سفارش برای ارسال آزمایشی به مشتری</label><input id="rs-feedback-test-order" type="number" min="1" dir="ltr"><small>تنظیمات را پیش از ارسال ذخیره کنید؛ پیامک به شماره صورتحساب این سفارش ارسال می‌شود.</small></div>
            <div class="rs-field"><label>آزمایش اتصال</label><button type="button" class="rs-btn rs-btn-outline" id="rs-feedback-test-send">ارسال پیام بازخورد</button></div>
          </div><p class="rs-muted">ارسال‌های زمان‌بندی‌شده به WP-Cron وابسته‌اند؛ برای زمان‌بندی قابل اطمینان cron سرور را فعال کنید. از سفارش‌هایی که قبل از فعال‌سازی در وضعیت ارسال بوده‌اند می‌توان به‌صورت دستی پیام فرستاد.</p>
        </div>
      </section>

      {{-- ==================== تب ۴: تک‌محصول ==================== --}}
      <section class="rs-tab-panel {{ $activeTab === 'product' ? 'is-active' : '' }}" data-rs-panel="product">
        <div class="rs-card">
          <h2>جزئیات صفحه تک‌محصول (Single Product)</h2>
          <div class="rs-grid-2"><div class="rs-field"><label>شناسه دسته‌های نیازمند تأیید (با ویرگول جدا کنید)</label><input type="text" data-opt="product.consent_category_ids" value="{{ $opts['product']['consent_category_ids'] ?? '' }}" dir="ltr"></div><div class="rs-field"><label>متن تأیید الزامی پیش از خرید</label><textarea rows="3" data-opt="product.consent_text">{{ $opts['product']['consent_text'] ?? '' }}</textarea><small>این شرط شامل زیردسته‌هاست و متن پذیرفته‌شده در ردیف سفارش ذخیره می‌شود.</small></div></div>
          <div class="rs-toggles">
            <label class="rs-switch"><input type="checkbox" data-opt="product.gallery_zoom" {{ !empty($opts['product']['gallery_zoom']) ? 'checked' : '' }}><span>دکمه بزرگ‌نمایی گالری تصویر</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="product.show_brand" {{ !empty($opts['product']['show_brand']) ? 'checked' : '' }}><span>نمایش نام برند</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="product.show_sku" {{ !empty($opts['product']['show_sku']) ? 'checked' : '' }}><span>نمایش کد کالا (SKU)</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="product.show_rating_row" {{ !empty($opts['product']['show_rating_row']) ? 'checked' : '' }}><span>نمایش ردیف ستاره‌ها و تعداد فروش</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="product.show_trust_badges" {{ !empty($opts['product']['show_trust_badges']) ? 'checked' : '' }}><span>نمایش نشان‌های اعتماد زیر دکمه خرید</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="product.tab_description" {{ !empty($opts['product']['tab_description']) ? 'checked' : '' }}><span>تب توضیحات محصول</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="product.tab_specs" {{ !empty($opts['product']['tab_specs']) ? 'checked' : '' }}><span>تب جدول مشخصات فنی</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="product.tab_reviews" {{ !empty($opts['product']['tab_reviews']) ? 'checked' : '' }}><span>تب دیدگاه‌ها و امتیاز خریداران</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="product.tab_shipping" {{ !empty($opts['product']['tab_shipping']) ? 'checked' : '' }}><span>تب شرایط ارسال و مرجوعی</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="product.related_enabled" {{ !empty($opts['product']['related_enabled']) ? 'checked' : '' }}><span>ریل محصولات مرتبط پایین صفحه</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="product.mobile_sticky_buy" {{ !empty($opts['product']['mobile_sticky_buy']) ? 'checked' : '' }}><span>نوار چسبان خرید سریع در موبایل</span></label>
          </div>
          <div class="rs-grid-2" style="margin-top:14px">
            <div class="rs-field"><label>برچسب محل ارسال</label><input type="text" data-opt="product.warehouse_label" value="{{ $opts['product']['warehouse_label'] ?? '' }}"></div>
            <div class="rs-field"><label>برچسب گارانتی و اصالت</label><input type="text" data-opt="product.warranty_label" value="{{ $opts['product']['warranty_label'] ?? '' }}"></div>
            <div class="rs-field"><label>تعداد محصولات مرتبط</label><input type="number" data-opt="product.related_count" value="{{ $opts['product']['related_count'] ?? 8 }}" dir="ltr"></div>
            <div class="rs-field rs-full"><label>متن توضیح تب ارسال و مرجوعی</label><textarea rows="2" data-opt="product.shipping_tab_text">{{ $opts['product']['shipping_tab_text'] ?? '' }}</textarea></div>
            <div class="rs-field rs-full"><label>نکات تیتروار تب ارسال و مرجوعی (هر خط یک مورد)</label><textarea rows="3" data-opt="product.shipping_tab_bullets">{{ $opts['product']['shipping_tab_bullets'] ?? '' }}</textarea></div>
          </div>
        </div>
      </section>

      {{-- ==================== تب ۵: سئو ==================== --}}
      <section class="rs-tab-panel {{ $activeTab === 'seo' ? 'is-active' : '' }}" data-rs-panel="seo">
        <div class="rs-card">
          <h2>موتور سئو داخلی و متاتگ‌ها</h2>
          <div class="rs-toggles">
            <label class="rs-switch"><input type="checkbox" data-opt="seo.enabled" {{ !empty($opts['seo']['enabled']) ? 'checked' : '' }}><span>فعال‌سازی موتور سئو داخلی قالب</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="seo.coexist_with_plugins" {{ !empty($opts['seo']['coexist_with_plugins']) ? 'checked' : '' }}><span>همزیستی هوشمند (واگذاری خودکار متاتگ‌ها در صورت نصب Yoast یا RankMath)</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="seo.canonical_enabled" {{ !empty($opts['seo']['canonical_enabled']) ? 'checked' : '' }}><span>تولید خودکار لینک Canonical</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="seo.opengraph_enabled" {{ !empty($opts['seo']['opengraph_enabled']) ? 'checked' : '' }}><span>تگ‌های OpenGraph (تلگرام، واتساپ، اینستاگرام + متای قیمت محصول)</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="seo.twitter_card_enabled" {{ !empty($opts['seo']['twitter_card_enabled']) ? 'checked' : '' }}><span>تگ‌های Twitter Card</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="seo.noindex_search" {{ !empty($opts['seo']['noindex_search']) ? 'checked' : '' }}><span>Noindex صفحات نتایج جستجوی داخلی</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="seo.noindex_cart_checkout" {{ !empty($opts['seo']['noindex_cart_checkout']) ? 'checked' : '' }}><span>Noindex صفحات سبد خرید، تسویه حساب و پنل کاربری</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="seo.noindex_paged_archives" {{ !empty($opts['seo']['noindex_paged_archives']) ? 'checked' : '' }}><span>Noindex صفحات ۲ به بعد آرشیوها</span></label>
          </div>
          <div class="rs-grid-2" style="margin-top:14px">
            <div class="rs-field"><label>عنوان سئو صفحه اصلی</label><input type="text" data-opt="seo.home_title" value="{{ $opts['seo']['home_title'] ?? '' }}"></div>
            <div class="rs-field"><label>جداکننده عنوان صفحات (Title Separator)</label><input type="text" data-opt="seo.title_separator" value="{{ $opts['seo']['title_separator'] ?? '|' }}" dir="ltr"></div>
            <div class="rs-field rs-full"><label>توضیحات متا (Meta Description) صفحه اصلی</label><textarea rows="2" data-opt="seo.home_description">{{ $opts['seo']['home_description'] ?? '' }}</textarea></div>
            <div class="rs-field rs-full"><label>توضیحات متا صفحه آرشیو فروشگاه</label><textarea rows="2" data-opt="seo.shop_description">{{ $opts['seo']['shop_description'] ?? '' }}</textarea></div>
            <div class="rs-field">
              <label>تصویر پیش‌فرض اشتراک‌گذاری شبکه‌های اجتماعی (OG Image)</label>
              <div class="rs-media-row">
                <input type="text" data-opt="seo.default_og_image" value="{{ $opts['seo']['default_og_image'] ?? '' }}" dir="ltr">
                <button type="button" class="rs-btn rs-btn-soft rs-media-pick">انتخاب</button>
              </div>
            </div>
            <div class="rs-field"><label>کد تأیید Google Search Console</label><input type="text" data-opt="seo.google_verification" value="{{ $opts['seo']['google_verification'] ?? '' }}" dir="ltr"></div>
            <div class="rs-field"><label>کد تأیید Bing Webmaster</label><input type="text" data-opt="seo.bing_verification" value="{{ $opts['seo']['bing_verification'] ?? '' }}" dir="ltr"></div>
            <div class="rs-field"><label>کد تأیید Yandex</label><input type="text" data-opt="seo.yandex_verification" value="{{ $opts['seo']['yandex_verification'] ?? '' }}" dir="ltr"></div>
            <div class="rs-field rs-full"><label>اسکریپت‌های سفارشی داخل &lt;head&gt; (مثل Google Tag Manager / Analytics)</label><textarea rows="3" data-opt="seo.head_scripts" dir="ltr">{{ $opts['seo']['head_scripts'] ?? '' }}</textarea></div>
            <div class="rs-field rs-full"><label>اسکریپت‌های سفارشی انتهای &lt;body&gt; (چت آنلاین و رهگیری)</label><textarea rows="3" data-opt="seo.footer_scripts" dir="ltr">{{ $opts['seo']['footer_scripts'] ?? '' }}</textarea></div>
          </div>
        </div>
      </section>

      {{-- ==================== تب ۶: اسکیماها ==================== --}}
      <section class="rs-tab-panel {{ $activeTab === 'schema' ? 'is-active' : '' }}" data-rs-panel="schema">
        <div class="rs-card">
          <h2>اسکیماهای ساختاریافته گوگل (Schema.org JSON-LD Rich Results)</h2>
          <div class="rs-toggles">
            <label class="rs-switch"><input type="checkbox" data-opt="schema.enabled" {{ !empty($opts['schema']['enabled']) ? 'checked' : '' }}><span>فعال‌سازی مولد اسکیما JSON-LD</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="schema.organization_enabled" {{ !empty($opts['schema']['organization_enabled']) ? 'checked' : '' }}><span>اسکیمای سازمان (Organization)</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="schema.local_business_enabled" {{ !empty($opts['schema']['local_business_enabled']) ? 'checked' : '' }}><span>اسکیمای کسب‌وکار محلی فروشگاه حضوری (LocalBusiness)</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="schema.website_search_enabled" {{ !empty($opts['schema']['website_search_enabled']) ? 'checked' : '' }}><span>جعبه جستجوی گوگل (WebSite + SearchAction)</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="schema.breadcrumb_enabled" {{ !empty($opts['schema']['breadcrumb_enabled']) ? 'checked' : '' }}><span>اسکیمای مسیر صفحه (BreadcrumbList)</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="schema.product_schema_enabled" {{ !empty($opts['schema']['product_schema_enabled']) ? 'checked' : '' }}><span>اسکیمای کامل محصول (Product + Offer + AggregateRating + Review + ReturnPolicy)</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="schema.product_currency_irr" {{ !empty($opts['schema']['product_currency_irr']) ? 'checked' : '' }}><span>تبدیل خودکار قیمت اسکیما به ریال (IRR) برای تطابق با استاندارد Google Merchant</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="schema.article_schema_enabled" {{ !empty($opts['schema']['article_schema_enabled']) ? 'checked' : '' }}><span>اسکیمای مقالات آموزشی (BlogPosting)</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="schema.faq_schema_enabled" {{ !empty($opts['schema']['faq_schema_enabled']) ? 'checked' : '' }}><span>اسکیمای سوالات متداول (FAQPage)</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="schema.itemlist_schema_enabled" {{ !empty($opts['schema']['itemlist_schema_enabled']) ? 'checked' : '' }}><span>اسکیمای فهرست محصولات آرشیو (ItemList)</span></label>
          </div>
          <div class="rs-grid-2" style="margin-top:14px">
            <div class="rs-field">
              <label>نوع کسب‌وکار در Schema.org</label>
              <select data-opt="schema.business_type">
                <option value="HomeAndConstructionBusiness" {{ ($opts['schema']['business_type'] ?? '') === 'HomeAndConstructionBusiness' ? 'selected' : '' }}>HomeAndConstructionBusiness (مصالح، رنگ و دکوراسیون)</option>
                <option value="Store" {{ ($opts['schema']['business_type'] ?? '') === 'Store' ? 'selected' : '' }}>Store (فروشگاه)</option>
                <option value="HardwareStore" {{ ($opts['schema']['business_type'] ?? '') === 'HardwareStore' ? 'selected' : '' }}>HardwareStore (ابزار و رنگ)</option>
              </select>
            </div>
            <div class="rs-field"><label>نام رسمی کسب‌وکار</label><input type="text" data-opt="schema.business_name" value="{{ $opts['schema']['business_name'] ?? '' }}"></div>
            <div class="rs-field"><label>کد پستی فروشگاه حضوری</label><input type="text" data-opt="schema.postal_code" value="{{ $opts['schema']['postal_code'] ?? '' }}" dir="ltr"></div>
            <div class="rs-field"><label>مهلت بازگشت کالا در اسکیما (روز)</label><input type="number" data-opt="schema.return_policy_days" value="{{ $opts['schema']['return_policy_days'] ?? 7 }}" dir="ltr"></div>
            <div class="rs-field"><label>عرض جغرافیایی (Latitude)</label><input type="text" data-opt="schema.geo_lat" value="{{ $opts['schema']['geo_lat'] ?? '' }}" dir="ltr"></div>
            <div class="rs-field"><label>طول جغرافیایی (Longitude)</label><input type="text" data-opt="schema.geo_lng" value="{{ $opts['schema']['geo_lng'] ?? '' }}" dir="ltr"></div>
            <div class="rs-field"><label>ساعت شروع کار (HH:MM)</label><input type="text" data-opt="schema.opens" value="{{ $opts['schema']['opens'] ?? '06:30' }}" dir="ltr"></div>
            <div class="rs-field"><label>ساعت پایان کار (HH:MM)</label><input type="text" data-opt="schema.closes" value="{{ $opts['schema']['closes'] ?? '20:30' }}" dir="ltr"></div>
          </div>
        </div>
      </section>

      {{-- ==================== تب ۷: سرعت سایت ==================== --}}
      <section class="rs-tab-panel {{ $activeTab === 'performance' ? 'is-active' : '' }}" data-rs-panel="performance">
        <div class="rs-card">
          <h2>بهینه‌سازی سرعت سایت و Core Web Vitals (LCP / INP / CLS)</h2>
          <div class="rs-toggles">
            <label class="rs-switch"><input type="checkbox" data-opt="performance.preload_fonts" {{ !empty($opts['performance']['preload_fonts']) ? 'checked' : '' }}><span>پیش‌بارگذاری (Preload) فونت اصلی IRANYekanX برای حذف پرش متن (CLS)</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="performance.preload_lcp_image" {{ !empty($opts['performance']['preload_lcp_image']) ? 'checked' : '' }}><span>Preload + fetchpriority="high" روی تصویر اصلی هیرو و محصول (بهبود LCP)</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="performance.disable_emojis" {{ !empty($opts['performance']['disable_emojis']) ? 'checked' : '' }}><span>حذف اسکریپت و استایل ایموجی‌های پیش‌فرض وردپرس</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="performance.disable_embeds" {{ !empty($opts['performance']['disable_embeds']) ? 'checked' : '' }}><span>غیرفعال‌سازی اسکریپت wp-embed</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="performance.disable_dashicons_guests" {{ !empty($opts['performance']['disable_dashicons_guests']) ? 'checked' : '' }}><span>عدم بارگذاری فونت سنگین Dashicons برای کاربران مهمان</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="performance.dequeue_block_styles" {{ !empty($opts['performance']['dequeue_block_styles']) ? 'checked' : '' }}><span>حذف CSS بلوک‌های گوتنبرگ در صفحات فروشگاهی</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="performance.dequeue_wc_styles" {{ !empty($opts['performance']['dequeue_wc_styles']) ? 'checked' : '' }}><span>حذف استایل‌های اضافی پیش‌فرض ووکامرس (اتکا به CSS سبک قالب)</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="performance.disable_cart_fragments" {{ !empty($opts['performance']['disable_cart_fragments']) ? 'checked' : '' }}><span>غیرفعال‌سازی AJAX سنگین wc-cart-fragments در صفحات غیرسبد</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="performance.defer_scripts" {{ !empty($opts['performance']['defer_scripts']) ? 'checked' : '' }}><span>افزودن ویژگی defer به اسکریپت‌ها برای حذف مسدودسازی رندر</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="performance.clean_wp_head" {{ !empty($opts['performance']['clean_wp_head']) ? 'checked' : '' }}><span>پاکسازی تگ‌های اضافی هدر (RSD, WLW, Shortlink, WP Generator)</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="performance.disable_xmlrpc" {{ !empty($opts['performance']['disable_xmlrpc']) ? 'checked' : '' }}><span>غیرفعال‌سازی XML-RPC (افزایش امنیت و کاهش بار سرور)</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="performance.cache_frontpage_queries" {{ !empty($opts['performance']['cache_frontpage_queries']) ? 'checked' : '' }}><span>کش هوشمند کوئری‌های صفحه اصلی (Transient Cache)</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="performance.minify_html" {{ !empty($opts['performance']['minify_html']) ? 'checked' : '' }}><span>فشرده‌سازی امن خروجی HTML (حذف فاصله‌ها و کامنت‌های اضافی)</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="performance.remove_query_strings" {{ !empty($opts['performance']['remove_query_strings']) ? 'checked' : '' }}><span>حذف رشته نسخه (?ver=) از فایل‌های استاتیک</span></label>
          </div>
          <div class="rs-grid-2" style="margin-top:14px">
            <div class="rs-field">
              <label>مدیریت Heartbeat API وردپرس</label>
              <select data-opt="performance.heartbeat_mode">
                <option value="slow" {{ ($opts['performance']['heartbeat_mode'] ?? 'slow') === 'slow' ? 'selected' : '' }}>بهینه (کاهش به هر ۶۰ ثانیه)</option>
                <option value="disable_front" {{ ($opts['performance']['heartbeat_mode'] ?? '') === 'disable_front' ? 'selected' : '' }}>غیرفعال کامل در فرانت‌اند</option>
                <option value="default" {{ ($opts['performance']['heartbeat_mode'] ?? '') === 'default' ? 'selected' : '' }}>پیش‌فرض وردپرس</option>
              </select>
            </div>
            <div class="rs-field"><label>دامنه‌های Preconnect خارجی (اختیاری — با ویرگول جدا کنید)</label><input type="text" data-opt="performance.preconnect_urls" value="{{ $opts['performance']['preconnect_urls'] ?? '' }}" dir="ltr"></div>
          </div>
        </div>
      </section>

      {{-- ==================== تب ۸: بروزرسانی با پچ ==================== --}}
      <section class="rs-tab-panel {{ $activeTab === 'updater' ? 'is-active' : '' }}" data-rs-panel="updater">
        <div class="rs-card">
          <h2>وضعیت سیستم و بروزرسانی پچ‌محور (Delta Patch Updater)</h2>
          <div class="rs-status-badges">
            <span>نسخه قالب: <strong>{{ $themeVersion }}</strong></span>
            <span>نسخه PHP: <strong>{{ $phpVersion }}</strong></span>
            <span>نسخه وردپرس: <strong>{{ $wpVersion }}</strong></span>
            <span>ماژول ZipArchive: <strong>{{ $hasZipArchive ? 'فعال (.zip و .json)' : 'غیرفعال (فقط پچ .json)' }}</strong></span>
          </div>

          <div class="rs-patch-upload-box">
            <h3>۱. نصب مستقیم فایل پچ (آفلاین)</h3>
            <p class="rs-muted">فایل پچ (<code>.zip</code> یا <code>.json</code>) تولیدشده با ابزار <code>tools/make-patch.py</code> را انتخاب کنید. پیش از تغییر هر فایل، امضای SHA-256 بررسی و نسخه پشتیبان خودکار ذخیره می‌شود.</p>
            <div class="rs-media-row">
              <input type="file" id="rs-patch-file-input" accept=".zip,.json">
              <button type="button" class="rs-btn rs-btn-primary" id="rs-apply-patch-btn">اعتبارسنجی و اعمال پچ</button>
            </div>
          </div>

          <div class="rs-grid-2" style="margin-top:18px">
            <div class="rs-field">
              <label>آدرس سرور مانیفست بروزرسانی آنلاین (Manifest JSON URL)</label>
              <input type="url" data-opt="updater.manifest_url" value="{{ $opts['updater']['manifest_url'] ?? '' }}" dir="ltr" placeholder="https://updates.example.com/rangesadeghi/manifest.json">
            </div>
            <div class="rs-field">
              <label>کلید لایسنس / توکن دسترسی (اختیاری)</label>
              <input type="text" data-opt="updater.license_key" value="{{ $opts['updater']['license_key'] ?? '' }}" dir="ltr">
            </div>
          </div>

          <div class="rs-toggles" style="margin-top:10px">
            <label class="rs-switch"><input type="checkbox" data-opt="updater.auto_backup_before_patch" {{ !empty($opts['updater']['auto_backup_before_patch']) ? 'checked' : '' }}><span>تهیه بکاپ خودکار از فایل‌های درگیر قبل از هر پچ</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="updater.verify_checksum" {{ !empty($opts['updater']['verify_checksum']) ? 'checked' : '' }}><span>بررسی اجباری امضای امنیتی SHA-256 فایل‌ها</span></label>
            <label class="rs-switch"><input type="checkbox" data-opt="updater.check_wp_updates" {{ !empty($opts['updater']['check_wp_updates']) ? 'checked' : '' }}><span>نمایش اعلان نسخه جدید در پیشخوان وردپرس</span></label>
          </div>

          <div class="rs-btn-row" style="margin-top:14px">
            <button type="button" class="rs-btn rs-btn-outline" id="rs-check-remote-patch-btn">بررسی آنلاین نسخه جدید</button>
            <button type="button" class="rs-btn rs-btn-primary" id="rs-install-remote-patch-btn" hidden>دانلود و نصب خودکار پچ جدید</button>
          </div>
          <div id="rs-remote-patch-result" style="margin-top:10px"></div>
        </div>

        <div class="rs-card">
          <h2>نسخه‌های پشتیبان و بازگردانی یک‌کلیکی (Rollback)</h2>
          @if(!empty($backups))
            <table class="rs-table">
              <thead><tr><th>شناسه بکاپ</th><th>حجم</th><th>زمان ایجاد</th><th>عملیات</th></tr></thead>
              <tbody>
                @foreach($backups as $bk)
                  <tr>
                    <td dir="ltr"><code>{{ $bk['id'] }}</code></td>
                    <td>{{ $bk['size_kb'] }} KB</td>
                    <td dir="ltr">{{ $bk['created_at'] }}</td>
                    <td><button type="button" class="rs-btn rs-btn-soft rs-rollback-btn" data-backup-id="{{ $bk['id'] }}">بازگردانی به این بکاپ</button></td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @else
            <p class="rs-muted">هنوز پچی اعمال نشده است؛ به محض نصب اولین پچ، نسخه پشتیبان خودکار در اینجا فهرست می‌شود.</p>
          @endif
        </div>

        <div class="rs-card">
          <h2>تاریخچه بروزرسانی‌ها و پچ‌ها</h2>
          @if(!empty($patchHistory))
            <table class="rs-table">
              <thead><tr><th>عنوان</th><th>تغییر نسخه</th><th>تعداد فایل</th><th>وضعیت</th><th>تاریخ</th></tr></thead>
              <tbody>
                @foreach($patchHistory as $ph)
                  <tr>
                    <td><strong>{{ $ph['title'] ?? '' }}</strong>@if(!empty($ph['notes']))<br><small class="rs-muted">{{ $ph['notes'] }}</small>@endif</td>
                    <td dir="ltr">{{ $ph['from_version'] ?? '' }} &rarr; <strong>{{ $ph['to_version'] ?? '' }}</strong></td>
                    <td>{{ rs_fa($ph['files_count'] ?? 0) }} فایل</td>
                    <td>{{ ($ph['status'] ?? '') === 'rolled_back' ? 'بازگردانی‌شده' : 'اعمال‌شده' }}</td>
                    <td dir="ltr">{{ $ph['applied_at'] ?? '' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @else
            <p class="rs-muted">گزارشی ثبت نشده است.</p>
          @endif
        </div>
      </section>

    </form>
  </div>
</div>
