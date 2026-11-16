# -*- coding: utf-8 -*-
"""
سازنده صفحات سایت رنگ صادقی
--------------------------------------------------------------------
این اسکریپت هدر، فوتر، منوها و لایه‌های مشترک را یک‌بار تعریف می‌کند و
صفحات HTML مستقل (فروشگاه، دسته‌بندی، محصول، وبلاگ، نوشته، درباره ما،
تماس با ما و فروشگاه‌های ما) را در ریشه پروژه تولید می‌کند.

اجرا:  python3 build.py
"""

NAV = [
    ("index.html", "صفحه اصلی", "home"),
    ("shop.html", "فروشگاه", "shop"),
    ("categories.html", "دسته‌بندی محصولات", "categories"),
    ("blog.html", "مجله", "blog"),
    ("about.html", "درباره ما", "about"),
    ("contact.html", "تماس با ما", "contact"),
    ("stores.html", "فروشگاه‌های ما", "stores"),
]

SPRITE = """
    <symbol id="i-menu" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></symbol>
    <symbol id="i-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></symbol>
    <symbol id="i-user" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4.5 21a7.5 7.5 0 0 1 15 0"/></symbol>
    <symbol id="i-bag" viewBox="0 0 24 24"><path d="M5 8h14l-1 13H6L5 8Z"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/></symbol>
    <symbol id="i-heart" viewBox="0 0 24 24"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/></symbol>
    <symbol id="i-chevron-down" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></symbol>
    <symbol id="i-chevron-left" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></symbol>
    <symbol id="i-chevron-right" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></symbol>
    <symbol id="i-close" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></symbol>
    <symbol id="i-home" viewBox="0 0 24 24"><path d="m3 11 9-8 9 8"/><path d="M5 10v11h14V10M9 21v-7h6v7"/></symbol>
    <symbol id="i-grid" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></symbol>
    <symbol id="i-list" viewBox="0 0 24 24"><path d="M8 6h12M8 12h12M8 18h12"/><circle cx="4" cy="6" r="1"/><circle cx="4" cy="12" r="1"/><circle cx="4" cy="18" r="1"/></symbol>
    <symbol id="i-filter" viewBox="0 0 24 24"><path d="M4 6h16M7 12h10M10 18h4"/></symbol>
    <symbol id="i-arrow-up" viewBox="0 0 24 24"><path d="m18 15-6-6-6 6"/></symbol>
    <symbol id="i-plus" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></symbol>
    <symbol id="i-minus" viewBox="0 0 24 24"><path d="M5 12h14"/></symbol>
    <symbol id="i-trash" viewBox="0 0 24 24"><path d="M4 7h16M9 7V4h6v3M7 7l1 14h8l1-14M10 11v6M14 11v6"/></symbol>
    <symbol id="i-phone" viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.4 19.4 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.9a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.9.3 1.9.6 2.9.7a2 2 0 0 1 1.7 2Z"/></symbol>
    <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></symbol>
    <symbol id="i-pin" viewBox="0 0 24 24"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></symbol>
    <symbol id="i-mail" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></symbol>
    <symbol id="i-send" viewBox="0 0 24 24"><path d="m22 2-7 20-4-9-9-4 20-7Z"/><path d="M22 2 11 13"/></symbol>
    <symbol id="i-play" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m10 8 6 4-6 4V8Z"/></symbol>
    <symbol id="i-check" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></symbol>
    <symbol id="i-check-circle" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.6 2.6L16 9"/></symbol>
    <symbol id="i-instagram" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".8" fill="currentColor" stroke="none"/></symbol>
    <symbol id="i-whatsapp" viewBox="0 0 24 24"><path d="M20.5 11.6a8.5 8.5 0 0 1-12.6 7.4L3 20.5 4.6 16A8.5 8.5 0 1 1 20.5 11.6Z"/><path d="M8.2 7.5c.3-.6.6-.6.9-.6h.7c.2 0 .4.1.5.4l.9 2c.1.3.1.5-.1.7l-.7.9c-.2.2-.2.4-.1.6.6 1.2 1.6 2.2 2.8 2.8.2.1.4.1.6-.1l.9-1c.2-.2.4-.3.7-.2l2 .9c.3.1.4.3.4.5 0 .8-.4 1.5-1.1 1.9-.6.3-1.4.5-2.2.3-1.3-.3-3.1-1.1-4.8-2.7-1.4-1.4-2.4-3-2.7-4.3-.3-1.1 0-1.7.3-2.1Z"/></symbol>
    <symbol id="i-calendar" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 11h18"/></symbol>
    <symbol id="i-book" viewBox="0 0 24 24"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5Z"/><path d="M20 18v3H6.5A2.5 2.5 0 0 1 4 18.5"/></symbol>
    <symbol id="i-tag" viewBox="0 0 24 24"><path d="M20.5 12.5 12 21l-9-9 8.5-8.5H20V12Z"/><circle cx="16" cy="8" r="1.4"/></symbol>
    <symbol id="i-share" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 10.6 6.8-3.9M8.6 13.4l6.8 3.9"/></symbol>
    <symbol id="i-link" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1.5 1.5"/><path d="M14 11a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7L12.5 20"/></symbol>
    <symbol id="i-truck" viewBox="0 0 24 24"><path d="M3 7h11v9H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="18" r="1.8"/><circle cx="17.5" cy="18" r="1.8"/></symbol>
    <symbol id="i-shield" viewBox="0 0 24 24"><path d="M12 3 5 6v6c0 4 3 7 7 9 4-2 7-5 7-9V6Z"/><path d="m9 12 2 2 4-4"/></symbol>
    <symbol id="i-award" viewBox="0 0 24 24"><circle cx="12" cy="9" r="5"/><path d="m8.5 13.5-1.5 7 5-2.5 5 2.5-1.5-7"/></symbol>
    <symbol id="i-layers" viewBox="0 0 24 24"><path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 13 9 5 9-5"/></symbol>
    <symbol id="i-globe" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18 15 15 0 0 1 0-18Z"/></symbol>
    <symbol id="i-store" viewBox="0 0 24 24"><path d="M4 9h16v11H4z"/><path d="M3 9l2-5h14l2 5"/><path d="M9 20v-6h6v6"/></symbol>
    <symbol id="i-map" viewBox="0 0 24 24"><path d="m9 4 6 2 6-2v16l-6 2-6-2-6 2V6Z"/><path d="M9 4v16M15 6v16"/></symbol>
    <symbol id="i-sparkles" viewBox="0 0 24 24"><path d="m11 3 1.7 4.3L17 9l-4.3 1.7L11 15l-1.7-4.3L5 9l4.3-1.7Z"/><path d="m18 14 .9 2.1L21 17l-2.1.9L18 20l-.9-2.1L15 17l2.1-.9Z"/></symbol>
    <symbol id="i-palette" viewBox="0 0 24 24"><path d="M12 21a9 9 0 1 1 9-9c0 2.5-2 3-3.5 3H16a2 2 0 0 0-1.4 3.4c.6.7.2 2.6-2.6 2.6Z"/><circle cx="8" cy="10" r="1"/><circle cx="12" cy="7.5" r="1"/><circle cx="16" cy="10" r="1"/></symbol>
    <symbol id="i-quote" viewBox="0 0 24 24"><path d="M9 7H5v5h4v5H5M19 7h-4v5h4v5h-4"/></symbol>
    <symbol id="i-info" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></symbol>
    <symbol id="i-box" viewBox="0 0 24 24"><path d="m12 3 9 5v8l-9 5-9-5V8Z"/><path d="m3 8 9 5 9-5M12 13v8"/></symbol>
    <symbol id="i-wallet" viewBox="0 0 24 24"><path d="M4 7h13a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h11"/><path d="M16 13h2"/></symbol>
    <symbol id="i-gift" viewBox="0 0 24 24"><rect x="3" y="8" width="18" height="12" rx="2"/><path d="M3 12h18M12 8v12"/><path d="M12 8S9.5 3 7.5 4.4C5.8 5.6 8 8 12 8Zm0 0s2.5-5 4.5-3.6C18.2 5.6 16 8 12 8Z"/></symbol>
    <symbol id="i-ticket" viewBox="0 0 24 24"><path d="M3 9V6h18v3a2.5 2.5 0 0 0 0 5v4H3v-4a2.5 2.5 0 0 0 0-5Z"/><path d="M13 9v1.5M13 13.5V15"/></symbol>
    <symbol id="i-logout" viewBox="0 0 24 24"><path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3"/><path d="M10 8l-4 4 4 4M6 12h9"/></symbol>
    <symbol id="i-edit" viewBox="0 0 24 24"><path d="M4 20h4l10-10a2.8 2.8 0 0 0-4-4L4 16Z"/><path d="m14.5 5.5 4 4"/></symbol>
    <symbol id="i-card" viewBox="0 0 24 24"><rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path d="M2.5 10h19M6 15h4"/></symbol>
    <symbol id="i-heart-hand" viewBox="0 0 24 24"><path d="M20 12c0 4-8 8-8 8s-8-4-8-8a4 4 0 0 1 8-2 4 4 0 0 1 8 2Z"/></symbol>
    <symbol id="i-eye" viewBox="0 0 24 24"><path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="3"/></symbol>
    <symbol id="i-zoom" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4M11 8v6M8 11h6"/></symbol>
    <symbol id="i-download" viewBox="0 0 24 24"><path d="M12 4v11m0 0-4-4m4 4 4-4"/><path d="M4 19h16"/></symbol>
    <symbol id="i-headset" viewBox="0 0 24 24"><path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="2.5" y="13" width="4" height="6" rx="2"/><rect x="17.5" y="13" width="4" height="6" rx="2"/><path d="M20 19a4 4 0 0 1-4 3h-2"/></symbol>
    <symbol id="i-refresh" viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 4v5h-5"/></symbol>
"""

HEADER_TEMPLATE = """  <header class="site-header hx-header" id="site-header">
    <!-- نوار خدمات بالای سایت -->
    <div class="hx-utility desktop-only">
      <div class="site-container hx-utility-inner">
        <nav class="hx-utility-links" aria-label="دسترسی‌های سریع">
          <a href="stores.html"><svg><use href="#i-store"/></svg>فروشگاه‌های ما</a>
          <a href="order-details.html"><svg><use href="#i-box"/></svg>پیگیری سفارش</a>
          <a href="blog.html"><svg><use href="#i-book"/></svg>مجله رنگ</a>
          <a href="contact.html"><svg><use href="#i-headset"/></svg>مشاوره تخصصی</a>
        </nav>
        <div class="hx-utility-side">
          <span class="hx-utility-note"><svg><use href="#i-truck"/></svg>ارسال رایگان سفارش‌های بالای ۱۰ میلیون تومان</span>
          <div class="social-links">
            <a href="https://www.instagram.com/rangsadeghy" target="_blank" rel="noopener" aria-label="اینستاگرام"><svg><use href="#i-instagram"/></svg></a>
            <a href="https://wa.me/989113294688" target="_blank" rel="noopener" aria-label="واتساپ"><svg><use href="#i-whatsapp"/></svg></a>
            <a href="https://t.me/rangsadeghy" target="_blank" rel="noopener" aria-label="تلگرام"><svg><use href="#i-send"/></svg></a>
          </div>
          <button class="wishlist-top" id="wishlist-top" type="button">
            <svg><use href="#i-heart"/></svg><span>علاقه‌مندی</span><b id="wishlist-count">0</b>
          </button>
        </div>
      </div>
    </div>

    <!-- هدر اصلی دسکتاپ (چسبان + فشرده‌شو) -->
    <div class="main-header desktop-only" id="main-header">
      <div class="site-container hx-main-inner">
        <a class="hx-logo" href="index.html" aria-label="فروشگاه رنگ صادقی">
          <img src="assets/images/logo.png" alt="رنگ صادقی" width="100" height="39">
        </a>

        <div class="category-nav hx-cats">
          <button class="category-button" id="category-button" type="button" aria-expanded="false">
            <svg><use href="#i-menu"/></svg>
            <span>دسته‌بندی کالاها</span>
            <svg class="down-icon"><use href="#i-chevron-down"/></svg>
          </button>
          <div class="mega-menu" id="mega-menu">
            <div class="mega-column">
              <a class="mega-title" href="categories.html">بخش هنری <svg><use href="#i-chevron-left"/></svg></a>
              <a href="shop.html?cat=art">رنگ اکریلیک</a>
              <a href="shop.html?cat=art">رنگ روغن ها</a>
              <a href="shop.html?cat=art">رزین اپوکسی هنری</a>
              <a href="shop.html?cat=art">سفالگری و مجسمه سازی</a>
              <a href="shop.html?cat=art">افکت ها</a>
              <a href="shop.html?cat=art">وارنیش و تثبیت کننده</a>
            </div>
            <div class="mega-column">
              <a class="mega-title" href="categories.html">پتینه <svg><use href="#i-chevron-left"/></svg></a>
              <a href="shop.html?cat=patina">تکسچر ها</a>
              <a href="shop.html?cat=patina">میکروسمنت</a>
              <a href="shop.html?cat=patina">رنگ های دکوراتیو</a>
              <a href="shop.html?cat=patina">ورق ها</a>
              <a href="categories.html">تثبیت کننده ها</a>
            </div>
            <div class="mega-column">
              <a class="mega-title" href="categories.html">ابزار ها <svg><use href="#i-chevron-left"/></svg></a>
              <a href="shop.html?cat=tools">غلطک</a>
              <a href="shop.html?cat=tools">قلم</a>
              <a href="shop.html?cat=tools">لیسه و کاردک</a>
              <a href="categories.html">ماله</a>
              <a href="shop.html?cat=tools">سنباده ها</a>
            </div>
            <div class="mega-column">
              <a class="mega-title" href="categories.html">شابلون و تابلو <svg><use href="#i-chevron-left"/></svg></a>
              <a href="shop.html?cat=stencil">شابلون</a>
              <a href="shop.html?cat=stencil">ماندالا</a>
              <a href="shop.html?cat=stencil">داماسک</a>
              <a href="categories.html">بوم ها</a>
              <a href="categories.html">بیس های چوبی</a>
            </div>
            <div class="mega-highlight">
              <span class="mega-highlight-tag"><svg><use href="#i-sparkles"/></svg>پیشنهاد هفته</span>
              <b>تا ۳۵٪ تخفیف روی رنگ‌های ساختمانی</b>
              <a class="btn btn-primary btn-sm" href="shop.html?sale=1">مشاهده تخفیف‌ها</a>
            </div>
          </div>
        </div>

        <div class="search-box hx-search">
          <form class="search-form" data-search-form>
            <span class="hx-search-icon"><svg><use href="#i-search"/></svg></span>
            <input type="search" placeholder="جستجو در رنگ، پتینه، ابزار و شابلون…" aria-label="جستجوی محصولات" autocomplete="off">
            <button type="submit" aria-label="جستجو"><svg><use href="#i-chevron-left"/></svg></button>
          </form>
          <div class="search-results"></div>
        </div>

        <div class="header-tools hx-tools">
          <button class="hx-tool account-button" type="button" aria-label="ورود یا ثبت نام">
            <svg><use href="#i-user"/></svg><span>ورود | ثبت‌نام</span>
          </button>
          <button class="hx-tool hx-tool-cart cart-button" type="button" aria-label="سبد خرید">
            <svg><use href="#i-bag"/></svg><span>سبد خرید</span><span class="cart-count">0</span>
          </button>
        </div>
      </div>

      <div class="hx-navbar">
        <div class="site-container hx-navbar-inner">
          <nav class="main-nav" aria-label="منوی اصلی">
{NAV_LINKS}
          </nav>
          <a class="hx-nav-deal" href="shop.html?sale=1"><svg><use href="#i-sparkles"/></svg>تخفیف‌های امروز</a>
        </div>
      </div>
    </div>

    <!-- هدر موبایل -->
    <div class="mobile-header mobile-only">
      <div class="hx-mobile-top">
        <button class="mobile-menu-button" type="button" aria-label="باز کردن منو"><svg><use href="#i-menu"/></svg></button>
        <a href="index.html" class="mobile-logo"><img src="assets/images/logo-alt.webp" alt="رنگ صادقی"></a>
        <a class="hx-mobile-store" href="stores.html" aria-label="فروشگاه‌ها"><svg><use href="#i-store"/></svg></a>
      </div>
      <div class="search-box mobile-search">
        <form class="search-form" data-search-form>
          <input type="search" placeholder="جستجوی محصولات" aria-label="جستجوی محصولات" autocomplete="off">
          <button type="submit" aria-label="جستجو"><svg><use href="#i-search"/></svg></button>
        </form>
        <div class="search-results"></div>
      </div>
      <button class="hx-hidden-cart cart-button" type="button" aria-label="سبد خرید"><svg><use href="#i-bag"/></svg><span class="cart-count">0</span></button>
    </div>
  </header>"""

FOOTER = """  <footer class="site-footer hx-footer">
    <div class="hx-footer-top">
      <div class="site-container hx-footer-top-inner">
        <section class="hx-footer-brand">
          <a class="hx-footer-logo" href="index.html"><img src="assets/images/logo.png" alt="فروشگاه رنگ صادقی" loading="lazy"></a>
          <p>فروشگاه رنگ صادقی با تمرکز تخصصی بر رنگ ساختمانی، رنگ صنعتی، رنگ پتینه، هنری و ابزارآلات دستی و برقی حرفه‌ای فعالیت می‌کند؛ با هدف ارائه مجموعه‌ای کامل از محصولات معتبر برای خریدی امن، سریع و دقیق.</p>
          <div class="hx-footer-social">
            <a href="https://www.instagram.com/rangsadeghy" target="_blank" rel="noopener" aria-label="اینستاگرام"><svg><use href="#i-instagram"/></svg></a>
            <a href="https://wa.me/989113294688" target="_blank" rel="noopener" aria-label="واتساپ"><svg><use href="#i-whatsapp"/></svg></a>
            <a href="https://t.me/rangsadeghy" target="_blank" rel="noopener" aria-label="تلگرام"><svg><use href="#i-send"/></svg></a>
            <a href="mailto:rangsadeghi@gmail.com" aria-label="ایمیل"><svg><use href="#i-mail"/></svg></a>
          </div>
        </section>

        <div class="hx-footer-cols">
          <section>
            <h3>خرید و دسترسی</h3>
            <a href="index.html">صفحه اصلی</a>
            <a href="shop.html">فروشگاه</a>
            <a href="categories.html">دسته‌بندی محصولات</a>
            <a href="blog.html">مجله رنگ</a>
            <a href="about.html">درباره ما</a>
          </section>
          <section>
            <h3>دسته‌های پرطرفدار</h3>
            <a href="shop.html?cat=building">رنگ ساختمانی</a>
            <a href="shop.html?cat=art">رنگ هنری</a>
            <a href="shop.html?cat=patina">پتینه و میکروسمنت</a>
            <a href="shop.html?cat=stencil">شابلون و تابلو</a>
            <a href="shop.html?cat=tools">ابزار و لوازم</a>
          </section>
          <section>
            <h3>خدمات مشتریان</h3>
            <a href="order-details.html">پیگیری سفارش</a>
            <a href="cart.html">سبد خرید</a>
            <a href="account.html">حساب کاربری</a>
            <a href="contact.html">تماس و پشتیبانی</a>
            <a href="stores.html">فروشگاه‌های ما</a>
          </section>
          <section class="hx-footer-contact">
            <h3>ارتباط با ما</h3>
            <a href="tel:09113294688"><svg><use href="#i-phone"/></svg><span><bdo dir="ltr">۰۹۱۱۳۲۹۴۶۸۸</bdo></span></a>
            <p><svg><use href="#i-clock"/></svg><span class="hx-nowrap">شنبه تا پنج‌شنبه، ۶:۳۰–۲۰:۳۰</span></p>
            <p><svg><use href="#i-pin"/></svg><span>مازندران، آمل، خیابان طالب آملی، دریای ۳۰/۱، پلاک ۴۴۴</span></p>
            <a href="mailto:rangsadeghi@gmail.com"><svg><use href="#i-mail"/></svg><span>rangsadeghi@gmail.com</span></a>
          </section>
        </div>

        <aside class="hx-footer-license">
          <h3>مجوزهای سایت</h3>
          <a class="enamad" href="https://trustseal.enamad.ir/?id=574425&amp;Code=e8HImAQ72k1oH8npkUuHCXT0c1OmYjHr" target="_blank" rel="noopener" aria-label="نماد اعتماد الکترونیکی">
            <span class="enamad-mark"><svg><use href="#i-shield"/></svg>اینماد</span><small>نماد اعتماد الکترونیکی</small>
          </a>
          <span class="hx-license-note"><svg><use href="#i-shield"/></svg>پرداخت امن با درگاه بانکی</span>
        </aside>
      </div>
    </div>

    <div class="hx-footer-trust">
      <div class="site-container hx-trust-inner">
        <span><svg><use href="#i-check-circle"/></svg>ضمانت اصالت کالا</span>
        <span><svg><use href="#i-card"/></svg>پرداخت امن آنلاین</span>
        <span><svg><use href="#i-refresh"/></svg>۷ روز مهلت بازگشت</span>
        <span><svg><use href="#i-headset"/></svg>پشتیبانی تخصصی رنگ</span>
        <span><svg><use href="#i-truck"/></svg>ارسال به سراسر ایران</span>
      </div>
    </div>

    <div class="hx-footer-bottom">
      <div class="site-container hx-footer-bottom-inner">
        <p>تمامی حقوق فروشگاه رنگ صادقی محفوظ می‌باشد و هرگونه کپی‌برداری مستلزم کسب اجازه‌ی کتبی است.</p>
        <p>پشتیبانی از <a href="https://amolnovin.ir/" target="_blank" rel="noopener">آمل نوین</a></p>
      </div>
    </div>
  </footer>"""

LAYERS = """  <button class="back-to-top" id="back-to-top" type="button" aria-label="بازگشت به بالا"><svg><use href="#i-arrow-up"/></svg></button>

  <nav class="mobile-toolbar mobile-only" aria-label="دسترسی سریع">
    <a href="index.html" class="toolbar-home active" aria-current="page"><svg><use href="#i-home"/></svg><span>خانه</span></a>
    <a href="shop.html" class="toolbar-shop"><svg><use href="#i-grid"/></svg><span>فروشگاه</span></a>
    <button class="mobile-menu-button" type="button"><svg><use href="#i-menu"/></svg><span>منو</span></button>
    <a href="cart.html" class="toolbar-cart"><span class="toolbar-icon"><svg><use href="#i-bag"/></svg><b class="cart-count">0</b></span><span>سبد خرید</span></a>
    <a href="account.html" class="toolbar-account"><svg><use href="#i-user"/></svg><span>حساب کاربری</span></a>
  </nav>

  <div class="page-overlay" id="page-overlay"></div>

  <aside class="side-drawer mobile-menu-drawer" id="mobile-menu-drawer" aria-hidden="true" aria-label="منوی موبایل">
    <div class="drawer-heading"><img src="assets/images/logo.png" alt="رنگ صادقی"><button class="drawer-close" type="button" aria-label="بستن"><svg><use href="#i-close"/></svg></button></div>
    <div class="mobile-drawer-tabs"><button class="active" type="button">منو</button><button type="button" id="drawer-categories-tab">دسته بندی ها</button></div>
    <nav class="mobile-drawer-nav">
      <a href="index.html">صفحه اصلی</a><a href="shop.html">فروشگاه</a><a href="categories.html">دسته بندی محصولات</a><a href="blog.html">بلاگ</a><a href="about.html">درباره ما</a><a href="contact.html">تماس با ما</a><a href="stores.html">فروشگاه های ما</a>
    </nav>
    <div class="mobile-drawer-categories" id="mobile-drawer-categories">
      <details><summary>بخش هنری <svg><use href="#i-chevron-down"/></svg></summary><a href="shop.html?cat=art">رنگ اکریلیک</a><a href="shop.html?cat=art">رنگ روغن ها</a><a href="shop.html?cat=art">رزین اپوکسی</a><a href="shop.html?cat=art">سفالگری و مجسمه سازی</a></details>
      <details><summary>پتینه <svg><use href="#i-chevron-down"/></svg></summary><a href="shop.html?cat=patina">تکسچر ها</a><a href="shop.html?cat=patina">میکروسمنت</a><a href="shop.html?cat=patina">رنگ های دکوراتیو</a></details>
      <details><summary>ابزار ها <svg><use href="#i-chevron-down"/></svg></summary><a href="shop.html?cat=tools">قلم</a><a href="shop.html?cat=tools">غلطک</a><a href="shop.html?cat=tools">لیسه و کاردک</a></details>
      <details><summary>شابلون و تابلو <svg><use href="#i-chevron-down"/></svg></summary><a href="shop.html?cat=stencil">شابلون</a><a href="categories.html">بوم ها</a></details>
    </div>
  </aside>

  <aside class="side-drawer cart-drawer" id="cart-drawer" aria-hidden="true" aria-label="سبد خرید">
    <div class="drawer-heading"><h2>سبد خرید</h2><button class="drawer-close" type="button" aria-label="بستن"><svg><use href="#i-close"/></svg></button></div>
    <div class="cart-items" id="cart-items"></div>
    <div class="cart-footer">
      <div class="cart-total"><span>جمع سبد خرید</span><strong id="cart-total">0 تومان</strong></div>
      <button type="button" class="checkout-button" id="checkout-button">تسویه حساب</button>
      <button type="button" class="continue-shopping drawer-close">ادامه خرید</button>
    </div>
  </aside>

  <section class="modal" id="quick-modal" role="dialog" aria-modal="true" aria-hidden="true" aria-label="مشاهده سریع محصول">
    <button class="modal-close" type="button" aria-label="بستن"><svg><use href="#i-close"/></svg></button>
    <div id="quick-modal-content"></div>
  </section>

  <section class="modal login-modal" id="login-modal" role="dialog" aria-modal="true" aria-hidden="true" aria-label="ورود به حساب کاربری">
    <button class="modal-close" type="button" aria-label="بستن"><svg><use href="#i-close"/></svg></button>
    <img src="assets/images/logo.png" alt="رنگ صادقی" width="110">
    <h2>ورود | ثبت نام</h2>
    <p>برای ورود یا ثبت نام، شماره موبایل خود را وارد کنید.</p>
    <form id="login-form"><label for="mobile-number">شماره موبایل</label><input id="mobile-number" inputmode="numeric" placeholder="مثلاً 09123456789" required pattern="09[0-9]{9}"><button type="submit">دریافت کد تایید</button></form>
  </section>

  <div class="toast" id="toast" role="status" aria-live="polite"><svg><use href="#i-check"/></svg><span></span></div>"""


def nav_links(active: str) -> str:
    items = []
    for href, label, key in NAV:
        cls = ' class="active"' if key == active else ""
        items.append(f'            <a{cls} href="{href}">{label}</a>')
    return "\n".join(items)


def header(active: str) -> str:
    return HEADER_TEMPLATE.replace("{NAV_LINKS}", nav_links(active))


def crumbs(items) -> str:
    """items: list of (label, href|None)"""
    parts = []
    for index, (label, href) in enumerate(items):
        if href and index < len(items) - 1:
            parts.append(f'<a href="{href}">{label}</a><svg><use href="#i-chevron-left"/></svg>')
        else:
            parts.append(f'<span class="current">{label}</span>')
    return f'<nav class="breadcrumb" aria-label="مسیر صفحه">\n        <a href="index.html"><svg><use href="#i-home"/></svg></a><svg><use href="#i-chevron-left"/></svg>\n        {"".join(parts[1:] if items[0][0] == "خانه" else parts)}\n      </nav>'


def page(filename, title, description, active, content, extra_head="", body_class="", page_key="home"):
    if body_class.startswith(' class="'):
        body_class = body_class.replace(' class="', ' class="hx-page ', 1)
    elif body_class:
        body_class = body_class
    else:
        body_class = ' class="hx-page"'
    html = f"""<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="{description}">
  <title>{title}</title>
  <link rel="icon" href="assets/images/logo-alt.webp" type="image/webp">
  <link rel="preload" href="assets/fonts/iranyekanx-regular.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="pages.css">
  <link rel="stylesheet" href="home.css">
{extra_head}</head>
<body data-page="{page_key}"{body_class}>
  <svg class="svg-sprite" aria-hidden="true">{SPRITE}  </svg>

  <a class="skip-link" href="#main-content">رفتن به محتوای اصلی</a>

{header(active)}
{content}
{FOOTER}
{LAYERS}
  <script src="app.js"></script>
  <script src="pages.js"></script>
</body>
</html>
"""
    with open(filename, "w", encoding="utf-8") as handler:
        handler.write(html)
    print("✔", filename, len(html), "bytes")


# ==========================================================================
#  فروشگاه
# ==========================================================================
SHOP_CONTENT = """  <main class="page-main" id="main-content">
    <div class="page-container">
      <nav class="breadcrumb" aria-label="مسیر صفحه">
        <a href="index.html">خانه</a><svg><use href="#i-chevron-left"/></svg><span class="current">فروشگاه</span>
      </nav>

      <header class="page-head with-visual">
        <div>
          <h1>فروشگاه رنگ صادقی</h1>
          <p>مجموعه‌ای منتخب از رنگ‌های ساختمانی و صنعتی، مواد پتینه و میکروسمنت، رنگ‌های هنری، رزین و ابزارآلات حرفه‌ای؛ همه در یک فروشگاه با ضمانت اصالت کالا.</p>
          <div class="head-meta">
            <span><svg><use href="#i-shield"/></svg>ضمانت اصالت کالا</span>
            <span><svg><use href="#i-truck"/></svg>ارسال به سراسر کشور</span>
            <span><svg><use href="#i-headset"/></svg>مشاوره تخصصی خرید</span>
          </div>
        </div>
        <img src="assets/images/blog/tools.webp" alt="ابزار و محصولات فروشگاه رنگ صادقی">
      </header>

      <div class="shop-layout">
        <aside class="shop-sidebar" aria-label="فیلتر محصولات">
          <div class="filter-card">
            <h3>دسته بندی محصولات <svg><use href="#i-layers"/></svg></h3>
            <div class="filter-list" data-filter-list="cats"></div>
          </div>
          <div class="filter-card">
            <h3>برند <svg><use href="#i-award"/></svg></h3>
            <div class="filter-list" data-filter-list="brands"></div>
          </div>
          <div class="filter-card">
            <h3>محدوده قیمت (تومان) <svg><use href="#i-tag"/></svg></h3>
            <div class="price-inputs">
              <div><label for="filter-min">از</label><input id="filter-min" inputmode="numeric" placeholder="۰"></div>
              <div><label for="filter-max">تا</label><input id="filter-max" inputmode="numeric" placeholder="۵.۰۰۰.۰۰۰"></div>
            </div>
            <p class="filter-note">قیمت‌ها به تومان و بدون احتساب هزینه ارسال است.</p>
          </div>
          <div class="filter-card">
            <h3>سایر فیلترها <svg><use href="#i-filter"/></svg></h3>
            <div class="filter-list">
              <label class="filter-option"><input type="checkbox" id="filter-sale"><span>فقط کالاهای تخفیف‌دار</span></label>
              <label class="filter-option"><input type="checkbox" id="filter-stock"><span>فقط کالاهای موجود</span></label>
            </div>
          </div>
          <a class="filter-side-banner" href="shop.html?cat=patina" aria-label="میکروسمنت کارن">
            <img src="assets/images/blog/microcement.webp" alt="میکروسمنت">
          </a>
        </aside>

        <div class="shop-main">
          <div class="shop-toolbar">
            <span class="result-count" id="result-count"></span>
            <button class="btn btn-soft btn-sm mobile-filter-button" type="button" data-open-filters><svg><use href="#i-filter"/></svg>فیلترها</button>
            <span class="toolbar-spacer"></span>
            <label class="sort-select"><span>مرتب‌سازی:</span>
              <select id="sort-select">
                <option value="default">پیش‌فرض</option>
                <option value="newest">جدیدترین</option>
                <option value="popular">محبوب‌ترین</option>
                <option value="bestseller">پرفروش‌ترین</option>
                <option value="discount">تخفیف‌دار</option>
                <option value="cheap">ارزان‌ترین</option>
                <option value="expensive">گران‌ترین</option>
                <option value="name">نام (الفبایی)</option>
              </select>
            </label>
            <div class="view-toggles" role="group" aria-label="نوع نمایش">
              <button class="active" type="button" data-view="grid" aria-label="نمایش شبکه‌ای"><svg><use href="#i-grid"/></svg></button>
              <button type="button" data-view="list" aria-label="نمایش لیستی"><svg><use href="#i-list"/></svg></button>
            </div>
          </div>

          <div class="active-filters" id="active-filters"></div>
          <div class="shop-grid" id="shop-grid"></div>
          <nav class="pagination" id="shop-pagination" aria-label="صفحه‌بندی محصولات"></nav>
        </div>
      </div>

      <section class="page-section">
        <div class="section-head">
          <div><h2>خرید بر اساس دسته‌بندی</h2><p>سریع‌تر به محصول مورد نظرتان برسید</p></div>
          <a class="section-link" href="categories.html">همه دسته‌بندی‌ها <svg><use href="#i-chevron-left"/></svg></a>
        </div>
        <div class="subcat-tiles">
          <a class="subcat-tile" href="shop.html?cat=patina"><img src="assets/images/popular-micro.webp" alt=""><div><strong>میکروسمنت</strong><span data-cat-count="patina"></span></div></a>
          <a class="subcat-tile" href="shop.html?cat=patina"><img src="assets/images/best-ceramic.webp" alt=""><div><strong>تکسچر</strong><span data-cat-count="patina"></span></div></a>
          <a class="subcat-tile" href="shop.html?cat=art"><img src="assets/images/popular-acrylic.webp" alt=""><div><strong>رنگ اکریلیک</strong><span data-cat-count="art"></span></div></a>
          <a class="subcat-tile" href="shop.html?cat=art"><img src="assets/images/popular-resin.webp" alt=""><div><strong>رزین اپوکسی</strong><span data-cat-count="art"></span></div></a>
          <a class="subcat-tile" href="shop.html?cat=tools"><img src="assets/images/popular-brush.webp" alt=""><div><strong>قلم‌مو</strong><span data-cat-count="tools"></span></div></a>
          <a class="subcat-tile" href="shop.html?cat=tools"><img src="assets/images/popular-roller.webp" alt=""><div><strong>غلطک</strong><span data-cat-count="tools"></span></div></a>
          <a class="subcat-tile" href="shop.html?cat=stencil"><img src="assets/images/cat-stencil.webp" alt=""><div><strong>شابلون</strong><span data-cat-count="stencil"></span></div></a>
          <a class="subcat-tile" href="shop.html?cat=building"><img src="assets/images/cat-building.webp" alt=""><div><strong>ساختمانی</strong><span data-cat-count="building"></span></div></a>
        </div>
      </section>

      <section class="page-section">
        <div class="cta-band">
          <div>
            <h2>در انتخاب محصول مطمئن نیستید؟</h2>
            <p>کارشناسان رنگ صادقی در انتخاب رنگ، متریال و ابزار کنار شما هستند؛ از ساعت ۸ صبح تا ۸ شب.</p>
          </div>
          <a class="btn" href="contact.html"><svg><use href="#i-headset"/></svg>مشاوره رایگان</a>
        </div>
      </section>
    </div>
  </main>
"""

# ==========================================================================
#  دسته بندی محصولات
# ==========================================================================
CATEGORY_DATA = [
    ("patina", "پتینه", "blog/patina.webp",
     "تکسچر، میکروسمنت، رنگ‌های دکوراتیو، ورق طلا و تثبیت‌کننده‌ها برای اجرای دیوارهای لوکس و یکپارچه.",
     ["تکسچرها", "میکروسمنت", "رنگ دکوراتیو", "ورق طلا", "تثبیت‌کننده", "افکت عنکبوتی"]),
    ("art", "بخش هنری", "blog/metallic.webp",
     "رنگ اکریلیک و روغن هنری، رزین اپوکسی، مواد سفالگری، افکت و وارنیش برای هنرمندان و هنرجویان.",
     ["رنگ اکریلیک", "رنگ روغن", "رزین اپوکسی", "سفالگری", "افکت", "وارنیش"]),
    ("tools", "ابزارها", "blog/tools.webp",
     "غلطک، قلم‌مو، لیسه و کاردک، ماله، سنباده و ابزارهای برقی حرفه‌ای برای اجرای بی‌نقص.",
     ["غلطک", "قلم‌مو", "لیسه و کاردک", "ماله", "سنباده", "ابزار برقی"]),
    ("stencil", "شابلون و تابلو", "cat-stencil-photo.webp",
     "شابلون ماندالا، داماسک و کالیگرافی، بوم نقاشی و بیس‌های چوبی با طرح‌های اختصاصی.",
     ["شابلون", "ماندالا", "داماسک", "کالیگرافی", "بوم", "بیس چوبی"]),
    ("building", "ساختمانی و صنعتی", "cat-building-photo.webp",
     "رنگ ساختمانی و صنعتی، تینر و حلال، چسب کاغذی، بتونه و آستری برای پروژه‌های اجرایی.",
     ["رنگ ساختمانی", "رنگ صنعتی", "تینر", "چسب", "بتونه", "آستری"]),
]


def categories_content() -> str:
    cards = []
    for slug, title, image, desc, children in CATEGORY_DATA:
        subs = "".join(f'<a href="shop.html?cat={slug}">{child}</a>' for child in children)
        cards.append(f"""          <article class="cat-card">
            <div class="cat-card-media">
              <img src="assets/images/{image}" alt="{title}" loading="lazy">
              <span class="cat-count" data-cat-count="{slug}"></span>
            </div>
            <div class="cat-card-body">
              <h2><a href="shop.html?cat={slug}">{title} <svg width="16" height="16"><use href="#i-chevron-left"/></svg></a></h2>
              <p>{desc}</p>
              <div class="cat-sub-list">{subs}</div>
              <div class="cat-card-foot">
                <span>ارسال سریع • ضمانت اصالت</span>
                <a class="text-link" href="shop.html?cat={slug}">مشاهده محصولات <svg><use href="#i-chevron-left"/></svg></a>
              </div>
            </div>
          </article>""")
    return f"""  <main class="page-main" id="main-content">
    <div class="page-container">
      <nav class="breadcrumb" aria-label="مسیر صفحه">
        <a href="index.html">خانه</a><svg><use href="#i-chevron-left"/></svg><span class="current">دسته بندی محصولات</span>
      </nav>

      <header class="page-head">
        <h1>دسته بندی محصولات</h1>
        <p>محصولات فروشگاه رنگ صادقی در پنج گروه اصلی دسته‌بندی شده‌اند؛ برای دیدن زیرشاخه‌ها و محصولات هر گروه، روی دسته‌بندی مورد نظر کلیک کنید.</p>
        <div class="head-meta">
          <span><svg><use href="#i-layers"/></svg>۵ گروه اصلی</span>
          <span><svg><use href="#i-grid"/></svg>بیش از ۳۰ زیرشاخه</span>
          <span><svg><use href="#i-check-circle"/></svg>برندهای معتبر</span>
        </div>
      </header>

      <div class="cat-grid">
{chr(10).join(cards)}
      </div>

      <section class="page-section">
        <div class="section-head">
          <div><h2>محبوب‌ترین دسته‌بندی‌ها</h2><p>پرجستجوترین گروه‌های محصول در رنگ صادقی</p></div>
        </div>
        <div class="subcat-tiles">
          <a class="subcat-tile" href="shop.html?cat=patina"><img src="assets/images/popular-micro.webp" alt=""><div><strong>میکروسمنت</strong><span>دیوار و کف یکپارچه</span></div></a>
          <a class="subcat-tile" href="shop.html?cat=patina"><img src="assets/images/best-ceramic.webp" alt=""><div><strong>تکسچر سرامیک</strong><span>پایه پتینه‌کاری</span></div></a>
          <a class="subcat-tile" href="shop.html?cat=patina"><img src="assets/images/sale-spider-gold.webp" alt=""><div><strong>تار عنکبوتی</strong><span>افکت متالیک</span></div></a>
          <a class="subcat-tile" href="shop.html?cat=tools"><img src="assets/images/popular-male.webp" alt=""><div><strong>ماله و کاردک</strong><span>ابزار اجرا</span></div></a>
          <a class="subcat-tile" href="shop.html?cat=tools"><img src="assets/images/best-roller.webp" alt=""><div><strong>غلطک</strong><span>پوشش یکدست</span></div></a>
          <a class="subcat-tile" href="shop.html?cat=art"><img src="assets/images/popular-oil.webp" alt=""><div><strong>رنگ روغن</strong><span>نقاشی هنری</span></div></a>
          <a class="subcat-tile" href="shop.html?cat=art"><img src="assets/images/popular-resin.webp" alt=""><div><strong>رزین اپوکسی</strong><span>کارهای هنری</span></div></a>
          <a class="subcat-tile" href="shop.html?cat=stencil"><img src="assets/images/new-canvas-100.webp" alt=""><div><strong>بوم نقاشی</strong><span>تابلو دکوراتیو</span></div></a>
        </div>
      </section>

      <section class="page-section">
        <div class="cta-band">
          <div>
            <h2>دسته‌بندی مورد نظرتان را پیدا نکردید؟</h2>
            <p>کافیست نام محصول یا برند را برای ما بفرستید؛ تیم پشتیبانی سریع‌ترین مسیر را پیشنهاد می‌دهد.</p>
          </div>
          <a class="btn" href="contact.html"><svg><use href="#i-phone"/></svg>تماس با پشتیبانی</a>
        </div>
      </section>
    </div>
  </main>
"""


# ==========================================================================
#  تک صفحه محصول
# ==========================================================================
PRODUCT_CONTENT = """  <main class="page-main" id="main-content">
    <div class="page-container">
      <nav class="breadcrumb" aria-label="مسیر صفحه">
        <a href="index.html">خانه</a><svg><use href="#i-chevron-left"/></svg>
        <a href="shop.html">فروشگاه</a><svg><use href="#i-chevron-left"/></svg>
        <a href="shop.html?cat=patina" id="product-breadcrumb-cat">رنگ‌های دکوراتیو، عنکبوتی</a><svg><use href="#i-chevron-left"/></svg>
        <span class="current" id="product-breadcrumb-current">تار عنکبوتی ۵۰۰ گرمی گلد ساندورا</span>
      </nav>

      <div class="product-layout">
        <div class="gallery">
          <div class="gallery-main" id="gallery-main">
            <img src="assets/images/sale-spider-gold.webp" alt="تار عنکبوتی ۵۰۰ گرمی گلد ساندورا">
            <span class="gallery-badge"><svg><use href="#i-shield"/></svg>اصالت کالا</span>
            <button class="gallery-zoom" type="button" aria-label="بزرگ‌نمایی تصویر"><svg><use href="#i-zoom"/></svg></button>
          </div>
          <div class="gallery-thumbs" id="gallery-thumbs" aria-label="گالری تصاویر محصول"></div>
        </div>

        <div class="product-summary">
          <div class="brand-line">
            <span>برند: <a href="shop.html?cat=patina" id="product-brand">ساندورا</a></span>
            <span class="dot"></span>
            <span>کد کالا: <bdo id="product-sku">RS-1002</bdo></span>
          </div>

          <h1 class="product-h1" id="product-title">تار عنکبوتی ۵۰۰ گرمی گلد ساندورا</h1>

          <div class="rating-row">
            <span class="stars" id="product-rating">★★★★★</span>
            <span class="rate-value"><bdo id="product-rate-value">۵</bdo> از ۵</span>
            <a href="#tab-reviews" id="product-reviews">(۲۶ دیدگاه)</a>
            <span aria-hidden="true">•</span>
            <span>بیش از ۱۳۰ فروش موفق</span>
          </div>

          <div class="price-block">
            <span class="now"><bdo id="product-price">1.220.000</bdo><small>تومان</small></span>
            <span class="was" id="product-old">1.848.000 تومان</span>
            <span class="off" id="product-off">-۳۴%</span>
            <span class="stock" id="product-stock"><svg><use href="#i-check"/></svg> موجود در انبار</span>
          </div>

          <p class="short-desc" id="product-short">رنگ دکوراتیو تار عنکبوتی با افکت متالیک طلایی؛ پوشش‌دهی بالا و اجرای آسان روی دیوار.</p>

          <div class="variant-group">
            <label>وزن / بسته‌بندی</label>
            <div class="variant-options" id="variant-options">
              <button type="button" class="active" data-factor="1">۵۰۰ گرم</button>
              <button type="button" data-factor="1.8">۱ کیلوگرم</button>
              <button type="button" data-factor="8">بسته پیمانکاری</button>
            </div>
          </div>

          <div class="variant-group">
            <label>رنگ</label>
            <div class="color-options">
              <button type="button" class="active" style="background:#c9a24a" aria-label="طلایی"></button>
              <button type="button" style="background:#c9c9c9" aria-label="نقره‌ای"></button>
              <button type="button" style="background:#6b4a7a" aria-label="ته بنفش"></button>
              <button type="button" style="background:#2f5f7a" aria-label="ته آبی"></button>
            </div>
          </div>

          <div class="buy-row">
            <div class="qty-stepper">
              <button type="button" id="qty-minus" aria-label="کاهش تعداد"><svg><use href="#i-minus"/></svg></button>
              <input id="qty-input" value="۱" inputmode="numeric" aria-label="تعداد" readonly>
              <button type="button" id="qty-plus" aria-label="افزایش تعداد"><svg><use href="#i-plus"/></svg></button>
            </div>
            <button class="btn btn-primary" type="button" id="product-add"><svg><use href="#i-bag"/></svg>افزودن به سبد خرید</button>
            <button class="icon-action" type="button" data-wish="p2" aria-label="افزودن به علاقه‌مندی"><svg><use href="#i-heart"/></svg></button>
          </div>

          <div class="product-trust">
            <div><svg><use href="#i-truck"/></svg><span>ارسال سریع به سراسر کشور</span></div>
            <div><svg><use href="#i-shield"/></svg><span>تضمین اصالت و کیفیت</span></div>
            <div><svg><use href="#i-refresh"/></svg><span>۷ روز ضمانت بازگشت</span></div>
          </div>

          <ul class="product-meta">
            <li><b>دسته:</b> <a href="shop.html?cat=patina">پتینه، رنگ‌های دکوراتیو</a></li>
            <li><b>برند:</b> <span id="product-brand-2">ساندورا</span></li>
            <li><b>ارسال از:</b> انبار آمل، مازندران</li>
            <li><b>گارانتی:</b> اصالت کالا</li>
          </ul>
        </div>
      </div>

      <section class="product-tabs">
        <div class="tab-nav" role="tablist">
          <button type="button" class="active" data-tab="tab-description" role="tab">توضیحات</button>
          <button type="button" data-tab="tab-specs" role="tab">مشخصات فنی</button>
          <button type="button" data-tab="tab-reviews" role="tab" id="tab-reviews"><span id="product-tab-reviews">نظرات (۲۶)</span></button>
          <button type="button" data-tab="tab-shipping" role="tab">ارسال و مرجوعی</button>
        </div>

        <div class="tab-panel active" id="tab-description">
          <h3>درباره این محصول</h3>
          <p id="product-desc">رنگ دکوراتیو تار عنکبوتی ساندورا با ایجاد بافتی موج‌دار و درخششی متالیک، به دیوار عمق و حرکت می‌دهد. این محصول با قلم‌مو، غلطک یا کاردک اجرا می‌شود و برای دیوار نشیمن، لابی، کافه و فضاهای تجاری انتخاب مناسبی است.</p>
          <p>پوشش‌دهی بالای این رنگ باعث می‌شود با مقدار کمی محصول، سطح وسیعی اجرا شود؛ همچنین خشک‌شدن یکنواخت آن اجازه می‌دهد لایه‌های بعدی بدون ایجاد لک روی سطح قرار بگیرند.</p>
          <h3>نکات اجرا</h3>
          <ul>
            <li>سطح دیوار باید کاملاً تمیز، خشک و بدون گرد و غبار باشد.</li>
            <li>برای پوشش بهتر، ابتدا یک لایه پرایمر یا تکسچر پایه اجرا کنید.</li>
            <li>ابزار پیشنهادی: کاردک استیل، اسفنج مصنوعی دونه‌ریز و غلطک فومی.</li>
            <li>دمای مناسب اجرا بین ۱۵ تا ۳۰ درجه سانتی‌گراد است.</li>
          </ul>
        </div>

        <div class="tab-panel" id="tab-specs">
          <h3>مشخصات فنی</h3>
          <table class="spec-table"><tbody id="spec-rows"></tbody></table>
        </div>

        <div class="tab-panel" id="tab-reviews">
          <h3>دیدگاه خریداران</h3>
          <div class="review-summary">
            <div class="review-score"><b>۴.۸</b><span>از ۲۶ دیدگاه</span><div class="stars">★★★★★</div></div>
            <div class="bars">
              <span class="bar"><span>۵</span><i><b style="width:82%"></b></i><span>۲۱</span></span>
              <span class="bar"><span>۴</span><i><b style="width:12%"></b></i><span>۴</span></span>
              <span class="bar"><span>۳</span><i><b style="width:4%"></b></i><span>۱</span></span>
              <span class="bar"><span>۲</span><i><b style="width:0"></b></i><span>۰</span></span>
              <span class="bar"><span>۱</span><i><b style="width:0"></b></i><span>۰</span></span>
            </div>
          </div>
          <div class="review">
            <span class="avatar">م</span>
            <div>
              <div class="review-head"><strong>مهدی رضایی</strong><span>۱۸ مهر ۱۴۰۵</span><span class="stars">★★★★★</span></div>
              <p>رنگ خیلی خوبی بود، افکتش دقیقاً مثل نمونه عکس درومد. مقدارش هم برای دیوار ۶ متری کافی بود.</p>
            </div>
          </div>
          <div class="review">
            <span class="avatar">س</span>
            <div>
              <div class="review-head"><strong>سارا محمدی</strong><span>۰۲ مهر ۱۴۰۵</span><span class="stars">★★★★★</span></div>
              <p>بسته‌بندی سالم رسید و پشتیبانی هم راهنمایی کامل برای اجرا داد. خرید بعدی هم از همینجا انجام می‌دهم.</p>
            </div>
          </div>
          <div class="review">
            <span class="avatar">ح</span>
            <div>
              <div class="review-head"><strong>حسین کریمی</strong><span>۲۵ شهریور ۱۴۰۵</span><span class="stars">★★★★☆</span></div>
              <p>کیفیت رنگ عالی، فقط برای متراژ بالا باید مقدار بیشتری سفارش داد.</p>
            </div>
          </div>
        </div>

        <div class="tab-panel" id="tab-shipping">
          <h3>ارسال و مرجوعی</h3>
          <p>سفارش‌ها در روزهای کاری بین ۲۴ تا ۷۲ ساعت آماده و از انبار آمل ارسال می‌شوند. برای شهرهای مازندران امکان ارسال با پیک در همان روز وجود دارد.</p>
          <ul>
            <li>ارسال به سراسر کشور با پست پیشتاز و باربری.</li>
            <li>هزینه ارسال برای سفارش‌های بالای ۱۰ میلیون تومان رایگان است.</li>
            <li>امکان بازگشت کالای سالم و پلمب‌نشده تا ۷ روز پس از تحویل.</li>
          </ul>
          <div class="note-box"><svg><use href="#i-info"/></svg><p>در صورت آسیب‌دیدگی ظاهری بسته، از تحویل خودداری کنید و با پشتیبانی <b>09113294688</b> تماس بگیرید.</p></div>
        </div>
      </section>

      <section class="product-panel related-rail">
        <div class="panel-heading">
          <h2>محصولات مرتبط</h2>
          <a href="shop.html?cat=patina">مشاهده همه</a>
        </div>
        <div class="carousel-wrap">
          <div class="product-track" id="related-products"></div>
          <button class="carousel-arrow carousel-prev" data-carousel="related-products" data-dir="prev" aria-label="قبلی"><svg><use href="#i-chevron-right"/></svg></button>
          <button class="carousel-arrow carousel-next" data-carousel="related-products" data-dir="next" aria-label="بعدی"><svg><use href="#i-chevron-left"/></svg></button>
        </div>
      </section>
    </div>
  </main>

  <div class="sticky-buy mobile-only">
    <span class="price" id="sticky-price">1.220.000 تومان</span>
    <button class="btn btn-primary" type="button" id="sticky-add"><svg><use href="#i-bag"/></svg>افزودن به سبد</button>
  </div>
"""

# ==========================================================================
#  وبلاگ
# ==========================================================================
POSTS = [
    dict(slug="what-is-patina", title="پتینه چیست؟ انواع، کاربرد و تفاوت با رنگ دیوار | رنگ صادقی",
         excerpt="پتینه‌کاری هنر تغییر چهره دیوارهاست؛ در این مقاله انواع پتینه، کاربرد هرکدام و تفاوت‌هایش با رنگ معمولی دیوار را بررسی می‌کنیم.",
         cat="پتینه", tags="patina", image="patina.webp", date="۱۰ آبان ۱۴۰۵", read="۹ دقیقه", author="تیم محتوای رنگ صادقی", featured=True),
    dict(slug="metallic-acrylic-paint", title="رنگ اکریلیک متالیک چیست و چطور از آن برای دیوار و تابلو استفاده کنیم؟",
         excerpt="درخشش فلزی، سطوح لوکس می‌سازد؛ راهنمای کامل انتخاب، ترکیب و اجرای اکریلیک متالیک روی دیوار و تابلو.",
         cat="رنگ هنری", tags="art", image="metallic.webp", date="۰۷ مهر ۱۴۰۵", read="۷ دقیقه", author="تیم محتوای رنگ صادقی"),
    dict(slug="gold-leaf-patina", title="ورق طلا و نقره در پتینه‌کاری؛ از انتخاب ورق تا تکنیک آنتیک",
         excerpt="همه چیز درباره ورق طلا و نقره: چسب مخصوص، ابزار، روش اجرا و تکنیک آنتیک برای رسیدن به سطحی کلاسیک.",
         cat="آموزش", tags="patina", image="gold-leaf.webp", date="۲۴ شهریور ۱۴۰۵", read="۱۲ دقیقه", author="تیم محتوای رنگ صادقی"),
    dict(slug="buy-artist-oil-paint", title="خرید رنگ روغن هنری؛ تفاوت برندها و نکاتی که قبل از خرید باید بدانید",
         excerpt="پیگمنت، روغن و زمان خشک‌شدن؛ سه فاکتور کلیدی که کیفیت رنگ روغن هنری را تعیین می‌کنند.",
         cat="رنگ هنری", tags="art", image="oil-paint.webp", date="۱۴ شهریور ۱۴۰۵", read="۸ دقیقه", author="تیم محتوای رنگ صادقی"),
    dict(slug="velvet-powder-wall", title="پودر مخمل چیست؟ کاربرد، روش اجرا و تفاوت با رنگ مخملی",
         excerpt="دیوارهای نرم و لوکس با پودر مخمل؛ چه سطوحی مناسب است و چه ابزاری برای اجرا لازم دارید.",
         cat="پتینه", tags="patina", image="velvet.webp", date="۲۹ مرداد ۱۴۰۵", read="۱۰ دقیقه", author="تیم محتوای رنگ صادقی"),
    dict(slug="what-is-epoxy-resin", title="رزین اپوکسی چیست؟ انواع، نسبت ترکیب و کاربرد در هنر",
         excerpt="نسبت ترکیب، زمان کار، حباب‌گیری و ایمنی؛ هر آنچه برای شروع کار با رزین اپوکسی باید بدانید.",
         cat="رنگ هنری", tags="art", image="epoxy.webp", date="۱۷ مرداد ۱۴۰۵", read="۱۱ دقیقه", author="تیم محتوای رنگ صادقی"),
    dict(slug="microcement-guide", title="میکروسمنت چیست؟ راهنمای کامل اجرا، مزایا و مقایسه با سرامیک",
         excerpt="سطحی بدون درز، مقاوم و مدرن؛ میکروسمنت را با مراحل اجرا و نکات کلیدی‌اش بشناسید.",
         cat="آموزش", tags="patina", image="microcement.webp", date="۱۲ مرداد ۱۴۰۵", read="۱۳ دقیقه", author="تیم محتوای رنگ صادقی"),
    dict(slug="paint-roller-vs-brush", title="غلطک یا قلم‌مو؟ مقایسه کامل برای نقاشی ساختمان",
         excerpt="انتخاب ابزار درست، کیفیت نهایی کار را تعیین می‌کند؛ مقایسه غلطک و قلم‌مو در سطوح مختلف.",
         cat="ابزار", tags="tools", image="tools.webp", date="۰۸ تیر ۱۴۰۵", read="۶ دقیقه", author="تیم محتوای رنگ صادقی"),
]


def post_card(post, hidden=False) -> str:
    return f"""            <article class="post-card" data-tags="{post['tags']}">
              <div class="post-card-media">
                <a href="blog-single.html"><img src="assets/images/blog/{post['image']}" alt="{post['title']}" loading="lazy"></a>
                <span class="post-cat">{post['cat']}</span>
              </div>
              <div class="post-card-body">
                <div class="post-meta dark">
                  <span><svg><use href="#i-calendar"/></svg>{post['date']}</span>
                  <span><svg><use href="#i-clock"/></svg>{post['read']} مطالعه</span>
                </div>
                <h3><a href="blog-single.html">{post['title']}</a></h3>
                <p>{post['excerpt']}</p>
                <div class="post-card-foot">
                  <span>{post['author']}</span>
                  <a class="read" href="blog-single.html">ادامه مطلب <svg><use href="#i-chevron-left"/></svg></a>
                </div>
              </div>
            </article>"""


def blog_content() -> str:
    featured = POSTS[0]
    cards = "\n".join(post_card(post) for post in POSTS[1:])
    side_posts = "".join(f"""              <a class="side-post" href="blog-single.html">
                <img src="assets/images/blog/{post['image']}" alt="{post['title']}" loading="lazy">
                <div><strong>{post['title'][:52]}…</strong><span>{post['date']} • {post['read']} مطالعه</span></div>
              </a>""" for post in POSTS[1:5])
    return f"""  <main class="page-main" id="main-content">
    <div class="page-container">
      <nav class="breadcrumb" aria-label="مسیر صفحه">
        <a href="index.html">خانه</a><svg><use href="#i-chevron-left"/></svg><span class="current">وبلاگ</span>
      </nav>

      <div class="blog-top">
        <header class="page-head" style="margin-top:0">
          <h1>مجله رنگ صادقی</h1>
          <p>آموزش، بررسی و راهنمای خرید در دنیای رنگ، پتینه، میکروسمنت، رزین و ابزارهای نقاشی؛ نوشته‌شده توسط تیم فنی فروشگاه رنگ صادقی.</p>
          <div class="head-meta">
            <span><svg><use href="#i-book"/></svg>بیش از ۴۰ مقاله تخصصی</span>
            <span><svg><use href="#i-check-circle"/></svg>مطالب کارشناسی‌شده</span>
            <span><svg><use href="#i-clock"/></svg>به‌روزرسانی هفتگی</span>
          </div>
        </header>

        <article class="featured-post">
          <img src="assets/images/blog/{featured['image']}" alt="{featured['title']}" fetchpriority="high">
          <div class="featured-body">
            <span class="post-cat">مقاله ویژه • {featured['cat']}</span>
            <h2><a href="blog-single.html">{featured['title']}</a></h2>
            <p>{featured['excerpt']}</p>
            <div class="post-meta">
              <span><svg><use href="#i-calendar"/></svg>{featured['date']}</span>
              <span><svg><use href="#i-clock"/></svg>{featured['read']} مطالعه</span>
              <span><svg><use href="#i-user"/></svg>{featured['author']}</span>
            </div>
          </div>
        </article>

        <div class="blog-filterbar">
          <div class="chip-row">
            <button class="chip active" type="button" data-blog-filter="all">همه مقالات <b><span id="blog-result-count">۸</span></b></button>
            <button class="chip" type="button" data-blog-filter="patina">پتینه</button>
            <button class="chip" type="button" data-blog-filter="art">رنگ هنری</button>
            <button class="chip" type="button" data-blog-filter="tools">ابزار</button>
          </div>
          <div class="blog-search">
            <input id="blog-search-input" type="search" placeholder="جستجو در مقالات…" aria-label="جستجو در مقالات">
            <button type="button" aria-label="جستجو"><svg><use href="#i-search"/></svg></button>
          </div>
        </div>
      </div>

      <div class="blog-layout">
        <div>
          <div class="blog-grid" id="blog-grid">
{cards}
          </div>
          <div class="empty-state" id="blog-empty" style="display:none;margin-top:16px">
            <svg><use href="#i-search"/></svg>
            <h3>مقاله‌ای با این عبارت پیدا نشد</h3>
            <p>عبارت دیگری را جستجو کنید یا دسته‌بندی‌ها را تغییر دهید.</p>
          </div>
          <div class="load-more-wrap">
            <button class="btn btn-outline" type="button" id="load-more" data-limit="6">مشاهده مقالات بیشتر <svg><use href="#i-chevron-down"/></svg></button>
          </div>
        </div>

        <aside class="blog-side" aria-label="ستون کنار وبلاگ">
          <section class="side-card">
            <h3><svg><use href="#i-sparkles"/></svg>پرخواننده‌ترین مقالات</h3>
            <div class="side-posts">
{side_posts}
            </div>
          </section>

          <section class="side-card">
            <h3><svg><use href="#i-tag"/></svg>برچسب‌ها</h3>
            <div class="tag-cloud">
              <a href="blog.html">پتینه</a><a href="blog.html">میکروسمنت</a><a href="blog.html">رزین اپوکسی</a>
              <a href="blog.html">رنگ اکریلیک</a><a href="blog.html">ورق طلا</a><a href="blog.html">غلطک</a>
              <a href="blog.html">رنگ روغن</a><a href="blog.html">شابلون</a><a href="blog.html">پودر مخمل</a>
            </div>
          </section>

          <section class="side-cta">
            <h3>مشاوره خرید رایگان</h3>
            <p>نمی‌دانید برای پروژه‌تان چه محصولی مناسب است؟ کارشناسان ما راهنمایی می‌کنند.</p>
            <a class="btn" href="contact.html">دریافت مشاوره</a>
          </section>
        </aside>
      </div>

      <section class="newsletter">
        <div>
          <h2>تازه‌ترین مقالات را از دست ندهید</h2>
          <p>هر هفته یک ایمیل کوتاه با نکات اجرایی، معرفی محصولات جدید و پیشنهادهای ویژه فروشگاه.</p>
        </div>
        <form data-newsletter>
          <input type="email" placeholder="ایمیل خود را وارد کنید" aria-label="ایمیل" required>
          <button class="btn" type="submit">عضویت</button>
        </form>
      </section>
    </div>
  </main>
"""


# ==========================================================================
#  تک نوشته
# ==========================================================================
ARTICLE_CONTENT = """  <div class="read-progress" id="read-progress" aria-hidden="true"><span></span></div>
  <main class="page-main" id="main-content">
    <div class="page-container">
      <nav class="breadcrumb" aria-label="مسیر صفحه">
        <a href="index.html">خانه</a><svg><use href="#i-chevron-left"/></svg>
        <a href="blog.html">وبلاگ</a><svg><use href="#i-chevron-left"/></svg>
        <span class="current">پتینه چیست؟</span>
      </nav>

      <header class="article-head">
        <span class="post-cat">پتینه</span>
        <h1>پتینه چیست؟ انواع، کاربرد و تفاوت آن با رنگ دیوار</h1>
        <div class="post-meta">
          <span class="article-author"><img src="assets/images/blog/patina.webp" alt="">تیم محتوای رنگ صادقی</span>
          <span><svg><use href="#i-calendar"/></svg>۱۰ آبان ۱۴۰۵</span>
          <span><svg><use href="#i-clock"/></svg>۹ دقیقه مطالعه</span>
          <span><svg><use href="#i-eye"/></svg>۲٫۳ هزار بازدید</span>
        </div>
      </header>

      <figure class="article-cover">
        <img src="assets/images/blog/patina.webp" alt="اجرای پتینه روی دیوار با ماله استیل">
        <figcaption>پتینه‌کاری، سطحی زنده و چندلایه روی دیوار می‌سازد</figcaption>
      </figure>

      <div class="article-layout">
        <article class="article-card">
          <div class="article-body">
            <p>وقتی از «پتینه» حرف می‌زنیم، منظورمان یک رنگ ساده نیست؛ پتینه یک <strong>پوشش دکوراتیو چندلایه</strong> است که با ابزار و تکنیک‌های دستی اجرا می‌شود و به دیوار بافت، عمق و حس قدمت یا لوکس‌بودن می‌دهد. در این مقاله بررسی می‌کنیم پتینه دقیقاً چیست، چه انواعی دارد، کجا به کار می‌آید و چه تفاوتی با رنگ دیوار معمولی دارد.</p>

            <h2>پتینه چیست؟</h2>
            <p>پتینه (Patina) در لغت به معنای لایه‌ای است که در اثر گذر زمان روی سطح فلز یا سنگ ایجاد می‌شود؛ اما در دنیای دکوراسیون داخلی، پتینه به مجموعه‌ای از متریال و تکنیک‌ها گفته می‌شود که با آن ظاهری شبیه سنگ، فلز، چرم، ابر و بخار یا دیوارهای تاریخی روی سطوح مختلف اجرا می‌کنند.</p>
            <p>پایه‌ی بسیاری از اجراهای پتینه، <a href="shop.html?cat=patina">تکسچر</a> است؛ ماده‌ای خمیری‌شکل که با ماله، کاردک یا برس روی دیوار پخش می‌شود و بعد با رنگ‌های دکوراتیو، ورق طلا یا لاک افکت تکمیل می‌شود.</p>

            <blockquote>پتینه، دیوار را از یک سطح صاف و بی‌روح، به یک عنصر معماری تبدیل می‌کند؛ سطحی که با تغییر نور، رنگ عوض می‌کند.</blockquote>

            <h2>انواع پتینه</h2>
            <h3>۱. پتینه تکسچری (بافت‌دار)</h3>
            <p>با تکسچر سرامیک، فاین استون یا سند استون اجرا می‌شود و بافت سنگ، سیمان یا پارچه را شبیه‌سازی می‌کند. مناسب دیوار نشیمن، ستون و بخش‌های شاخص فضا.</p>
            <h3>۲. پتینه متالیک</h3>
            <p>با رنگ‌های دکوراتیو متالیک مانند تار عنکبوتی و مولتی سورفیس متالیک اجرا می‌شود و درخشش طلایی، نقره‌ای یا مسی ایجاد می‌کند. برای لابی، کافه و دیوار پشت تلویزیون بسیار پرطرفدار است.</p>
            <h3>۳. پتینه کلاسیک و آنتیک</h3>
            <p>با ورق طلا و نقره، لاک افکت و تکنیک‌های سایش، ظاهری قدیمی و کلاسیک به سطح می‌دهد؛ گزینه‌ای محبوب برای قاب آینه، ستون و مبلمان.</p>
            <h3>۴. میکروسمنت</h3>
            <p>پوششی یکپارچه و بدون درز که بیشتر برای کف و دیوار سرویس‌های بهداشتی و فضاهای مدرن استفاده می‌شود. درباره‌ی آن در مقاله‌ی <a href="blog-single.html">راهنمای کامل میکروسمنت</a> مفصل صحبت کرده‌ایم.</p>

            <h2>تفاوت پتینه با رنگ دیوار</h2>
            <table>
              <thead><tr><th>ویژگی</th><th>رنگ دیوار معمولی</th><th>پتینه</th></tr></thead>
              <tbody>
                <tr><td>تعداد لایه</td><td>۲ تا ۳ لایه یکنواخت</td><td>۳ تا ۶ لایه با تکنیک دستی</td></tr>
                <tr><td>بافت سطح</td><td>صاف و یکدست</td><td>بافت‌دار و چندلایه</td></tr>
                <tr><td>اجرا</td><td>غلطک و قلم‌مو</td><td>ماله، کاردک، برس، اسفنج</td></tr>
                <tr><td>هزینه</td><td>اقتصادی</td><td>بالاتر، به دلیل متریال و اجرا</td></tr>
                <tr><td>ماندگاری</td><td>۳ تا ۵ سال</td><td>۸ سال و بیشتر با لاک محافظ</td></tr>
                <tr><td>کاربرد</td><td>همه فضاها</td><td>دیوارهای شاخص و دکوراتیو</td></tr>
              </tbody>
            </table>

            <h2>مراحل اجرای پتینه</h2>
            <ol>
              <li><strong>آماده‌سازی سطح:</strong> تمیز کردن کامل دیوار، ترمیم ترک‌ها و اجرای آستری.</li>
              <li><strong>اجرای پایه:</strong> لایه اول تکسچر با ماله استیل و ضخامت یکنواخت.</li>
              <li><strong>ایجاد بافت:</strong> لایه دوم با حرکت‌های نامنظم یا اسفنج برای شکستن یکنواختی.</li>
              <li><strong>رنگ‌آمیزی:</strong> اجرای رنگ دکوراتیو با قلم‌مو یا برس برای عمق‌بخشی.</li>
              <li><strong>تثبیت:</strong> اجرای لاک یا وارنیش محافظ برای مقاوم‌سازی سطح.</li>
            </ol>

            <figure>
              <img src="assets/images/blog/microcement.webp" alt="نمونه سطح پتینه و میکروسمنت در فضای داخلی">
              <figcaption>ترکیب پتینه با میکروسمنت، فضایی مینیمال و یکپارچه می‌سازد</figcaption>
            </figure>

            <div class="note-box">
              <svg><use href="#i-info"/></svg>
              <p><b>نکته اجرایی:</b> پیش از خرید متریال، متراژ دقیق دیوار را حساب کنید. هر کیلو تکسچر بسته به بافت، بین ۴ تا ۶ متر مربع پوشش می‌دهد و کمبود متریال در میانه کار، تفاوت رنگ ایجاد می‌کند.</p>
            </div>

            <h2>ابزار و متریال لازم</h2>
            <ul>
              <li>ماله و کاردک استیل در اندازه‌های مختلف</li>
              <li>اسفنج مصنوعی دونه‌ریز و برس نرم</li>
              <li>تکسچر پایه (سرامیک، فاین استون یا سند استون)</li>
              <li>رنگ دکوراتیو یا متالیک</li>
              <li>لاک افکت یا تثبیت‌کننده نهایی</li>
            </ul>
            <p>همه‌ی این اقلام را می‌توانید در دسته‌بندی <a href="shop.html?cat=patina">پتینه</a> و <a href="shop.html?cat=tools">ابزارها</a> فروشگاه رنگ صادقی تهیه کنید.</p>

            <h2>جمع‌بندی</h2>
            <p>پتینه یک انتخاب دکوراتیو است، نه جایگزین رنگ ساختمانی. اگر به دنبال دیواری شاخص، ماندگار و متفاوت هستید و اجرای حرفه‌ای برایتان اهمیت دارد، پتینه یکی از بهترین گزینه‌های بازار است. برای انتخاب متریال مناسب، می‌توانید از مشاوره رایگان کارشناسان رنگ صادقی استفاده کنید.</p>
          </div>

          <div class="article-tags">
            <a href="blog.html">#پتینه</a><a href="blog.html">#تکسچر</a><a href="blog.html">#رنگ_دکوراتیو</a>
            <a href="blog.html">#میکروسمنت</a><a href="blog.html">#دکوراسیون_داخلی</a>
          </div>

          <div class="share-row">
            <span>اشتراک‌گذاری:</span>
            <a href="https://t.me/share/url?url=https://rangsadeghy.com/blog/" target="_blank" rel="noopener" aria-label="اشتراک در تلگرام"><svg><use href="#i-send"/></svg></a>
            <a href="https://wa.me/?text=https://rangsadeghy.com/blog/" target="_blank" rel="noopener" aria-label="اشتراک در واتساپ"><svg><use href="#i-whatsapp"/></svg></a>
            <button type="button" data-copy-link aria-label="کپی لینک"><svg><use href="#i-link"/></svg></button>
          </div>

          <div class="author-box">
            <img src="assets/images/blog/patina.webp" alt="تیم محتوای رنگ صادقی">
            <div>
              <h3>تیم محتوای رنگ صادقی</h3>
              <p>محتوای تخصصی این مجله با همکاری کارشناسان فنی فروشگاه رنگ صادقی و بر اساس تجربه اجرای پروژه‌های رنگ و پتینه تهیه می‌شود.</p>
            </div>
          </div>

          <div class="post-nav">
            <a href="blog-single.html"><small>مقاله قبلی</small>رنگ اکریلیک متالیک چیست و چطور اجرا می‌شود؟</a>
            <a href="blog-single.html"><small>مقاله بعدی</small>ورق طلا و نقره در پتینه‌کاری؛ راهنمای کامل</a>
          </div>

          <section class="comments">
            <h2 style="font-size:18px;margin:22px 0 10px">دیدگاه‌ها (۳)</h2>
            <div class="comment"><span class="avatar">ن</span><div><strong>نسترن احمدی</strong><span>۱۲ آبان ۱۴۰۵</span><p>خیلی کامل توضیح داده بودید. برای دیوار ۱۲ متری چه مقدار تکسچر لازم دارم؟</p></div></div>
            <div class="comment"><span class="avatar">ر</span><div><strong>رضا شریفی</strong><span>۱۱ آبان ۱۴۰۵</span><p>بعد از خواندن این مقاله تصمیم گرفتم دیوار پذیرایی را پتینه کنم؛ نتیجه واقعاً عالی شد.</p></div></div>
            <div class="comment"><span class="avatar">م</span><div><strong>مینا کاظمی</strong><span>۱۰ آبان ۱۴۰۵</span><p>تفاوت پتینه و میکروسمنت همیشه برایم گنگ بود، ممنون از جدول مقایسه.</p></div></div>

            <form class="comment-form" style="margin-top:18px" onsubmit="event.preventDefault();window.RS.showToast('دیدگاه شما ثبت شد (نمایشی)');this.reset();">
              <div class="form-grid">
                <div class="field"><label for="c-name">نام <span>*</span></label><input id="c-name" required placeholder="نام شما"></div>
                <div class="field"><label for="c-mail">ایمیل <span>*</span></label><input id="c-mail" type="email" required placeholder="you@example.com"></div>
                <div class="field full"><label for="c-text">دیدگاه شما <span>*</span></label><textarea id="c-text" required placeholder="دیدگاه خود را بنویسید…"></textarea></div>
              </div>
              <button class="btn btn-primary" type="submit" style="margin-top:12px">ارسال دیدگاه</button>
            </form>
          </section>
        </article>

        <aside class="article-side" aria-label="ابزارهای مقاله">
          <section class="side-card">
            <h3><svg><use href="#i-list"/></svg>فهرست مطالب</h3>
            <ul class="toc" id="article-toc"></ul>
          </section>

          <section class="side-cta">
            <h3>خرید متریال پتینه</h3>
            <p>تکسچر، رنگ دکوراتیو، ورق طلا و ابزار اجرا با ضمانت اصالت کالا.</p>
            <a class="btn" href="shop.html?cat=patina">مشاهده محصولات</a>
          </section>

          <section class="side-card">
            <h3><svg><use href="#i-sparkles"/></svg>مقالات مرتبط</h3>
            <div class="side-posts">
              <a class="side-post" href="blog-single.html"><img src="assets/images/blog/microcement.webp" alt=""><div><strong>میکروسمنت چیست؟ راهنمای کامل اجرا</strong><span>۱۲ مرداد ۱۴۰۵</span></div></a>
              <a class="side-post" href="blog-single.html"><img src="assets/images/blog/gold-leaf.webp" alt=""><div><strong>ورق طلا و نقره در پتینه‌کاری</strong><span>۲۴ شهریور ۱۴۰۵</span></div></a>
              <a class="side-post" href="blog-single.html"><img src="assets/images/blog/velvet.webp" alt=""><div><strong>پودر مخمل چیست و چگونه اجرا می‌شود؟</strong><span>۲۹ مرداد ۱۴۰۵</span></div></a>
            </div>
          </section>
        </aside>
      </div>

      <section class="page-section">
        <div class="section-head">
          <div><h2>مقالات پیشنهادی</h2><p>مطالب مرتبط با رنگ، پتینه و ابزار</p></div>
          <a class="section-link" href="blog.html">همه مقالات <svg><use href="#i-chevron-left"/></svg></a>
        </div>
        <div class="blog-grid">
          <article class="post-card">
            <div class="post-card-media"><a href="blog-single.html"><img src="assets/images/blog/metallic.webp" alt="رنگ اکریلیک متالیک" loading="lazy"></a><span class="post-cat">رنگ هنری</span></div>
            <div class="post-card-body"><div class="post-meta dark"><span><svg><use href="#i-calendar"/></svg>۰۷ مهر ۱۴۰۵</span><span><svg><use href="#i-clock"/></svg>۷ دقیقه</span></div><h3><a href="blog-single.html">رنگ اکریلیک متالیک چیست و چطور اجرا می‌شود؟</a></h3></div>
          </article>
          <article class="post-card">
            <div class="post-card-media"><a href="blog-single.html"><img src="assets/images/blog/microcement.webp" alt="میکروسمنت" loading="lazy"></a><span class="post-cat">آموزش</span></div>
            <div class="post-card-body"><div class="post-meta dark"><span><svg><use href="#i-calendar"/></svg>۱۲ مرداد ۱۴۰۵</span><span><svg><use href="#i-clock"/></svg>۱۳ دقیقه</span></div><h3><a href="blog-single.html">میکروسمنت چیست؟ راهنمای کامل اجرا، مزایا و مقایسه با سرامیک</a></h3></div>
          </article>
        </div>
      </section>
    </div>
  </main>
"""

# ==========================================================================
#  درباره ما
# ==========================================================================
ABOUT_CONTENT = """  <main class="page-main" id="main-content">
    <div class="page-container">
      <nav class="breadcrumb" aria-label="مسیر صفحه">
        <a href="index.html">خانه</a><svg><use href="#i-chevron-left"/></svg><span class="current">درباره ما</span>
      </nav>

      <section class="about-hero">
        <div>
          <span class="hero-badge"><svg><use href="#i-award"/></svg>بیش از ۲۰ برند معتبر</span>
          <h1>درباره رنگ صادقی؛ مرجع جامع رنگ، پتینه و ابزار</h1>
          <p class="lead">فروشگاه رنگ صادقی با تمرکز تخصصی بر رنگ ساختمانی، رنگ صنعتی، رنگ پتینه، هنری و ابزارآلات دستی و برقی حرفه‌ای فعالیت می‌کند. هدف ما ارائه‌ی مجموعه‌ای کامل از محصولات معتبر است تا مشتریان بتوانند خریدی امن، سریع و با اطلاعات دقیق را تجربه کنند.</p>
          <div class="about-cta">
            <a class="btn btn-primary" href="shop.html"><svg><use href="#i-bag"/></svg>مشاهده فروشگاه</a>
            <a class="btn btn-outline" href="contact.html"><svg><use href="#i-phone"/></svg>تماس با ما</a>
          </div>
        </div>
        <img src="assets/images/blog/patina.webp" alt="فضای فروشگاه و متریال رنگ صادقی">
      </section>

      <div class="stats">
        <div class="stat"><b><span data-count="20">۰</span><i>+</i></b><span>برند معتبر</span></div>
        <div class="stat"><b><span data-count="1500">۰</span><i>+</i></b><span>کالای متنوع</span></div>
        <div class="stat"><b><span data-count="12">۰</span><i>+</i></b><span>سال تجربه اجرا</span></div>
        <div class="stat"><b><span data-count="9000">۰</span><i>+</i></b><span>مشتری راضی</span></div>
      </div>

      <section class="about-story">
        <img src="assets/images/blog/tools.webp" alt="ابزار و متریال حرفه‌ای رنگ صادقی">
        <div>
          <h2>داستان ما</h2>
          <p>رنگ صادقی از یک فروشگاه تخصصی رنگ در آمل شروع شد؛ جایی که تمرکز اصلی بر تأمین رنگ ساختمانی و ابزار نقاشی بود. با رشد پروژه‌های دکوراتیو و ورود متریال‌های جدید مثل پتینه و میکروسمنت، این مسیر به سمت تکمیل سبد محصولات حرفه‌ای پیش رفت.</p>
          <p>امروز در کنار رنگ و ابزار، رنگ‌های هنری، رزین اپوکسی، شابلون، بوم و مواد سفالگری را نیز تأمین می‌کنیم؛ با این باور که یک پروژه خوب، پیش از هر چیز به <strong>متریال درست</strong> و <strong>مشاوره دقیق</strong> نیاز دارد.</p>
          <ul class="about-list">
            <li><svg><use href="#i-check-circle"/></svg><span>تأمین کالای اصل از برندهای شناخته‌شده داخلی و خارجی</span></li>
            <li><svg><use href="#i-check-circle"/></svg><span>مشاوره تخصصی پیش از خرید، بر اساس نوع سطح و شرایط اجرا</span></li>
            <li><svg><use href="#i-check-circle"/></svg><span>ارسال سریع به سراسر کشور از انبار آمل</span></li>
          </ul>
        </div>
      </section>

      <section class="page-section">
        <div class="section-head">
          <div><h2>چرا رنگ صادقی؟</h2><p>چهار اصلی که در هر سفارش رعایت می‌کنیم</p></div>
        </div>
        <div class="value-grid">
          <article class="value-card">
            <span class="value-icon"><svg><use href="#i-shield"/></svg></span>
            <h3>اصالت و کیفیت</h3>
            <p>همه‌ی محصولات با ضمانت اصالت عرضه می‌شوند و از مبادی رسمی تأمین می‌گردند.</p>
          </article>
          <article class="value-card">
            <span class="value-icon"><svg><use href="#i-headset"/></svg></span>
            <h3>مشاوره تخصصی</h3>
            <p>کارشناسان ما با تجربه اجرای پروژه، بهترین متریال را برای سطح شما پیشنهاد می‌دهند.</p>
          </article>
          <article class="value-card">
            <span class="value-icon"><svg><use href="#i-truck"/></svg></span>
            <h3>ارسال سریع</h3>
            <p>ارسال به سراسر کشور با پست پیشتاز و باربری؛ تحویل در مازندران با پیک.</p>
          </article>
          <article class="value-card">
            <span class="value-icon"><svg><use href="#i-layers"/></svg></span>
            <h3>تنوع بی‌نظیر</h3>
            <p>از رنگ ساختمانی و صنعتی تا پتینه، رزین، شابلون و ابزار حرفه‌ای در یک فروشگاه.</p>
          </article>
        </div>
      </section>

      <section class="page-section">
        <div class="section-head">
          <div><h2>گوشه‌ای از کارگاه و پروژه‌ها</h2><p>تصاویری از متریال و اجراهای انجام‌شده</p></div>
        </div>
        <div class="about-gallery">
          <a href="blog.html"><img src="assets/images/blog/patina.webp" alt="پتینه کاری" loading="lazy"></a>
          <a href="blog.html"><img src="assets/images/blog/metallic.webp" alt="رنگ متالیک" loading="lazy"></a>
          <a href="blog.html"><img src="assets/images/blog/microcement.webp" alt="میکروسمنت" loading="lazy"></a>
          <a href="blog.html"><img src="assets/images/blog/gold-leaf.webp" alt="ورق طلا" loading="lazy"></a>
          <a href="blog.html"><img src="assets/images/blog/tools.webp" alt="ابزار نقاشی" loading="lazy"></a>
          <a href="blog.html"><img src="assets/images/promo-resin.webp" alt="رزین" loading="lazy"></a>
        </div>
      </section>

      <section class="brand-strip">
        <h2>با بهترین برندها کار می‌کنیم</h2>
        <p>در رنگ صادقی تلاش می‌کنیم بهترین انتخاب را برای شما فراهم کنیم</p>
        <div class="brand-marquee">
          <div class="brand-track" id="brand-track"></div>
        </div>
      </section>

      <section class="page-section">
        <div class="cta-band">
          <div>
            <h2>پروژه‌ای در پیش دارید؟</h2>
            <p>برای انتخاب متریال، برآورد مقدار و قیمت، با کارشناسان ما در تماس باشید.</p>
          </div>
          <a class="btn" href="contact.html"><svg><use href="#i-send"/></svg>درخواست مشاوره</a>
        </div>
      </section>
    </div>
  </main>
"""

# ==========================================================================
#  تماس با ما
# ==========================================================================
CONTACT_CONTENT = """  <main class="page-main" id="main-content">
    <div class="page-container">
      <nav class="breadcrumb" aria-label="مسیر صفحه">
        <a href="index.html">خانه</a><svg><use href="#i-chevron-left"/></svg><span class="current">تماس با ما</span>
      </nav>

      <header class="page-head">
        <h1>تماس با ما</h1>
        <p>برای مشاوره خرید، پیگیری سفارش یا هماهنگی بازدید حضوری از فروشگاه، از راه‌های زیر با ما در ارتباط باشید. کارشناسان رنگ صادقی هر روز از ۶:۳۰ تا ۲۰:۳۰ پاسخگوی شما هستند.</p>
      </header>

      <div class="contact-cards">
        <article class="contact-card">
          <span class="ic"><svg><use href="#i-pin"/></svg></span>
          <h3>آدرس فروشگاه</h3>
          <p>مازندران، آمل، خیابان طالب آملی، دریای ۳۰/۱، پلاک ۴۴۴</p>
          <small>نبش دریا ۳۰/۱ — طبقه همکف</small>
        </article>
        <article class="contact-card">
          <span class="ic"><svg><use href="#i-clock"/></svg></span>
          <h3>ساعت کاری</h3>
          <p>شنبه تا پنج‌شنبه<br>از ساعت ۶:۳۰ تا ۲۰:۳۰</p>
          <small>جمعه‌ها تعطیل</small>
        </article>
        <article class="contact-card">
          <span class="ic"><svg><use href="#i-phone"/></svg></span>
          <h3>شماره‌های تماس</h3>
          <a href="tel:+989113294688">پشتیبانی: ۰۹۱۱۳۲۹۴۶۸۸</a>
          <small>پاسخگویی تلفنی و واتساپ</small>
        </article>
        <article class="contact-card">
          <span class="ic"><svg><use href="#i-mail"/></svg></span>
          <h3>ایمیل</h3>
          <a href="mailto:rangsadeghi@gmail.com">rangsadeghi@gmail.com</a>
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
              <input type="checkbox" required> می‌خواهم از تخفیف‌ها و محصولات جدید رنگ صادقی باخبر شوم.
            </label>
            <button class="btn btn-primary" type="submit"><svg><use href="#i-send"/></svg>ارسال پیام</button>
            <p class="form-note">اطلاعات شما فقط برای پاسخ به همین درخواست استفاده می‌شود و در اختیار شخص ثالث قرار نمی‌گیرد.</p>
          </form>
        </section>

        <aside class="contact-side">
          <div class="map-card">
            <div class="map-canvas" role="img" aria-label="نقشه موقعیت فروشگاه رنگ صادقی در آمل">
              <span class="map-label">فروشگاه رنگ صادقی</span>
              <span class="map-pin"><svg><use href="#i-pin"/></svg></span>
            </div>
            <div class="map-foot">
              <span>آمل، خیابان طالب آملی، نبش دریا ۳۰/۱</span>
              <a class="text-link" href="https://rangsadeghy.com/store-locations/" target="_blank" rel="noopener">مسیریابی <svg><use href="#i-chevron-left"/></svg></a>
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
              <a href="https://www.instagram.com/rangsadeghy" target="_blank" rel="noopener" aria-label="اینستاگرام"><svg><use href="#i-instagram"/></svg></a>
              <a href="https://wa.me/989113294688" target="_blank" rel="noopener" aria-label="واتساپ"><svg><use href="#i-whatsapp"/></svg></a>
              <a href="https://t.me/rangsadeghy" target="_blank" rel="noopener" aria-label="تلگرام"><svg><use href="#i-send"/></svg></a>
              <a href="mailto:rangsadeghi@gmail.com" aria-label="ایمیل"><svg><use href="#i-mail"/></svg></a>
            </div>
          </div>
        </aside>
      </div>

      <section class="page-section">
        <div class="section-head">
          <div><h2>پرسش‌های پرتکرار</h2><p>پاسخ سوال‌های رایج درباره خرید و ارسال</p></div>
        </div>
        <div class="faq-list">
          <details class="faq-item" open>
            <summary>آیا امکان خرید حضوری از فروشگاه وجود دارد؟<svg><use href="#i-chevron-down"/></svg></summary>
            <p>بله؛ فروشگاه رنگ صادقی در آمل، خیابان طالب آملی، نبش دریا ۳۰/۱ از شنبه تا پنج‌شنبه بین ساعت ۶:۳۰ تا ۲۰:۳۰ پذیرای شماست.</p>
          </details>
          <details class="faq-item">
            <summary>هزینه و زمان ارسال چقدر است؟<svg><use href="#i-chevron-down"/></svg></summary>
            <p>ارسال به سراسر کشور با پست پیشتاز و باربری انجام می‌شود؛ زمان تحویل بین ۲ تا ۵ روز کاری است. برای سفارش‌های بالای ۱۰ میلیون تومان ارسال رایگان است.</p>
          </details>
          <details class="faq-item">
            <summary>برای انتخاب رنگ یا متریال مشاوره می‌دهید؟<svg><use href="#i-chevron-down"/></svg></summary>
            <p>بله؛ کارشناسان ما بر اساس نوع سطح، متراژ و شرایط اجرا، متریال و مقدار مورد نیاز را پیشنهاد می‌دهند. کافیست از طریق واتساپ یا فرم همین صفحه با ما در تماس باشید.</p>
          </details>
          <details class="faq-item">
            <summary>امکان مرجوعی کالا وجود دارد؟<svg><use href="#i-chevron-down"/></svg></summary>
            <p>کالاهای سالم و پلمب‌نشده تا ۷ روز پس از تحویل قابل بازگشت هستند. برای هماهنگی مرجوعی، با پشتیبانی تماس بگیرید.</p>
          </details>
        </div>
      </section>
    </div>
  </main>
"""

# ==========================================================================
#  فروشگاه های ما
# ==========================================================================
STORES_CONTENT = """  <main class="page-main" id="main-content">
    <div class="page-container">
      <nav class="breadcrumb" aria-label="مسیر صفحه">
        <a href="index.html">خانه</a><svg><use href="#i-chevron-left"/></svg><span class="current">فروشگاه های ما</span>
      </nav>

      <header class="stores-intro">
        <h1>فروشگاه‌های ما</h1>
        <p>مجموعه‌ی رنگ صادقی در دو فروشگاه تخصصی فعالیت می‌کند؛ هر کدام با تمرکز بر بخش متفاوتی از دنیای رنگ و هنر. برای ورود به هر فروشگاه، روی دکمه مربوطه کلیک کنید.</p>
      </header>

      <div class="stores-grid">
        <article class="store-card">
          <div class="store-top">
            <span class="store-logo"><img src="assets/images/logo.png" alt="رنگ صادقی"></span>
            <div>
              <h2>فروشگاه رنگ صادقی</h2>
              <a href="https://rangsadeghy.com" target="_blank" rel="noopener">rangsadeghy.com</a>
            </div>
          </div>
          <p>مرجع جامع رنگ ساختمانی، صنعتی، پتینه، هنری و ابزارآلات حرفه‌ای؛ با بیش از ۲۰ برند معتبر و تمرکز بر مشاوره فنی برای پروژه‌های اجرایی و دکوراتیو.</p>
          <ul class="store-features">
            <li><svg><use href="#i-check-circle"/></svg><span>رنگ ساختمانی و صنعتی، تینر و چسب</span></li>
            <li><svg><use href="#i-check-circle"/></svg><span>پتینه، میکروسمنت، تکسچر و رنگ‌های دکوراتیو</span></li>
            <li><svg><use href="#i-check-circle"/></svg><span>رنگ هنری، رزین اپوکسی و مواد سفالگری</span></li>
            <li><svg><use href="#i-check-circle"/></svg><span>ابزار دستی و برقی: غلطک، قلم‌مو، ماله و کاردک</span></li>
          </ul>
          <div class="store-info">
            <div><svg><use href="#i-pin"/></svg><span>مازندران، آمل، خیابان طالب آملی، نبش دریا ۳۰/۱، پلاک ۴۴۴</span></div>
            <div><svg><use href="#i-clock"/></svg><span>شنبه تا پنج‌شنبه، ۶:۳۰ تا ۲۰:۳۰</span></div>
            <div><svg><use href="#i-phone"/></svg><span dir="ltr">۰۹۱۱۳۲۹۴۶۸۸</span></div>
          </div>
          <div class="store-actions">
            <a class="btn btn-primary" href="https://rangsadeghy.com" target="_blank" rel="noopener"><svg><use href="#i-globe"/></svg>ورود به فروشگاه</a>
            <a class="btn btn-outline" href="contact.html"><svg><use href="#i-map"/></svg>مسیریابی</a>
          </div>
        </article>

        <article class="store-card naghshi">
          <div class="store-top">
            <span class="store-logo store-logo-text" aria-hidden="true">نقشینو</span>
            <div>
              <h2>فروشگاه نقشینو</h2>
              <a href="https://naghshinoart.com" target="_blank" rel="noopener">naghshinoart.com</a>
            </div>
          </div>
          <p>فروشگاه تخصصی شابلون و تابلو؛ با تنوع بی‌نظیر شابلون در ابعاد و طرح‌های مختلف، حروف کالیگرافی و بوم‌های نقاشی برای خلق تابلوهای دکوراتیو.</p>
          <ul class="store-features">
            <li><svg><use href="#i-check-circle"/></svg><span>شابلون ماندالا، داماسک، طرح فرش، کاشی و آیات</span></li>
            <li><svg><use href="#i-check-circle"/></svg><span>حروف کالیگرافی در بیش از ۵۰ مدل و سایزبندی</span></li>
            <li><svg><use href="#i-check-circle"/></svg><span>بوم نقاشی برای همه سبک‌های هنری و دکوراتیو</span></li>
            <li><svg><use href="#i-check-circle"/></svg><span>طرح‌های اختصاصی و سفارشی‌سازی</span></li>
          </ul>
          <div class="store-info">
            <div><svg><use href="#i-globe"/></svg><span>فروش آنلاین و ارسال به سراسر کشور</span></div>
            <div><svg><use href="#i-clock"/></svg><span>پشتیبانی آنلاین در ساعات کاری</span></div>
            <div><svg><use href="#i-truck"/></svg><span>ارسال با بسته‌بندی محافظ برای شابلون و بوم</span></div>
          </div>
          <div class="store-actions">
            <a class="btn btn-primary" href="https://naghshinoart.com" target="_blank" rel="noopener"><svg><use href="#i-globe"/></svg>ورود به فروشگاه</a>
            <a class="btn btn-outline" href="shop.html?cat=stencil"><svg><use href="#i-grid"/></svg>دسته شابلون</a>
          </div>
        </article>
      </div>

      <section class="store-compare">
        <h2>مقایسه دو فروشگاه</h2>
        <table class="compare-table">
          <thead>
            <tr><th>ویژگی</th><th>رنگ صادقی (rangsadeghy.com)</th><th>نقشینو (naghshinoart.com)</th></tr>
          </thead>
          <tbody>
            <tr><td>تخصص اصلی</td><td>رنگ، پتینه، میکروسمنت و ابزار</td><td>شابلون، تابلو و بوم نقاشی</td></tr>
            <tr><td>رنگ ساختمانی و صنعتی</td><td>دارد</td><td>—</td></tr>
            <tr><td>متریال پتینه و دکوراتیو</td><td>دارد</td><td>محدود</td></tr>
            <tr><td>شابلون و حروف کالیگرافی</td><td>محدود</td><td>تنوع کامل</td></tr>
            <tr><td>بوم و بیس چوبی</td><td>دارد</td><td>تنوع کامل</td></tr>
            <tr><td>فروشگاه حضوری</td><td>آمل، مازندران</td><td>فروش آنلاین</td></tr>
            <tr><td>ارسال به سراسر کشور</td><td>دارد</td><td>دارد</td></tr>
          </tbody>
        </table>
      </section>

      <section class="map-wide">
        <div class="map-canvas" role="img" aria-label="نقشه فروشگاه حضوری رنگ صادقی">
          <span class="map-label">فروشگاه رنگ صادقی — آمل</span>
          <span class="map-pin"><svg><use href="#i-pin"/></svg></span>
        </div>
        <div class="map-foot">
          <span><strong>فروشگاه حضوری:</strong> مازندران، آمل، خیابان طالب آملی، دریای ۳۰/۱، پلاک ۴۴۴ — ۶:۳۰ تا ۲۰:۳۰</span>
          <a class="text-link" href="https://rangsadeghy.com/store-locations/" target="_blank" rel="noopener">مشاهده مسیر <svg><use href="#i-chevron-left"/></svg></a>
        </div>
      </section>

      <section class="page-section">
        <div class="cta-band">
          <div>
            <h2>کدام فروشگاه را انتخاب کنیم؟</h2>
            <p>برای رنگ، پتینه و ابزار سراغ رنگ صادقی بروید؛ برای شابلون، بوم و تابلو، نقشینو انتخاب شماست. در هر دو فروشگاه، پشتیبانی و ارسال یکسان است.</p>
          </div>
          <a class="btn" href="contact.html"><svg><use href="#i-headset"/></svg>مشاوره انتخاب</a>
        </div>
      </section>
    </div>
  </main>
"""


# ==========================================================================
#  سبد خرید
# ==========================================================================
CART_CONTENT = """  <main class="page-main" id="main-content">
    <div class="page-container">
      <nav class="breadcrumb" aria-label="مسیر صفحه">
        <a href="index.html">خانه</a><svg><use href="#i-chevron-left"/></svg>
        <a href="shop.html">فروشگاه</a><svg><use href="#i-chevron-left"/></svg>
        <span class="current">سبد خرید</span>
      </nav>

{CART_STEPS}

      <div class="cart-layout">
        <section class="cart-panel" aria-labelledby="cart-title">
          <header class="cart-panel-head">
            <h1 id="cart-title">سبد خرید <span id="cart-page-count"></span></h1>
            <button class="cart-clear" type="button" id="cart-page-clear"><svg><use href="#i-trash"/></svg>حذف همه کالاها</button>
          </header>

          <div class="free-ship" id="free-ship">
            <div class="free-ship-text"><svg><use href="#i-truck"/></svg><span id="free-ship-text">محاسبه هزینه ارسال…</span></div>
            <div class="free-ship-bar"><span id="free-ship-progress" style="width:0%"></span></div>
          </div>

          <div id="cart-page-items" class="cart-lines"></div>

          <div class="cart-panel-foot">
            <a class="btn btn-outline btn-sm" href="shop.html"><svg><use href="#i-chevron-right"/></svg>ادامه خرید</a>
            <span class="cart-panel-note"><svg><use href="#i-info"/></svg>قیمت‌ها به تومان و شامل مالیات است.</span>
          </div>
        </section>

        <aside class="cart-side" aria-label="خلاصه سفارش">
          <div class="summary-card">
            <h2>خلاصه سفارش</h2>
            <div class="summary-row"><span>مبلغ کالاها (<b id="summary-count">۰</b> کالا)</span><b id="summary-subtotal">۰ تومان</b></div>
            <div class="summary-row is-discount"><span>تخفیف محصولات</span><b id="summary-discount">۰ تومان</b></div>
            <div class="summary-row"><span>هزینه ارسال</span><b id="summary-shipping">پس از انتخاب آدرس</b></div>
            <div class="summary-total"><span>مبلغ قابل پرداخت</span><b id="summary-total">۰ تومان</b></div>

            <form class="coupon" id="coupon-form" novalidate>
              <input id="coupon-input" type="text" placeholder="کد تخفیف دارید؟" aria-label="کد تخفیف">
              <button type="submit">اعمال</button>
            </form>
            <p class="coupon-note" id="coupon-note">کد تخفیف در مرحله پرداخت نیز قابل ثبت است.</p>

            <button class="btn btn-primary btn-block" type="button" id="cart-checkout">ادامه فرآیند خرید</button>
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
              <a class="text-link" href="contact.html">درخواست مشاوره <svg><use href="#i-chevron-left"/></svg></a>
            </div>
          </div>
        </aside>
      </div>

      <section class="page-section">
        <div class="section-head">
          <div><h2>محصولات پیشنهادی</h2><p>بر اساس سبد خرید شما</p></div>
          <a class="section-link" href="shop.html">مشاهده همه <svg><use href="#i-chevron-left"/></svg></a>
        </div>
        <div class="carousel-wrap">
          <div class="product-track" id="cart-suggested"></div>
        </div>
      </section>
    </div>
  </main>

  <div class="cart-mobile-bar mobile-only">
    <div class="cart-mobile-total">
      <span>مبلغ قابل پرداخت</span>
      <b id="cart-mobile-total">۰ تومان</b>
    </div>
    <button class="btn btn-primary" type="button" id="cart-mobile-checkout">ادامه فرآیند خرید</button>
  </div>
"""


# ==========================================================================
#  حساب کاربری
# ==========================================================================
ACCOUNT_CONTENT = """  <main class="page-main" id="main-content">
    <div class="page-container">
      <nav class="breadcrumb" aria-label="مسیر صفحه">
        <a href="index.html">خانه</a><svg><use href="#i-chevron-left"/></svg><span class="current">حساب کاربری</span>
      </nav>

      <header class="account-hero">
        <div class="account-hero-user">
          <span class="account-avatar" aria-hidden="true">ع</span>
          <div class="account-hero-id">
            <span class="account-badge"><svg><use href="#i-award"/></svg>کاربر طلایی</span>
            <h1>علی رضایی</h1>
            <p><bdo dir="ltr">۰۹۱۲۳۴۵۶۷۸۹</bdo> • <a href="mailto:ali@example.com">ali@example.com</a></p>
          </div>
          <div class="account-hero-actions">
            <button class="btn btn-outline btn-sm" type="button" data-open-login><svg><use href="#i-user"/></svg>تغییر حساب</button>
            <a class="btn btn-soft btn-sm" href="cart.html"><svg><use href="#i-bag"/></svg>سبد خرید</a>
          </div>
        </div>

        <div class="account-quick">
          <button type="button" class="quick-card" data-account-tab="orders">
            <span class="quick-icon"><svg><use href="#i-box"/></svg></span>
            <b id="quick-active-orders">۲</b>
            <span>سفارش جاری</span>
          </button>
          <button type="button" class="quick-card" data-account-tab="orders">
            <span class="quick-icon"><svg><use href="#i-check-circle"/></svg></span>
            <b>۷</b>
            <span>سفارش تحویل‌شده</span>
          </button>
          <button type="button" class="quick-card" data-account-tab="favorites">
            <span class="quick-icon"><svg><use href="#i-heart"/></svg></span>
            <b id="quick-wishlist">۰</b>
            <span>علاقه‌مندی</span>
          </button>
          <button type="button" class="quick-card" data-account-tab="wallet">
            <span class="quick-icon"><svg><use href="#i-wallet"/></svg></span>
            <b>۱۲۰٫۰۰۰</b>
            <span>اعتبار کیف پول (تومان)</span>
          </button>
        </div>
      </header>

      <div class="account-layout">
        <aside class="account-side" aria-label="منوی حساب کاربری">
          <nav class="account-nav">
            <button type="button" class="active" data-account-tab="overview"><svg><use href="#i-grid"/></svg>پیشخوان</button>
            <button type="button" data-account-tab="orders"><svg><use href="#i-box"/></svg>سفارش‌های من<b>۲</b></button>
            <button type="button" data-account-tab="addresses"><svg><use href="#i-pin"/></svg>آدرس‌های من</button>
            <button type="button" data-account-tab="favorites"><svg><use href="#i-heart"/></svg>علاقه‌مندی‌ها</button>
            <button type="button" data-account-tab="wallet"><svg><use href="#i-wallet"/></svg>کیف پول و کد تخفیف</button>
            <button type="button" data-account-tab="profile"><svg><use href="#i-user"/></svg>اطلاعات حساب</button>
            <button type="button" data-account-tab="support"><svg><use href="#i-headset"/></svg>پشتیبانی</button>
            <button type="button" class="account-logout" data-open-login><svg><use href="#i-logout"/></svg>خروج از حساب</button>
          </nav>
          <div class="account-side-help">
            <h3>نیاز به راهنمایی دارید؟</h3>
            <p>شنبه تا پنج‌شنبه، ۶:۳۰ تا ۲۰:۳۰</p>
            <a class="btn btn-primary btn-sm btn-block" href="tel:09113294688"><svg><use href="#i-phone"/></svg>۰۹۱۱۳۲۹۴۶۸۸</a>
          </div>
        </aside>

        <div class="account-main">
          <!-- پیشخوان -->
          <section class="account-panel active" id="panel-overview" aria-label="پیشخوان">
            <article class="track-card">
              <div class="track-head">
                <div>
                  <span class="track-label">آخرین سفارش</span>
                  <h2>سفارش RS-۱۴۰۵۰۸۲۳</h2>
                  <p>ثبت شده در ۲۳ آبان ۱۴۰۵ • مبلغ ۳٫۴۱۰٫۰۰۰ تومان</p>
                </div>
                <a class="btn btn-outline btn-sm" href="order-details.html?id=RS-14050823">جزئیات سفارش</a>
              </div>
              <div class="track-steps">
                <span class="done"><i><svg><use href="#i-check"/></svg></i>ثبت سفارش</span>
                <span class="done"><i><svg><use href="#i-check"/></svg></i>آماده‌سازی</span>
                <span class="current"><i><svg><use href="#i-truck"/></svg></i>در حال ارسال</span>
                <span><i><svg><use href="#i-home"/></svg></i>تحویل به شما</span>
              </div>
              <div class="track-items">
                <img src="assets/images/sale-spider-gold.webp" alt="">
                <img src="assets/images/best-ceramic.webp" alt="">
                <img src="assets/images/sale-kardak.webp" alt="">
                <span>۳ کالا در این سفارش</span>
              </div>
            </article>

            <div class="account-cards">
              <article class="mini-card">
                <h3><svg><use href="#i-pin"/></svg>آدرس پیش‌فرض</h3>
                <p>مازندران، آمل، خیابان طالب آملی، دریای ۳۰/۱، پلاک ۴۴۴</p>
                <button class="text-link" type="button" data-account-tab="addresses">مدیریت آدرس‌ها <svg><use href="#i-chevron-left"/></svg></button>
              </article>
              <article class="mini-card">
                <h3><svg><use href="#i-gift"/></svg>باشگاه مشتریان</h3>
                <p>با هر خرید امتیاز بگیرید و در خریدهای بعدی تخفیف دریافت کنید. امتیاز فعلی شما: ۱٫۲۴۰</p>
                <a class="text-link" href="shop.html?sale=1">مشاهده پیشنهادهای ویژه <svg><use href="#i-chevron-left"/></svg></a>
              </article>
            </div>

            <div class="panel-head">
              <h2>علاقه‌مندی‌های من</h2>
              <button class="text-link" type="button" data-account-tab="favorites">مشاهده همه <svg><use href="#i-chevron-left"/></svg></button>
            </div>
            <div class="favorites-strip" id="overview-favorites"></div>
          </section>

          <!-- سفارش ها -->
          <section class="account-panel" id="panel-orders" aria-label="سفارش‌های من">
            <div class="panel-head"><h2>سفارش‌های من</h2><span class="panel-hint">۲ سفارش جاری، ۷ سفارش تحویل‌شده</span></div>

            ">
              <header class="order-head">
                <div><b>RS-۱۴۰۵۰۸۲۳</b><span>۲۳ آبان ۱۴۰۵</span></div>
                <span class="status-chip is-shipping">در حال ارسال</span>
              </header>
              <div class="order-body">
                <div class="order-thumbs">
                  <img src="assets/images/sale-spider-gold.webp" alt="">
                  <img src="assets/images/best-ceramic.webp" alt="">
                  <img src="assets/images/sale-kardak.webp" alt="">
                </div>
                <p class="order-desc">تار عنکبوتی گلد ساندورا، تکسچر سرامیک کارن ۱ کیلویی، کاردک استیل ۳ عددی ویلکو</p>
                <div class="order-price"><span>مبلغ کل</span><b>۳٫۰۲۵٫۰۰۰ تومان</b></div>
              </div>
              <footer class="order-foot">
                <a class="btn btn-outline btn-sm" href="order-details.html?id=RS-14050823"><svg><use href="#i-box"/></svg>جزئیات سفارش</a>
                <button class="btn btn-ghost btn-sm" type="button" data-toast="پیگیری مرسوله: کد رهگیری ۲۴۵۶۷۸۹۰"><svg><use href="#i-truck"/></svg>پیگیری مرسوله</button>
              </footer>
            </article>

            ">
              <header class="order-head">
                <div><b>RS-۱۴۰۵۰۷۱۴</b><span>۱۴ مهر ۱۴۰۵</span></div>
                <span class="status-chip is-processing">در حال آماده‌سازی</span>
              </header>
              <div class="order-body">
                <div class="order-thumbs">
                  <img src="assets/images/popular-resin.webp" alt="">
                  <img src="assets/images/popular-acrylic.webp" alt="">
                </div>
                <p class="order-desc">رزین اپوکسی هنری شفاف ۱ کیلویی، رنگ اکریلیک هنری سوداکو ۲۵۰ میل</p>
                <div class="order-price"><span>مبلغ کل</span><b>۳٫۵۸۰٫۰۰۰ تومان</b></div>
              </div>
              <footer class="order-foot">
                <a class="btn btn-outline btn-sm" href="order-details.html?id=RS-14050714"><svg><use href="#i-box"/></svg>جزئیات سفارش</a>
                <button class="btn btn-ghost btn-sm" type="button" data-toast="سفارش شما لغو شد (نمایشی)"><svg><use href="#i-close"/></svg>لغو سفارش</button>
              </footer>
            </article>

             is-done">
              <header class="order-head">
                <div><b>RS-۱۴۰۵۰۶۰۲</b><span>۰۲ شهریور ۱۴۰۵</span></div>
                <span class="status-chip is-delivered">تحویل شده</span>
              </header>
              <div class="order-body">
                <div class="order-thumbs">
                  <img src="assets/images/new-canvas-100.webp" alt="">
                  <img src="assets/images/best-lacquer.webp" alt="">
                </div>
                <p class="order-desc">بوم نیم دایره قطر ۱۰۰، لاک افکت کارن ۲۵۰ میل</p>
                <div class="order-price"><span>مبلغ کل</span><b>۲٫۴۷۰٫۰۰۰ تومان</b></div>
              </div>
              <footer class="order-foot">
                <button class="btn btn-ghost btn-sm" type="button" data-toast="کالاها به سبد خرید اضافه شدند (نمایشی)"><svg><use href="#i-refresh"/></svg>خرید مجدد</button>
                <button class="btn btn-outline btn-sm" type="button" data-toast="ثبت دیدگاه در نسخه نمایشی فعال نیست"><svg><use href="#i-edit"/></svg>ثبت دیدگاه</button>
              </footer>
            </article>
          </section>

          <!-- آدرس ها -->
          <section class="account-panel" id="panel-addresses" aria-label="آدرس‌های من">
            <div class="panel-head"><h2>آدرس‌های من</h2><span class="panel-hint">حداکثر ۵ آدرس قابل ثبت است</span></div>
            <div class="address-grid">
              <article class="address-card is-default">
                <header><span class="default-chip">پیش‌فرض</span><b>فروشگاه (تحویل حضوری)</b></header>
                <p>مازندران، آمل، خیابان طالب آملی، دریای ۳۰/۱، پلاک ۴۴۴</p>
                <footer>
                  <span><bdo dir="ltr">۰۹۱۲۳۴۵۶۷۸۹</bdo></span>
                  <button type="button" data-toast="ویرایش آدرس (نمایشی)"><svg><use href="#i-edit"/></svg>ویرایش</button>
                </footer>
              </article>
              <article class="address-card">
                <header><b>منزل</b></header>
                <p>مازندران، آمل، بلوار طالب آملی، کوچه ۱۲، ساختمان نگین، طبقه ۳</p>
                <footer>
                  <span><bdo dir="ltr">۰۹۱۱۳۲۹۴۶۸۸</bdo></span>
                  <button type="button" data-toast="ویرایش آدرس (نمایشی)"><svg><use href="#i-edit"/></svg>ویرایش</button>
                </footer>
              </article>
            </div>

            <details class="address-form">
              <summary><svg><use href="#i-plus"/></svg>افزودن آدرس جدید</summary>
              <form data-demo-form="آدرس جدید ثبت شد (نمایشی)">
                <div class="form-grid">
                  <div class="field"><label for="a-title">عنوان آدرس <span>*</span></label><input id="a-title" required placeholder="مثلاً محل کار"></div>
                  <div class="field"><label for="a-phone">شماره تماس <span>*</span></label><input id="a-phone" type="tel" required placeholder="۰۹۱۲۳۴۵۶۷۸۹"></div>
                  <div class="field"><label for="a-province">استان <span>*</span></label><input id="a-province" required value="مازندران"></div>
                  <div class="field"><label for="a-city">شهر <span>*</span></label><input id="a-city" required value="آمل"></div>
                  <div class="field full"><label for="a-address">نشانی کامل <span>*</span></label><textarea id="a-address" required placeholder="خیابان، کوچه، پلاک، واحد"></textarea></div>
                </div>
                <button class="btn btn-primary" type="submit" style="margin-top:12px">ثبت آدرس</button>
              </form>
            </details>
          </section>

          <!-- علاقه مندی ها -->
          <section class="account-panel" id="panel-favorites" aria-label="علاقه‌مندی‌ها">
            <div class="panel-head"><h2>علاقه‌مندی‌های من</h2><span class="panel-hint" id="favorites-count"></span></div>
            <div id="account-favorites" class="favorites-grid"></div>
          </section>

          <!-- کیف پول -->
          <section class="account-panel" id="panel-wallet" aria-label="کیف پول و کد تخفیف">
            <div class="panel-head"><h2>کیف پول و کد تخفیف</h2></div>
            <div class="wallet-grid">
              <article class="wallet-card">
                <span class="wallet-label">موجودی کیف پول</span>
                <b>۱۲۰٫۰۰۰ <small>تومان</small></b>
                <button class="btn btn-primary btn-sm" type="button" data-toast="درگاه پرداخت در نسخه نمایشی فعال نیست"><svg><use href="#i-plus"/></svg>افزایش موجودی</button>
              </article>
              <article class="wallet-card is-gift">
                <span class="wallet-label">کارت هدیه</span>
                <b>۵۰٫۰۰۰ <small>تومان</small></b>
                <button class="btn btn-soft btn-sm" type="button" data-toast="ثبت کارت هدیه (نمایشی)"><svg><use href="#i-gift"/></svg>ثبت کارت هدیه</button>
              </article>
            </div>
            <div class="coupon-list">
              <h3>کدهای تخفیف شما</h3>
              <div class="coupon-row"><span class="coupon-code">RANG15</span><span>۱۵٪ تخفیف روی رنگ‌های ساختمانی</span><b>تا ۳۰ آبان</b></div>
              <div class="coupon-row"><span class="coupon-code">PATINA10</span><span>۱۰٪ تخفیف روی متریال پتینه</span><b>تا ۱۵ آذر</b></div>
            </div>
          </section>

          <!-- اطلاعات حساب -->
          <section class="account-panel" id="panel-profile" aria-label="اطلاعات حساب">
            <div class="panel-head"><h2>اطلاعات حساب</h2><span class="panel-hint">اطلاعات شما نزد ما محفوظ است</span></div>
            <form class="profile-form" data-demo-form="اطلاعات حساب با موفقیت ذخیره شد">
              <div class="form-grid">
                <div class="field"><label for="p-name">نام و نام خانوادگی <span>*</span></label><input id="p-name" required value="علی رضایی"></div>
                <div class="field"><label for="p-phone">شماره موبایل <span>*</span></label><input id="p-phone" type="tel" required value="09123456789"></div>
                <div class="field"><label for="p-email">ایمیل</label><input id="p-email" type="email" value="ali@example.com"></div>
                <div class="field"><label for="p-code">کد ملی</label><input id="p-code" inputmode="numeric" value="1234567890"></div>
                <div class="field"><label for="p-birth">تاریخ تولد</label><input id="p-birth" value="۱۳۷۰/۰۵/۱۲"></div>
                <div class="field"><label for="p-job">شغل</label><input id="p-job" value="مجری پروژه‌های دکوراتیو"></div>
              </div>
              <button class="btn btn-primary" type="submit" style="margin-top:14px"><svg><use href="#i-check"/></svg>ذخیره تغییرات</button>
            </form>
          </section>

          <!-- پشتیبانی -->
          <section class="account-panel" id="panel-support" aria-label="پشتیبانی">
            <div class="panel-head"><h2>پشتیبانی</h2></div>
            <div class="support-grid">
              <a class="support-tile" href="tel:09113294688"><span class="quick-icon"><svg><use href="#i-phone"/></svg></span><b>تماس تلفنی</b><span>۰۹۱۱۳۲۹۴۶۸۸</span></a>
              <a class="support-tile" href="https://wa.me/989113294688" target="_blank" rel="noopener"><span class="quick-icon"><svg><use href="#i-whatsapp"/></svg></span><b>واتساپ</b><span>پاسخ سریع در ساعات کاری</span></a>
              <a class="support-tile" href="contact.html"><span class="quick-icon"><svg><use href="#i-mail"/></svg></span><b>فرم تماس</b><span>پاسخ حداکثر تا ۲۴ ساعت</span></a>
            </div>
            <div class="faq-list" style="margin-top:18px">
              <details class="faq-item" open><summary>چطور سفارش خود را پیگیری کنم؟<svg><use href="#i-chevron-down"/></svg></summary><p>از بخش «سفارش‌های من» روی دکمه پیگیری مرسوله بزنید؛ کد رهگیری و وضعیت لحظه‌ای مرسوله نمایش داده می‌شود.</p></details>
              <details class="faq-item"><summary>امکان تغییر آدرس پس از ثبت سفارش وجود دارد؟<svg><use href="#i-chevron-down"/></svg></summary><p>تا پیش از تحویل مرسوله به پست، با پشتیبانی تماس بگیرید تا آدرس اصلاح شود.</p></details>
              <details class="faq-item"><summary>شرایط مرجوعی کالا چیست؟<svg><use href="#i-chevron-down"/></svg></summary><p>کالاهای سالم و پلمب‌نشده تا ۷ روز پس از تحویل قابل بازگشت هستند.</p></details>
            </div>
          </section>
        </div>
      </div>
    </div>
  </main>
"""


# ==========================================================================
#  مراحل خرید (مشترک سبد خرید و تسویه حساب)
# ==========================================================================
CO_STEPS = [
    ("cart", "i-bag", "سبد خرید", "بررسی کالاها"),
    ("address", "i-pin", "آدرس تحویل", "گیرنده و نشانی"),
    ("shipping", "i-truck", "شیوه ارسال", "روش و زمان تحویل"),
    ("payment", "i-card", "پرداخت", "روش پرداخت و تخفیف"),
    ("done", "i-check", "تکمیل سفارش", "ثبت نهایی"),
]


def co_steps(active_index: int, interactive: bool = False) -> str:
    items = []
    for index, (key, icon, label, sub) in enumerate(CO_STEPS):
        state = "is-done" if index < active_index else ("is-active" if index == active_index else "")
        current = ' aria-current="step"' if index == active_index else ""
        inner = f'<i><svg><use href="#{icon}"/></svg><em>{"۱۲۳۴۵"[index]}</em></i><span><b>{label}</b><small>{sub}</small></span>'
        if key == "cart" and active_index > 0:
            tag = f'<a class="co-step-btn" href="cart.html">{inner}</a>'
        elif interactive:
            tag = f'<button class="co-step-btn" type="button" data-goto="{key}" disabled>{inner}</button>'
        else:
            tag = f'<span class="co-step-btn">{inner}</span>'
        items.append(f'        <li class="{state}" data-step-key="{key}"{current}>{tag}</li>')
    progress = active_index / (len(CO_STEPS) - 1)
    return (f'      <ol class="co-steps" id="co-steps" style="--p:{progress:.2f}" aria-label="مراحل خرید">\n'
            + "\n".join(items) + "\n      </ol>")


# ==========================================================================
#  تسویه حساب (چند مرحله‌ای)
# ==========================================================================
PROVINCES = ["مازندران", "تهران", "گیلان", "گلستان", "البرز", "اصفهان", "خراسان رضوی", "فارس", "آذربایجان شرقی", "قم", "سمنان", "کرمان", "خوزستان", "یزد"]
PROVINCE_OPTIONS = "".join(f'<option{" selected" if p == "مازندران" else ""}>{p}</option>' for p in PROVINCES)

CHECKOUT_CONTENT = """  <main class="page-main" id="main-content">
    <div class="page-container">
      <nav class="breadcrumb" aria-label="مسیر صفحه">
        <a href="index.html">خانه</a><svg><use href="#i-chevron-left"/></svg>
        <a href="cart.html">سبد خرید</a><svg><use href="#i-chevron-left"/></svg>
        <span class="current">تسویه حساب</span>
      </nav>

{STEPS}

      <div class="cart-layout co-layout" id="co-layout">
        <div class="co-main">

          <!-- ========== مرحله ۱: آدرس و گیرنده ========== -->
          <section class="co-step is-active" id="co-step-address" data-step="address" aria-labelledby="co-address-title">
            <div class="co-card">
              <header class="co-card-head">
                <span class="co-card-num">۱</span>
                <div class="co-card-title">
                  <h1 id="co-address-title">آدرس تحویل سفارش</h1>
                  <p>یکی از آدرس‌های ذخیره‌شده را انتخاب کنید یا آدرس جدید ثبت کنید.</p>
                </div>
                <a class="text-link co-head-link" href="account.html#addresses">مدیریت آدرس‌ها <svg><use href="#i-chevron-left"/></svg></a>
              </header>

              <div class="co-address-list" id="co-address-list" role="radiogroup" aria-label="انتخاب آدرس تحویل"></div>
              <p class="co-inline-error" id="co-address-error" hidden>لطفاً یک آدرس برای ارسال انتخاب کنید.</p>

              <button class="co-add-address" type="button" id="co-add-address" aria-expanded="false" aria-controls="co-address-form">
                <span><svg><use href="#i-plus"/></svg></span>افزودن آدرس جدید
              </button>

              <form class="co-address-form" id="co-address-form" novalidate hidden>
                <h3><svg><use href="#i-pin"/></svg>ثبت آدرس جدید</h3>
                <div class="form-grid">
                  <div class="field"><label for="na-title">عنوان آدرس <span>*</span></label><input id="na-title" name="title" placeholder="مثلاً: کارگاه، منزل پدری" autocomplete="off"><small class="error"></small></div>
                  <div class="field"><label for="na-name">نام و نام خانوادگی گیرنده <span>*</span></label><input id="na-name" name="name" autocomplete="name" placeholder="مثلاً: علی رضایی"><small class="error"></small></div>
                  <div class="field"><label for="na-phone">شماره موبایل <span>*</span></label><input id="na-phone" name="phone" type="tel" inputmode="numeric" autocomplete="tel" placeholder="۰۹۱۲۳۴۵۶۷۸۹" dir="ltr"><small class="error"></small></div>
                  <div class="field"><label for="na-province">استان <span>*</span></label><select id="na-province" name="province">{PROVINCES}</select><small class="error"></small></div>
                  <div class="field"><label for="na-city">شهر <span>*</span></label><input id="na-city" name="city" placeholder="مثلاً: آمل" autocomplete="address-level2"><small class="error"></small></div>
                  <div class="field"><label for="na-postal">کد پستی ۱۰ رقمی <span>*</span></label><input id="na-postal" name="postal" inputmode="numeric" autocomplete="postal-code" placeholder="بدون خط تیره" dir="ltr"><small class="error"></small></div>
                  <div class="field full"><label for="na-address">نشانی کامل <span>*</span></label><textarea id="na-address" name="address" rows="3" placeholder="خیابان، کوچه، پلاک، واحد"></textarea><small class="error"></small></div>
                </div>
                <div class="co-form-actions">
                  <button class="btn btn-primary" type="submit"><svg><use href="#i-check"/></svg>ثبت و انتخاب این آدرس</button>
                  <button class="btn btn-outline" type="button" id="co-cancel-address">انصراف</button>
                </div>
              </form>
            </div>

            <div class="co-card">
              <header class="co-card-head">
                <span class="co-card-icon"><svg><use href="#i-user"/></svg></span>
                <div class="co-card-title"><h2>مشخصات گیرنده</h2><p>سفارش به چه کسی تحویل داده شود؟</p></div>
              </header>
              <label class="co-check">
                <input type="checkbox" id="co-receiver-self" checked>
                <span class="co-check-box"><svg><use href="#i-check"/></svg></span>
                <span>گیرنده سفارش خودم هستم <small id="co-self-info">(علی رضایی — ۰۹۱۲۳۴۵۶۷۸۹)</small></span>
              </label>
              <div class="form-grid co-receiver" id="co-receiver" hidden>
                <div class="field"><label for="rc-name">نام و نام خانوادگی گیرنده <span>*</span></label><input id="rc-name" autocomplete="off" placeholder="نام گیرنده"><small class="error"></small></div>
                <div class="field"><label for="rc-phone">شماره موبایل گیرنده <span>*</span></label><input id="rc-phone" type="tel" inputmode="numeric" placeholder="۰۹۱۲۳۴۵۶۷۸۹" dir="ltr"><small class="error"></small></div>
              </div>
            </div>
          </section>

          <!-- ========== مرحله ۲: شیوه و زمان ارسال ========== -->
          <section class="co-step" id="co-step-shipping" data-step="shipping" aria-labelledby="co-ship-title" hidden>
            <div class="co-card">
              <header class="co-card-head">
                <span class="co-card-num">۲</span>
                <div class="co-card-title">
                  <h1 id="co-ship-title">شیوه ارسال</h1>
                  <p>ارسال به: <b id="co-ship-to">—</b></p>
                </div>
                <button class="text-link co-head-link" type="button" data-goto="address">تغییر آدرس <svg><use href="#i-chevron-left"/></svg></button>
              </header>
              <div class="co-options" id="co-methods" role="radiogroup" aria-label="شیوه ارسال"></div>
            </div>

            <div class="co-card" id="co-slot-card">
              <header class="co-card-head">
                <span class="co-card-icon"><svg><use href="#i-calendar"/></svg></span>
                <div class="co-card-title"><h2>زمان تحویل</h2><p id="co-slot-hint">روز و بازه زمانی دلخواه را انتخاب کنید.</p></div>
              </header>
              <div class="co-days" id="co-days" role="radiogroup" aria-label="روز تحویل"></div>
              <div class="co-slots" id="co-slots" role="radiogroup" aria-label="بازه زمانی تحویل"></div>
            </div>

            <div class="co-card co-eta-card" id="co-eta-card" hidden>
              <span class="co-card-icon"><svg><use href="#i-clock"/></svg></span>
              <div><b>زمان تقریبی تحویل</b><p id="co-eta-text">—</p></div>
            </div>

            <div class="co-card">
              <header class="co-card-head">
                <span class="co-card-icon"><svg><use href="#i-box"/></svg></span>
                <div class="co-card-title"><h2>مرسوله شما</h2><p>ارسال از انبار مرکزی رنگ صادقی — آمل</p></div>
              </header>
              <div class="co-shipment" id="co-shipment"></div>
              <label class="co-check">
                <input type="checkbox" id="co-gift">
                <span class="co-check-box"><svg><use href="#i-check"/></svg></span>
                <span><svg class="co-check-icon"><use href="#i-gift"/></svg>بسته‌بندی هدیه همراه کارت پیام <small>(+ ۳۵٫۰۰۰ تومان)</small></span>
              </label>
              <div class="field co-note">
                <label for="co-note">توضیحات سفارش <small>(اختیاری)</small></label>
                <textarea id="co-note" rows="3" maxlength="300" placeholder="مثلاً: قبل از ارسال تماس بگیرید، یا کد رنگ مورد نظرتان را بنویسید…"></textarea>
              </div>
            </div>
          </section>

          <!-- ========== مرحله ۳: پرداخت ========== -->
          <section class="co-step" id="co-step-payment" data-step="payment" aria-labelledby="co-pay-title" hidden>
            <div class="co-card">
              <header class="co-card-head">
                <span class="co-card-num">۳</span>
                <div class="co-card-title"><h1 id="co-pay-title">روش پرداخت</h1><p>تمام پرداخت‌ها از طریق درگاه امن و رمزنگاری‌شده انجام می‌شود.</p></div>
              </header>
              <div class="co-options" id="co-payments" role="radiogroup" aria-label="روش پرداخت"></div>

              <label class="co-wallet" for="co-wallet">
                <input type="checkbox" id="co-wallet">
                <span class="co-wallet-icon"><svg><use href="#i-wallet"/></svg></span>
                <span class="co-wallet-body"><b>استفاده از اعتبار کیف پول</b><small>موجودی قابل استفاده: ۱۲۰٫۰۰۰ تومان</small></span>
                <span class="co-switch" aria-hidden="true"></span>
              </label>
            </div>

            <div class="co-card">
              <header class="co-card-head">
                <span class="co-card-icon"><svg><use href="#i-ticket"/></svg></span>
                <div class="co-card-title"><h2>کد تخفیف</h2><p>کد تخفیف یا کارت هدیه خود را وارد کنید.</p></div>
              </header>
              <form class="coupon co-coupon" id="co-coupon-form" novalidate>
                <input id="co-coupon-input" type="text" placeholder="مثلاً RANG15" aria-label="کد تخفیف" autocomplete="off" dir="ltr">
                <button type="submit">ثبت کد</button>
              </form>
              <p class="coupon-note" id="co-coupon-note">کدهای نمونه فعال: RANG15 ،PATINA10 ،SADEGHI5</p>
            </div>

            <div class="co-card">
              <header class="co-card-head">
                <span class="co-card-icon"><svg><use href="#i-check-circle"/></svg></span>
                <div class="co-card-title"><h2>بازبینی نهایی سفارش</h2><p>پیش از پرداخت، اطلاعات زیر را بررسی کنید.</p></div>
              </header>
              <div class="co-review" id="co-review"></div>
              <label class="co-check">
                <input type="checkbox" id="co-invoice">
                <span class="co-check-box"><svg><use href="#i-check"/></svg></span>
                <span>فاکتور رسمی همراه مرسوله ارسال شود</span>
              </label>
              <label class="co-check co-terms">
                <input type="checkbox" id="co-terms">
                <span class="co-check-box"><svg><use href="#i-check"/></svg></span>
                <span><a href="contact.html">قوانین و مقررات</a> و <a href="contact.html">شرایط بازگشت کالا</a> فروشگاه رنگ صادقی را خوانده‌ام و می‌پذیرم.</span>
              </label>
              <p class="co-inline-error" id="co-terms-error" hidden>برای ثبت سفارش، پذیرش قوانین و مقررات الزامی است.</p>
            </div>
          </section>

          <!-- ========== مرحله ۴: تکمیل سفارش ========== -->
          <section class="co-step co-done" id="co-step-done" data-step="done" aria-live="polite" hidden></section>

          <div class="co-nav" id="co-nav">
            <button class="btn btn-outline" type="button" id="co-back"><svg><use href="#i-chevron-right"/></svg><span>بازگشت به سبد خرید</span></button>
            <button class="btn btn-primary" type="button" id="co-next"><span>ادامه</span><svg><use href="#i-chevron-left"/></svg></button>
          </div>
        </div>

        <aside class="cart-side co-side" id="co-side" aria-label="خلاصه سفارش">
          <div class="summary-card">
            <h2>خلاصه سفارش <small id="co-count"></small></h2>
            <details class="co-items" id="co-items" open>
              <summary><span>کالاهای سفارش</span><svg><use href="#i-chevron-down"/></svg></summary>
              <div class="co-items-list" id="co-items-list"></div>
            </details>
            <div class="summary-row"><span>قیمت کالاها</span><b id="co-sum-original">۰ تومان</b></div>
            <div class="summary-row is-discount"><span>تخفیف کالاها</span><b id="co-sum-discount">۰ تومان</b></div>
            <div class="summary-row is-discount" id="co-sum-coupon-row" hidden><span>کد تخفیف <em id="co-sum-coupon-code"></em></span><b id="co-sum-coupon">۰ تومان</b></div>
            <div class="summary-row"><span>هزینه ارسال <em id="co-sum-method"></em></span><b id="co-sum-shipping">—</b></div>
            <div class="summary-row" id="co-sum-gift-row" hidden><span>بسته‌بندی هدیه</span><b>۳۵٫۰۰۰ تومان</b></div>
            <div class="summary-row is-discount" id="co-sum-wallet-row" hidden><span>اعتبار کیف پول</span><b id="co-sum-wallet">۰ تومان</b></div>
            <div class="summary-total"><span>مبلغ قابل پرداخت</span><b id="co-sum-total">۰ تومان</b></div>
            <div class="co-profit" id="co-profit" hidden><svg><use href="#i-gift"/></svg><span>سود شما از این خرید</span><b id="co-profit-value"></b></div>
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
            <div><svg><use href="#i-headset"/></svg><span>پشتیبانی<br><b dir="ltr">0911 329 4688</b></span></div>
          </div>
        </aside>
      </div>

      <div class="co-empty" id="co-empty" hidden>
        <svg><use href="#i-bag"/></svg>
        <h1>سبد خرید شما خالی است</h1>
        <p>برای تسویه حساب ابتدا کالاهای مورد نظرتان را به سبد خرید اضافه کنید.</p>
        <div><a class="btn btn-primary" href="shop.html"><svg><use href="#i-grid"/></svg>رفتن به فروشگاه</a><a class="btn btn-outline" href="account.html#orders">سفارش‌های من</a></div>
      </div>
    </div>
  </main>

  <div class="cart-mobile-bar co-mobile-bar mobile-only" id="co-mobile-bar">
    <div class="cart-mobile-total">
      <span>مبلغ قابل پرداخت</span>
      <b id="co-mobile-total">۰ تومان</b>
    </div>
    <button class="btn btn-primary" type="button" id="co-mobile-next">ادامه</button>
  </div>

  <div class="co-processing" id="co-processing" hidden>
    <div class="co-processing-box" role="alertdialog" aria-modal="true" aria-labelledby="co-processing-title" aria-describedby="co-processing-text">
      <span class="co-spinner" aria-hidden="true"></span>
      <h3 id="co-processing-title">در حال اتصال به درگاه پرداخت امن…</h3>
      <p id="co-processing-text">لطفاً تا پایان عملیات صفحه را نبندید.</p>
      <div class="co-processing-bar"><span></span></div>
    </div>
  </div>
""".replace("{STEPS}", co_steps(1, interactive=True)).replace("{PROVINCES}", PROVINCE_OPTIONS)


ORDER_CONTENT = """  <main class="page-main" id="main-content">
    <div class="page-container">
      <nav class="breadcrumb" aria-label="مسیر صفحه">
        <a href="index.html">خانه</a><svg><use href="#i-chevron-left"/></svg>
        <a href="account.html#orders">سفارش‌های من</a><svg><use href="#i-chevron-left"/></svg>
        <span class="current">جزئیات سفارش</span>
      </nav>

      <!-- ===================== سربرگ سفارش ===================== -->
      <section class="od-hero" aria-labelledby="od-title">
        <div class="od-hero-id">
          <span class="status-chip is-shipping" id="od-status"><svg><use href="#i-truck"/></svg>در حال ارسال</span>
          <h1 id="od-title">جزئیات سفارش <b id="od-code">RS-۱۴۰۵۰۸۲۳</b></h1>
          <p>
            ثبت‌شده در <span id="od-date">۲۳ آبان ۱۴۰۵</span>
            <span class="od-dot" aria-hidden="true"></span>
            <span id="od-count">۴ عدد کالا</span>
            <span class="od-dot" aria-hidden="true"></span>
            مبلغ کل <b id="od-total">۳٫۰۲۵٫۰۰۰ تومان</b>
          </p>
        </div>
        <div class="od-hero-actions">
          <button class="btn btn-outline btn-sm" type="button" data-copy="RS-۱۴۰۵۰۸۲۳"><svg><use href="#i-link"/></svg>کپی کد سفارش</button>
          <button class="btn btn-outline btn-sm" type="button" data-print><svg><use href="#i-download"/></svg>دریافت فاکتور</button>
          <a class="btn btn-primary btn-sm" href="tel:09113294688"><svg><use href="#i-headset"/></svg>پشتیبانی سفارش</a>
        </div>
      </section>

      <!-- ===================== وضعیت مرسوله ===================== -->
      <section class="od-track" id="od-track" aria-labelledby="od-track-title">
        <header class="od-track-head">
          <div>
            <h2 id="od-track-title">وضعیت مرسوله</h2>
            <p id="od-track-note">مرسوله در ۲۴ آبان تحویل پست پیشتاز شد و در مسیر آمل است.</p>
          </div>
          <div class="od-track-code">
            <span>کد رهگیری مرسوله</span>
            <b dir="ltr" id="od-tracking">۲۴۵۶۷۸۹۰۱۲۳۴۵</b>
            <button type="button" data-copy="۲۴۵۶۷۸۹۰۱۲۳۴۵" aria-label="کپی کد رهگیری"><svg><use href="#i-link"/></svg></button>
          </div>
        </header>

        <ol class="od-steps" id="od-steps">
          <li class="is-done"><i><svg><use href="#i-check"/></svg></i><span><b>ثبت سفارش</b><small>۲۳ آبان، ۱۰:۲۴</small></span></li>
          <li class="is-done"><i><svg><use href="#i-card"/></svg></i><span><b>تأیید پرداخت</b><small>۲۳ آبان، ۱۰:۲۵</small></span></li>
          <li class="is-done"><i><svg><use href="#i-box"/></svg></i><span><b>آماده‌سازی و بسته‌بندی</b><small>۲۳ آبان، ۱۲:۴۰</small></span></li>
          <li class="is-active"><i><svg><use href="#i-truck"/></svg></i><span><b>در حال ارسال</b><small>۲۴ آبان، ۰۸:۱۰</small></span></li>
          <li><i><svg><use href="#i-home"/></svg></i><span><b>تحویل به شما</b><small>انتظار: امروز ۱۴ تا ۱۸</small></span></li>
        </ol>

        <div class="od-track-foot">
          <span><svg><use href="#i-clock"/></svg>زمان تحویل تخمینی: امروز، ۲۶ آبان، ساعت ۱۴ تا ۱۸</span>
          <span><svg><use href="#i-pin"/></svg>مازندران، آمل، بلوار طالب آملی، کوچه ۱۲</span>
        </div>
      </section>

      <!-- ===================== ناوبری داخلی ===================== -->
      <nav class="od-subnav" aria-label="بخش‌های سفارش">
        <a class="is-active" href="#od-delivery">جزئیات سفارش</a>
        <a href="#od-items">اقلام و فاکتور</a>
        <a href="#od-history">گردش سفارش</a>
      </nav>

      <div class="od-layout">
        <div class="od-main">

          <!-- ============ اطلاعات ارسال ============ -->
          <section class="od-card" id="od-delivery" aria-labelledby="od-delivery-title">
            <header class="od-card-head">
              <h2 id="od-delivery-title"><span class="od-card-icon"><svg><use href="#i-truck"/></svg></span>اطلاعات ارسال و تحویل</h2>
              <button class="text-link" type="button" data-toast="پس از تحویل مرسوله به پست، تغییر آدرس ممکن نیست">ویرایش</button>
            </header>
            <dl class="od-info">
              <div><dt>گیرنده</dt><dd>علی رضایی</dd></div>
              <div><dt>شماره تماس</dt><dd><bdo dir="ltr">۰۹۱۲۳۴۵۶۷۸۹</bdo></dd></div>
              <div><dt>شیوه ارسال</dt><dd>پست پیشتاز — دارای کد رهگیری</dd></div>
              <div><dt>بازه زمانی تحویل</dt><dd>امروز، ۲۶ آبان، ساعت ۱۴ تا ۱۸</dd></div>
              <div class="wide"><dt>نشانی تحویل</dt><dd>مازندران، آمل، بلوار طالب آملی، کوچه ۱۲، ساختمان نگین، طبقه ۳، واحد ۱۲</dd></div>
              <div><dt>کد پستی</dt><dd><bdo dir="ltr">۴۶۱۴۸۵۳۷۲۱</bdo></dd></div>
              <div><dt>تعداد بسته</dt><dd>۱ بسته</dd></div>
              <div><dt>هزینه ارسال</dt><dd>۳۵٫۰۰۰ تومان</dd></div>
              <div><dt>بسته‌بندی</dt><dd>ایمن — ویژه گالن و سطل رنگ</dd></div>
            </dl>
          </section>

          <!-- ============ اطلاعات پرداخت ============ -->
          <section class="od-card" aria-labelledby="od-payment-title">
            <header class="od-card-head">
              <h2 id="od-payment-title"><span class="od-card-icon"><svg><use href="#i-card"/></svg></span>اطلاعات پرداخت</h2>
              <span class="od-badge is-paid"><svg><use href="#i-check-circle"/></svg>پرداخت‌شده</span>
            </header>
            <dl class="od-info">
              <div><dt>روش پرداخت</dt><dd>پرداخت اینترنتی (درگاه زرین‌پال)</dd></div>
              <div><dt>مبلغ پرداخت‌شده</dt><dd><b>۳٫۰۲۵٫۰۰۰ تومان</b></dd></div>
              <div><dt>تاریخ پرداخت</dt><dd>۲۳ آبان ۱۴۰۵، ساعت ۱۰:۲۵</dd></div>
              <div><dt>شماره پیگیری بانکی</dt><dd><bdo dir="ltr">۹۸۴۵۲۱۰۳۷۷</bdo></dd></div>
              <div><dt>کد تخفیف</dt><dd>استفاده نشده</dd></div>
              <div><dt>فاکتور رسمی</dt><dd><button class="text-link" type="button" data-print><svg><use href="#i-download"/></svg>دریافت فاکتور خرید</button></dd></div>
            </dl>
          </section>

          <!-- ============ اقلام و فاکتور ============ -->
          <section class="od-card" id="od-items" aria-labelledby="od-items-title">
            <header class="od-card-head">
              <h2 id="od-items-title"><span class="od-card-icon"><svg><use href="#i-bag"/></svg></span>اقلام سفارش</h2>
              <span class="panel-hint" id="od-items-hint">۴ عدد کالا در ۱ بسته</span>
            </header>

            <div class="od-items" id="od-items-list">
              <article class="od-item">
                <a class="od-item-img" href="product.html?id=p2"><img src="assets/images/sale-spider-gold.webp" alt="تار عنکبوتی ۵۰۰ گرمی گلد ساندورا" loading="lazy"></a>
                <div class="od-item-body">
                  <a class="od-item-title" href="product.html?id=p2">تار عنکبوتی ۵۰۰ گرمی گلد ساندورا</a>
                  <div class="od-item-meta"><span>فروشگاه رنگ صادقی</span><span aria-hidden="true">•</span><span>ارسال از انبار آمل</span><span aria-hidden="true">•</span><span>گارانتی اصالت کالا</span></div>
                  <div class="od-item-flags"><span class="mini-flag">در حال ارسال</span><span class="mini-flag is-muted">تحویل تا ۴ روز کاری</span></div>
                  <div class="od-item-actions">
                    <a class="cart-line-btn" href="product.html?id=p2"><svg><use href="#i-eye"/></svg>مشاهده محصول</a>
                    <button class="cart-line-btn" type="button" data-toast="درخواست مرجوعی این کالا ثبت شد (نمایشی)"><svg><use href="#i-refresh"/></svg>درخواست مرجوعی</button>
                    <button class="cart-line-btn" type="button" data-toast="ثبت دیدگاه پس از تحویل کالا فعال می‌شود"><svg><use href="#i-quote"/></svg>ثبت دیدگاه</button>
                  </div>
                </div>
                <div class="od-item-price">
                  <span class="old">۱٫۳۷۰٫۰۰۰ تومان</span>
                  <b>۱٫۲۲۰٫۰۰۰</b>
                  <small>تومان × ۱ عدد</small>
                </div>
              </article>

              <article class="od-item">
                <a class="od-item-img" href="product.html?id=p10"><img src="assets/images/best-ceramic.webp" alt="تکسچر سرامیک کارن ۱ کیلویی" loading="lazy"></a>
                <div class="od-item-body">
                  <a class="od-item-title" href="product.html?id=p10">تکسچر سرامیک کارن ۱ کیلویی</a>
                  <div class="od-item-meta"><span>فروشگاه رنگ صادقی</span><span aria-hidden="true">•</span><span>ارسال از انبار آمل</span><span aria-hidden="true">•</span><span>گارانتی اصالت کالا</span></div>
                  <div class="od-item-flags"><span class="mini-flag">در حال ارسال</span><span class="mini-flag is-muted">تحویل تا ۴ روز کاری</span></div>
                  <div class="od-item-actions">
                    <a class="cart-line-btn" href="product.html?id=p10"><svg><use href="#i-eye"/></svg>مشاهده محصول</a>
                    <button class="cart-line-btn" type="button" data-toast="درخواست مرجوعی این کالا ثبت شد (نمایشی)"><svg><use href="#i-refresh"/></svg>درخواست مرجوعی</button>
                    <button class="cart-line-btn" type="button" data-toast="ثبت دیدگاه پس از تحویل کالا فعال می‌شود"><svg><use href="#i-quote"/></svg>ثبت دیدگاه</button>
                  </div>
                </div>
                <div class="od-item-price">
                  <b>۱٫۱۸۰٫۰۰۰</b>
                  <small>تومان × ۲ عدد</small>
                </div>
              </article>

              <article class="od-item">
                <a class="od-item-img" href="product.html?id=p1"><img src="assets/images/sale-kardak.webp" alt="کاردک استیل ۳ عددی ویلکو" loading="lazy"></a>
                <div class="od-item-body">
                  <a class="od-item-title" href="product.html?id=p1">کاردک استیل ۳ عددی ویلکو</a>
                  <div class="od-item-meta"><span>فروشگاه رنگ صادقی</span><span aria-hidden="true">•</span><span>ارسال از انبار آمل</span><span aria-hidden="true">•</span><span>گارانتی اصالت کالا</span></div>
                  <div class="od-item-flags"><span class="mini-flag">در حال ارسال</span><span class="mini-flag is-muted">تحویل تا ۴ روز کاری</span></div>
                  <div class="od-item-actions">
                    <a class="cart-line-btn" href="product.html?id=p1"><svg><use href="#i-eye"/></svg>مشاهده محصول</a>
                    <button class="cart-line-btn" type="button" data-toast="درخواست مرجوعی این کالا ثبت شد (نمایشی)"><svg><use href="#i-refresh"/></svg>درخواست مرجوعی</button>
                    <button class="cart-line-btn" type="button" data-toast="ثبت دیدگاه پس از تحویل کالا فعال می‌شود"><svg><use href="#i-quote"/></svg>ثبت دیدگاه</button>
                  </div>
                </div>
                <div class="od-item-price">
                  <span class="old">۷۹۰٫۰۰۰ تومان</span>
                  <b>۵۹۰٫۰۰۰</b>
                  <small>تومان × ۱ عدد</small>
                </div>
              </article>
            </div>

            <div class="od-invoice">
              <div class="od-invoice-head">
                <h3>فاکتور خرید</h3>
                <span class="panel-hint">شماره فاکتور <bdo dir="ltr">۱۴۰۵-۰۸۲۳</bdo></span>
                <button class="btn btn-outline btn-sm" type="button" data-print><svg><use href="#i-download"/></svg>چاپ / دانلود فاکتور</button>
              </div>
              <div class="od-table-wrap">
                <table class="od-table">
                  <caption class="visually-hidden">فاکتور سفارش RS-۱۴۰۵۰۸۲۳</caption>
                  <thead>
                    <tr><th scope="col">#</th><th scope="col">کالا</th><th scope="col">تعداد</th><th scope="col">قیمت واحد</th><th scope="col">جمع</th></tr>
                  </thead>
                  <tbody id="od-invoice-body">
                    <tr><td>۱</td><td>تار عنکبوتی ۵۰۰ گرمی گلد ساندورا</td><td>۱</td><td>۱٫۲۲۰٫۰۰۰</td><td>۱٫۲۲۰٫۰۰۰</td></tr>
                    <tr><td>۲</td><td>تکسچر سرامیک کارن ۱ کیلویی</td><td>۲</td><td>۵۹۰٫۰۰۰</td><td>۱٫۱۸۰٫۰۰۰</td></tr>
                    <tr><td>۳</td><td>کاردک استیل ۳ عددی ویلکو</td><td>۱</td><td>۵۹۰٫۰۰۰</td><td>۵۹۰٫۰۰۰</td></tr>
                  </tbody>
                </table>
              </div>
              <div class="od-totals">
                <div><span>جمع کالاها (پیش از تخفیف)</span><b>۳٫۸۱۸٫۰۰۰ تومان</b></div>
                <div class="is-discount"><span>تخفیف ویژه فروشگاه</span><b>۸۲۸٫۰۰۰ − تومان</b></div>
                <div><span>هزینه ارسال (پست پیشتاز)</span><b>۳۵٫۰۰۰ تومان</b></div>
                <div class="od-total-row"><span>مبلغ پرداخت‌شده</span><b>۳٫۰۲۵٫۰۰۰ تومان</b></div>
              </div>
            </div>
          </section>

          <!-- ============ گردش سفارش ============ -->
          <section class="od-card" id="od-history" aria-labelledby="od-history-title">
            <header class="od-card-head">
              <h2 id="od-history-title"><span class="od-card-icon"><svg><use href="#i-clock"/></svg></span>گردش سفارش</h2>
              <span class="panel-hint">۵ رویداد ثبت‌شده</span>
            </header>
            <ol class="od-timeline">
              <li class="is-done"><i><svg><use href="#i-check"/></svg></i><div><b>ثبت سفارش</b><small>۲۳ آبان ۱۴۰۵، ساعت ۱۰:۲۴</small><p>سفارش شما با شماره RS-۱۴۰۵۰۸۲۳ در سایت ثبت شد.</p></div></li>
              <li class="is-done"><i><svg><use href="#i-card"/></svg></i><div><b>تأیید پرداخت</b><small>۲۳ آبان ۱۴۰۵، ساعت ۱۰:۲۵</small><p>پرداخت اینترنتی با شماره پیگیری ۹۸۴۵۲۱۰۳۷۷ تأیید شد.</p></div></li>
              <li class="is-done"><i><svg><use href="#i-box"/></svg></i><div><b>آماده‌سازی و بسته‌بندی</b><small>۲۳ آبان ۱۴۰۵، ساعت ۱۲:۴۰</small><p>کالاها در انبار آمل بسته‌بندی شد و بارنامه صادر گردید.</p></div></li>
              <li class="is-done"><i><svg><use href="#i-truck"/></svg></i><div><b>تحویل به پست پیشتاز</b><small>۲۴ آبان ۱۴۰۵، ساعت ۰۸:۱۰</small><p>کد رهگیری مرسوله: <bdo dir="ltr">۲۴۵۶۷۸۹۰۱۲۳۴۵</bdo></p></div></li>
              <li class="is-active"><i><svg><use href="#i-pin"/></svg></i><div><b>در حال ارسال به مقصد</b><small>آخرین بروزرسانی: امروز، ساعت ۰۹:۳۵</small><p>مرسوله در مرکز توزیع آمل است و امروز تحویل داده می‌شود.</p></div></li>
            </ol>
          </section>

        </div>

        <!-- ============ ستون کنار ============ -->
        <aside class="od-side">
          <section class="od-card od-summary" aria-labelledby="od-summary-title">
            <h2 id="od-summary-title">خلاصه پرداخت</h2>
            <div class="od-sum-row"><span>جمع کالاها (۴ عدد)</span><b>۳٫۸۱۸٫۰۰۰ تومان</b></div>
            <div class="od-sum-row is-discount"><span>تخفیف فروشگاه</span><b>۸۲۸٫۰۰۰ − تومان</b></div>
            <div class="od-sum-row"><span>هزینه ارسال</span><b>۳۵٫۰۰۰ تومان</b></div>
            <div class="od-sum-row is-muted"><span>کد تخفیف</span><b>—</b></div>
            <div class="od-total-row"><span>مبلغ پرداخت‌شده</span><b>۳٫۰۲۵٫۰۰۰ تومان</b></div>
            <div class="od-actions">
              <button class="btn btn-outline btn-sm" type="button" data-toast="درخواست مرجوعی سفارش ثبت شد (نمایشی)"><svg><use href="#i-refresh"/></svg>درخواست مرجوعی</button>
              <button class="btn btn-outline btn-sm" type="button" id="od-cancel"><svg><use href="#i-close"/></svg>لغو سفارش</button>
              <button class="btn btn-outline btn-sm" type="button" data-toast="کالاهای این سفارش به سبد خرید اضافه شدند"><svg><use href="#i-refresh"/></svg>خرید مجدد</button>
              <button class="btn btn-outline btn-sm" type="button" data-print><svg><use href="#i-download"/></svg>چاپ فاکتور</button>
            </div>
          </section>

          <section class="od-card od-help">
            <span class="help-icon"><svg><use href="#i-headset"/></svg></span>
            <h3>پشتیبانی این سفارش</h3>
            <p>شنبه تا پنج‌شنبه، ساعت ۶:۳۰ تا ۲۰:۳۰ — کارشناسان فروشگاه رنگ صادقی پاسخگوی شما هستند.</p>
            <a class="btn btn-primary btn-block" href="tel:09113294688">۰۹۱۱۳۲۹۴۶۸۸</a>
            <a class="text-link" href="contact.html">ارسال پیام به پشتیبانی <svg><use href="#i-chevron-left"/></svg></a>
          </section>

          <section class="od-card od-note">
            <h3><svg><use href="#i-info"/></svg>نکته‌های مهم</h3>
            <ul>
              <li>امکان مرجوعی کالای سالم تا ۷ روز پس از تحویل وجود دارد.</li>
              <li>برای پیگیری مرسوله، کد رهگیری پستی را در سایت پست وارد کنید.</li>
              <li>فاکتور رسمی این سفارش قابل چاپ و دانلود است.</li>
            </ul>
          </section>
        </aside>
      </div>
    </div>
  </main>
"""


if __name__ == "__main__":
    page("shop.html", "فروشگاه رنگ و ابزار صادقی | خرید رنگ، پتینه و ابزار",
         "فروشگاه رنگ صادقی؛ خرید رنگ ساختمانی، پتینه، میکروسمنت، رنگ هنری و ابزار با فیلتر پیشرفته.",
         "shop", SHOP_CONTENT, page_key="shop")

    page("categories.html", "دسته بندی محصولات | فروشگاه رنگ صادقی",
         "دسته بندی محصولات فروشگاه رنگ صادقی؛ رنگ ساختمانی، هنری، پتینه، شابلون و ابزار.",
         "categories", categories_content(), page_key="categories")

    page("product.html", "تار عنکبوتی ۵۰۰ گرمی گلد ساندورا | فروشگاه رنگ صادقی",
         "خرید تار عنکبوتی ۵۰۰ گرمی گلد ساندورا با قیمت و مشخصات کامل؛ گالری تصاویر، نظرات و محصولات مرتبط.",
         "shop", PRODUCT_CONTENT, page_key="product")

    page("blog.html", "وبلاگ رنگ صادقی | مقالات تخصصی رنگ، پتینه و هنر",
         "مجله رنگ صادقی؛ آموزش رنگ، پتینه، میکروسمنت، رزین و ابزار نقاشی.",
         "blog", blog_content(), page_key="blog")

    page("blog-single.html", "پتینه چیست؟ انواع، کاربرد و تفاوت با رنگ دیوار | رنگ صادقی",
         "پتینه چیست و چه تفاوتی با رنگ دیوار دارد؟ انواع پتینه، مراحل اجرا، جدول مقایسه و نکات اجرایی.",
         "blog", ARTICLE_CONTENT, page_key="article")

    page("about.html", "درباره ما | فروشگاه رنگ صادقی",
         "درباره فروشگاه رنگ صادقی؛ مرجع جامع رنگ ساختمانی، صنعتی، پتینه، هنری و ابزار.",
         "about", ABOUT_CONTENT, page_key="about")

    page("contact.html", "تماس با ما | رنگ صادقی: رنگ، رزین، ابزار نقاشی",
         "تماس با فروشگاه رنگ صادقی؛ آدرس، شماره تماس، ساعت کاری و فرم ارسال پیام.",
         "contact", CONTACT_CONTENT, page_key="contact")

    page("checkout.html", "تسویه حساب | فروشگاه رنگ صادقی",
         "تسویه حساب چندمرحله‌ای؛ انتخاب آدرس، شیوه و زمان ارسال، روش پرداخت، کد تخفیف و ثبت نهایی سفارش.",
         "shop", CHECKOUT_CONTENT, page_key="checkout", body_class=' class="page-checkout"')

    page("cart.html", "سبد خرید | فروشگاه رنگ صادقی",
         "مشاهده و ویرایش سبد خرید؛ تعداد، حذف کالا، کد تخفیف و خلاصه سفارش.",
         "shop", CART_CONTENT.replace("{CART_STEPS}", co_steps(0)), page_key="cart", body_class=' class="page-cart"')

    page("order-details.html", "جزئیات سفارش | فروشگاه رنگ صادقی",
         "جزئیات کامل سفارش: وضعیت مرسوله و کد رهگیری، اطلاعات ارسال و پرداخت، اقلام سفارش، فاکتور خرید و گردش سفارش.",
         "", ORDER_CONTENT, page_key="order", body_class=' class="page-order"')

    page("account.html", "حساب کاربری | فروشگاه رنگ صادقی",
         "پیشخوان حساب کاربری؛ سفارش‌ها، آدرس‌ها، علاقه‌مندی‌ها، کیف پول و اطلاعات حساب.",
         "", ACCOUNT_CONTENT, page_key="account", body_class=' class="page-account"')

    page("stores.html", "فروشگاه های ما | رنگ صادقی و نقشینو",
         "دو فروشگاه مجموعه رنگ صادقی: rangsadeghy.com و naghshinoart.com.",
         "stores", STORES_CONTENT, page_key="stores")

    print("همه صفحات ساخته شد.")
