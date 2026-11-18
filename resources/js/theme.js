/* ==========================================================================
   رنگ صادقی | کنترلر مدرن فرانت‌اند وردپرس و ووکامرس (Vanilla ES2022 - بدون jQuery)
   حفظ ۱۰۰٪ رفتارها و تعاملات نسخه HTML + اتصال کامل AJAX به ووکامرس
   ========================================================================== */
(function () {
  'use strict';

  const WP = window.RS_WP || {};
  const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

  const FA_DIGITS = '۰۱۲۳۴۵۶۷۸۹';
  const toFa = val => String(val ?? '').replace(/\d/g, d => FA_DIGITS[d]);
  const toEn = val => String(val ?? '')
    .replace(/[۰-۹]/g, d => String(FA_DIGITS.indexOf(d)))
    .replace(/[٠-٩]/g, d => String('٠١٢٣٤٥٦٧٨٩'.indexOf(d)));
  const formatPrice = num => toFa(Math.round(Number(num) || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '٫'));

  let toastTimer = null;
  function showToast(message, icon = 'i-check') {
    const toast = $('#toast');
    if (!toast || !message) return;
    const useEl = toast.querySelector('use');
    if (useEl) useEl.setAttribute('href', '#' + icon);
    const span = toast.querySelector('span');
    if (span) span.textContent = message;
    toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('show'), 3200);
  }

  async function wpAjax(action, data = {}, method = 'POST') {
    if (method === 'GET') {
      const url = new URL(WP.ajaxUrl, window.location.origin);
      url.searchParams.set('action', action);
      Object.entries(data).forEach(([k, v]) => url.searchParams.set(k, v));
      const res = await fetch(url.toString(), { credentials: 'same-origin' });
      return res.json();
    }

    const fd = new FormData();
    fd.append('action', action);
    fd.append('nonce', WP.nonce || '');
    Object.entries(data).forEach(([k, v]) => {
      if (Array.isArray(v)) {
        v.forEach(item => fd.append(k + '[]', item));
      } else if (v && typeof v === 'object') {
        Object.entries(v).forEach(([sk, sv]) => fd.append(`${k}[${sk}]`, sv));
      } else if (v !== undefined && v !== null) {
        fd.append(k, v);
      }
    });

    const res = await fetch(WP.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: fd });
    return res.json();
  }

  /* -------------------- مدیریت دراورها، مودال‌ها و اورلی -------------------- */
  function closeLayers() {
    $$('.side-drawer.open').forEach(d => { d.classList.remove('open'); d.setAttribute('aria-hidden', 'true'); });
    $$('.modal.open').forEach(m => { m.classList.remove('open'); m.setAttribute('aria-hidden', 'true'); });
    $('#shop-sidebar')?.classList.remove('open', 'is-open');
    $('#page-overlay')?.classList.remove('active');
    document.body.classList.remove('no-scroll');
  }

  function showOverlay() {
    $('#page-overlay')?.classList.add('active');
    document.body.classList.add('no-scroll');
  }

  function openDrawer(id) {
    const el = document.getElementById(id);
    if (!el) return;
    closeLayers();
    el.classList.add('open');
    el.setAttribute('aria-hidden', 'false');
    showOverlay();
    $('.drawer-close', el)?.focus();
  }

  function openModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    closeLayers();
    el.classList.add('open');
    el.setAttribute('aria-hidden', 'false');
    showOverlay();
    $('.modal-close', el)?.focus();
  }

  /* -------------------- هدر چسبان و بازگشت به بالا -------------------- */
  function initStickyAndScroll() {
    const mainHeader = $('#main-header');
    const backBtn = $('#back-to-top');
    const readBar = $('#read-progress span');

    const onScroll = () => {
      const y = window.scrollY || 0;
      if (mainHeader && WP.stickyHeader !== false) {
        mainHeader.classList.toggle('is-sticky', y > 110);
        const h = mainHeader.classList.contains('is-sticky') ? Math.round(mainHeader.getBoundingClientRect().height) : 0;
        document.documentElement.style.setProperty('--sticky-offset', h + 'px');
      }
      if (backBtn) backBtn.classList.toggle('show', y > 380);
      if (readBar) {
        const docH = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
        readBar.style.width = Math.min(100, Math.max(0, (y / docH) * 100)) + '%';
      }
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);
    onScroll();

    backBtn?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }

  /* -------------------- مگامنو و منوی موبایل -------------------- */
  function initNavigation() {
    const mobileSearch = $('#mobile-search-box');
    $('.hx-mobile-search-toggle')?.addEventListener('click', e => {
      if (!mobileSearch) return;
      mobileSearch.hidden = !mobileSearch.hidden;
      e.currentTarget.setAttribute('aria-expanded', String(!mobileSearch.hidden));
      if (!mobileSearch.hidden) $('input[type=search]', mobileSearch)?.focus();
    });
    const catBtn = $('#category-button');
    const catNav = $('.category-nav');

    catBtn?.addEventListener('click', e => {
      e.stopPropagation();
      if (!catNav) return;
      const open = catNav.classList.toggle('open');
      catBtn.setAttribute('aria-expanded', String(open));
    });

    $$('.mobile-menu-button').forEach(btn => btn.addEventListener('click', () => openDrawer('mobile-menu-drawer')));

    $$('[data-drawer-tab]').forEach(tabBtn => {
      tabBtn.addEventListener('click', () => {
        const isCats = tabBtn.dataset.drawerTab === 'cats';
        $$('[data-drawer-tab]').forEach(b => b.classList.toggle('active', b === tabBtn));
        const navEl = $('#mobile-drawer-nav');
        const catsEl = $('#mobile-drawer-categories');
        if (navEl) navEl.hidden = isCats;
        if (catsEl) catsEl.hidden = !isCats;
      });
    });

    $$('.cart-button').forEach(btn => btn.addEventListener('click', e => {
      e.preventDefault();
      if (window.matchMedia('(min-width:1025px)').matches && WP.cartUrl) {
        if (!document.body.classList.contains('page-cart')) {
          window.location.href = WP.cartUrl;
        }
        return;
      }
      openDrawer('cart-drawer');
    }));
  }

  /* -------------------- اسلایدر هیرو و شمارش معکوس و کاروسل‌ها -------------------- */
  function initHeroAndCarousels() {
    const hero = $('.hero-slider');
    const slides = $$('.hero-slide');
    if (hero && slides.length > 1) {
      let idx = 0;
      let timer = null;
      let touchX = 0;
      const delay = Math.max(2000, Number(WP.heroAutoplayMs || 5000));

      const show = next => {
        idx = (next + slides.length) % slides.length;
        slides.forEach((s, i) => s.classList.toggle('active', i === idx));
        $$('.hero-dots button').forEach((d, i) => d.classList.toggle('active', i === idx));
      };
      const start = () => { clearInterval(timer); timer = setInterval(() => show(idx + 1), delay); };

      $('.hero-prev', hero)?.addEventListener('click', () => { show(idx - 1); start(); });
      $('.hero-next', hero)?.addEventListener('click', () => { show(idx + 1); start(); });
      $$('.hero-dots button', hero).forEach(d => d.addEventListener('click', () => { show(Number(d.dataset.slideTo || 0)); start(); }));
      hero.addEventListener('mouseenter', () => clearInterval(timer));
      hero.addEventListener('mouseleave', start);
      hero.addEventListener('touchstart', e => { touchX = e.changedTouches[0].clientX; }, { passive: true });
      hero.addEventListener('touchend', e => {
        const dx = e.changedTouches[0].clientX - touchX;
        if (Math.abs(dx) > 45) { show(idx + (dx > 0 ? 1 : -1)); start(); }
      }, { passive: true });
      start();
    }

    /* شمارش معکوس پیشنهاد شگفت‌انگیز */
    const cdBox = $('#hx-countdown');
    if (cdBox) {
      const cells = { h: $('[data-cd="h"]', cdBox), m: $('[data-cd="m"]', cdBox), s: $('[data-cd="s"]', cdBox) };
      const padFa = v => toFa(String(v).padStart(2, '0'));
      const tick = () => {
        const now = new Date();
        const customEnd = cdBox.dataset.end || WP.dealEndDatetime;
        let end = customEnd ? new Date(customEnd) : null;
        if (!end || Number.isNaN(end.getTime()) || end <= now) {
          end = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1, 0, 0, 0);
        }
        const left = Math.max(0, Math.floor((end - now) / 1000));
        if (cells.h) cells.h.textContent = padFa(Math.floor(left / 3600));
        if (cells.m) cells.m.textContent = padFa(Math.floor((left % 3600) / 60));
        if (cells.s) cells.s.textContent = padFa(left % 60);
      };
      tick();
      setInterval(tick, 1000);
    }

    /* دکمه‌های اسکرول کاروسل محصولات */
    $$('[data-carousel]').forEach(btn => {
      btn.addEventListener('click', () => {
        const track = document.getElementById(btn.dataset.carousel);
        if (!track) return;
        const step = Math.max(260, Math.round(track.clientWidth * 0.72));
        const dir = btn.dataset.dir === 'prev' ? 1 : -1; // در RTL اسکرول معکوس است
        track.scrollBy({ left: dir * step, behavior: 'smooth' });
      });
    });
  }

  /* -------------------- جستجوی زنده AJAX در هدر -------------------- */
  function initLiveSearch() {
    const popular = Array.isArray(WP.popularSearches) && WP.popularSearches.length
      ? WP.popularSearches
      : ['مولتی سورفیس ریچ', 'تار عنکبوتی ساندورا', 'اکریلیک سوداکو', 'تکسچر کارن'];

    $$('[data-search-form]').forEach(form => {
      const box = form.closest('.search-box');
      const input = $('input[type="search"]', form);
      const results = $('.search-results', box);
      if (!box || !input || !results) return;

      let debounceId = null;

      const renderPopular = () => {
        results.innerHTML = `<div class="search-result-title">درخواست‌های محبوب</div><div class="popular-searches">${
          popular.map(term => `<button type="button" data-search-term="${escapeHtml(term)}">${escapeHtml(term)}</button>`).join('')
        }</div>`;
        box.classList.add('open');
      };

      const runQuery = async () => {
        const q = input.value.trim();
        if (!q) { renderPopular(); return; }
        try {
          const res = await wpAjax('rs_live_search', { q }, 'GET');
          const items = res?.data?.items || [];
          if (items.length) {
            results.innerHTML = `<div class="search-result-title">${res.data.count_fa} نتیجه برای «${escapeHtml(q)}»</div>` +
              items.map(item => `<a class="search-result-item" href="${escapeHtml(item.url)}"><img src="${escapeHtml(item.image)}" alt=""><span>${escapeHtml(item.title)}</span><strong>${escapeHtml(item.price_formatted)}</strong></a>`).join('');
          } else {
            results.innerHTML = '<div class="search-empty">محصولی با این عبارت پیدا نشد.</div>';
          }
          box.classList.add('open');
        } catch {
          /* نادیده‌گرفتن خطای شبکه در جستجوی زنده */
        }
      };

      input.addEventListener('focus', () => { if (!input.value.trim()) renderPopular(); else runQuery(); });
      input.addEventListener('input', () => {
        clearTimeout(debounceId);
        debounceId = setTimeout(runQuery, 240);
      });
    });
  }

  /* -------------------- سبد خرید AJAX + همگام‌سازی زنده UI -------------------- */
  function syncCartDom(payload) {
    if (!payload) return;
    ['cart-checkout', 'cart-mobile-checkout'].forEach(id => {
      const link = document.getElementById(id);
      if (!link) return;
      link.classList.toggle('is-disabled', !payload.count);
      link.setAttribute('aria-disabled', String(!payload.count));
      if (!payload.count) link.setAttribute('tabindex', '-1');
      else link.removeAttribute('tabindex');
    });
    const mobileBar = $('.cart-mobile-bar');
    if (mobileBar) mobileBar.classList.toggle('is-empty', !payload.count);
    const clear = $('#cart-page-clear');
    if (clear) clear.hidden = !payload.count;
    $$('.cart-count').forEach(el => { el.textContent = payload.count_fa || '۰'; });
    const drawerTotal = $('#cart-total');
    if (drawerTotal) drawerTotal.textContent = payload.subtotal_fa || '۰ تومان';

    const drawerItems = $('#cart-items');
    if (drawerItems) {
      if (Array.isArray(payload.items) && payload.items.length) {
        drawerItems.innerHTML = payload.items.map(ci => `
          <div class="cart-item" data-cart-key="${escapeHtml(ci.key)}">
            <img src="${escapeHtml(ci.image)}" alt="${escapeHtml(ci.title)}">
            <div>
              <h3>${escapeHtml(ci.title)}</h3>
              <div class="cart-item-price">${escapeHtml(ci.line_total_fa)} تومان</div>
              <div class="cart-item-actions">
                <button type="button" data-cart-action="plus" data-id="${escapeHtml(ci.key)}" aria-label="افزایش"><svg><use href="#i-plus"/></svg></button>
                <span>${escapeHtml(ci.quantity_fa)}</span>
                <button type="button" data-cart-action="minus" data-id="${escapeHtml(ci.key)}" aria-label="کاهش"><svg><use href="#i-minus"/></svg></button>
              </div>
            </div>
            <button type="button" class="remove-cart-item" data-cart-action="remove" data-id="${escapeHtml(ci.key)}" aria-label="حذف محصول"><svg><use href="#i-trash"/></svg></button>
          </div>`).join('');
      } else {
        drawerItems.innerHTML = `<div class="cart-empty"><svg><use href="#i-bag"/></svg><p>سبد خرید شما خالی است.</p><a class="quick-add" href="${escapeHtml(WP.shopUrl || '/')}">مشاهده محصولات</a></div>`;
      }
    }

    /* اگر در صفحه سبد خرید هستیم، خطوط و خلاصه سفارش را هم زنده به‌روز کن */
    const cartLines = $('#cart-page-items');
    if (cartLines) {
      const pageCount = $('#cart-page-count');
      if (pageCount) pageCount.textContent = payload.count ? `(${payload.count_fa} کالا)` : '';
      const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
      setTxt('summary-count', payload.count_fa || '۰');
      setTxt('summary-subtotal', payload.original_fa || '۰ تومان');
      setTxt('summary-discount', payload.discount_fa || '۰ تومان');
      setTxt('summary-shipping', payload.shipping_fa || 'پس از انتخاب آدرس');
      setTxt('summary-total', payload.total_fa || '۰ تومان');
      setTxt('cart-mobile-total', payload.total_fa || '۰ تومان');

      const freeText = $('#free-ship-text');
      const freeBar = $('#free-ship-progress');
      if (freeText) {
        freeText.textContent = !payload.count
          ? 'با افزودن کالا، هزینه ارسال محاسبه می‌شود.'
          : payload.free_remaining > 0
            ? `${payload.free_remaining_fa} تومان تا سقف پیشنهادی ارسال رایگان باقی مانده است؛ شرایط نهایی در تسویه حساب مشخص می‌شود.`
            : 'به سقف پیشنهادی رسیده‌اید؛ شرایط ارسال در تسویه حساب مشخص می‌شود.';
      }
      if (freeBar) freeBar.style.width = (payload.count ? payload.free_percent : 0) + '%';

      if (!payload.items || !payload.items.length) {
        cartLines.innerHTML = `<div class="cart-empty-lg"><svg><use href="#i-bag"/></svg><h3>سبد خرید شما خالی است</h3><p>هنوز محصولی به سبد خرید اضافه نکرده‌اید.</p><a class="btn btn-primary" href="${escapeHtml(WP.shopUrl || '/')}"><svg><use href="#i-grid"/></svg>مشاهده محصولات</a></div>`;
      } else {
        cartLines.innerHTML = payload.items.map(line => `
          <article class="cart-line" data-line="${escapeHtml(line.key)}">
            <a class="cart-line-img" href="${escapeHtml(line.url)}"><img src="${escapeHtml(line.image)}" alt="${escapeHtml(line.title)}" loading="lazy"></a>
            <div class="cart-line-body">
              <a class="cart-line-title" href="${escapeHtml(line.url)}">${escapeHtml(line.title)}</a>
              <div class="cart-line-meta"><span>${escapeHtml(line.cat)}</span><span aria-hidden="true">•</span><span>کد ${escapeHtml(line.sku_fa)}</span></div>
              <div class="cart-line-flags">
                <span class="mini-flag">${line.in_stock ? 'موجود در انبار' : 'ناموجود'}</span>
                ${line.discount > 0 ? `<span class="mini-flag is-sale">${escapeHtml(line.discount_fa)}٪ تخفیف</span>` : ''}
                <span class="mini-flag is-muted">ارسال از انبار آمل</span>
              </div>
              <div class="cart-line-actions">
                <div class="qty-stepper qty-sm">
                  <button type="button" data-cart-action="plus" data-id="${escapeHtml(line.key)}" aria-label="افزایش"><svg><use href="#i-plus"/></svg></button>
                  <input value="${escapeHtml(line.quantity_fa)}" readonly aria-label="تعداد">
                  <button type="button" data-cart-action="minus" data-id="${escapeHtml(line.key)}" aria-label="کاهش"><svg><use href="#i-minus"/></svg></button>
                </div>
                <button class="cart-line-btn is-danger" type="button" data-cart-action="remove" data-id="${escapeHtml(line.key)}"><svg><use href="#i-trash"/></svg>حذف</button>
                <button class="cart-line-btn" type="button" data-wish="${escapeHtml(line.parent_id)}"><svg><use href="#i-heart"/></svg>ذخیره در علاقه‌مندی</button>
              </div>
            </div>
            <div class="cart-line-price">
              <span class="old">${escapeHtml(line.line_old_fa || '')}</span>
              <b>${escapeHtml(line.line_total_fa)}</b>
              <small>تومان</small>
            </div>
          </article>`).join('');
      }
    }
  }

  async function addProductToCart(productId, quantity = 1, variationId = 0, variation = {}, consent = false) {
    const res = await wpAjax('rs_cart_add', {
      product_id: productId,
      quantity,
      variation_id: variationId,
      variation,
      rs_product_consent: consent ? 'yes' : ''
    });

    if (res.success) {
      syncCartDom(res.data);
      showToast(res.data.message || 'به سبد خرید اضافه شد');
      return;
    }

    showToast(res.data?.message || 'خطا در افزودن به سبد خرید', 'i-info');
  }

  async function mutateCart(cartKey, cartAction) {
    try {
      const res = await wpAjax('rs_cart_update', { cart_key: cartKey, cart_action: cartAction });
      if (res.success) {
        syncCartDom(res.data);
        if (res.data.message) showToast(res.data.message);
      } else showToast(res.data?.message || 'امکان تغییر سبد خرید وجود ندارد.', 'i-info');
    } catch { showToast('ارتباط با فروشگاه برقرار نشد.', 'i-info'); }
  }

  /* -------------------- علاقه‌مندی‌ها (کوکی + متای کاربر + مودال سریع) -------------------- */
  const wishSet = new Set((WP.wishlist || []).map(String));

  function persistWishlistCookie() {
    const arr = Array.from(wishSet);
    document.cookie = 'rs_wishlist=' + encodeURIComponent(JSON.stringify(arr)) + '; path=/; max-age=' + (60 * 60 * 24 * 90) + '; SameSite=Lax';
  }

  function refreshWishlistButtons() {
    const cnt = $('#wishlist-count');
    if (cnt) cnt.textContent = toFa(wishSet.size);
    $$('[data-wish]').forEach(btn => {
      if (btn.classList.contains('remove-wish')) return;
      const active = wishSet.has(String(btn.dataset.wish));
      btn.classList.toggle('active', active);
      btn.setAttribute('aria-pressed', String(active));
    });
  }

  async function toggleWishlist(id) {
    const sid = String(id);
    if (wishSet.has(sid)) wishSet.delete(sid);
    else wishSet.add(sid);
    refreshWishlistButtons();

    try {
      const res = await wpAjax('rs_wishlist_toggle', { product_id: sid });
      if (res.success && Array.isArray(res.data.wishlist)) {
        wishSet.clear();
        res.data.wishlist.forEach(x => wishSet.add(String(x)));
        persistWishlistCookie();
        refreshWishlistButtons();
        showToast(res.data.message, 'i-heart');
      }
    } catch {
      persistWishlistCookie();
      showToast(wishSet.has(sid) ? 'به علاقه‌مندی اضافه شد' : 'از علاقه‌مندی حذف شد', 'i-heart');
    }

    if ($('#quick-modal.open .wishlist-view')) openWishlistModal();
  }

  async function openWishlistModal() {
    const content = $('#quick-modal-content');
    if (!content) return;
    content.innerHTML = '<div class="wishlist-view"><h2>لیست علاقه‌مندی‌ها</h2><p style="padding:16px 0">در حال بارگذاری…</p></div>';
    openModal('quick-modal');

    const res = await wpAjax('rs_wishlist_items', { ids: Array.from(wishSet) });
    const items = res?.data?.items || [];
    content.innerHTML = `<div class="wishlist-view"><h2>لیست علاقه‌مندی‌ها</h2>${
      items.length
        ? `<div class="wishlist-list">${items.map(p => `
            <div class="wishlist-row">
              <img src="${escapeHtml(p.image)}" alt="${escapeHtml(p.title)}">
              <a class="wishlist-name" href="${escapeHtml(p.url)}">${escapeHtml(p.title)}</a>
              <strong>${escapeHtml(p.price)}</strong>
              <button class="quick-add" type="button" data-add="${escapeHtml(p.id)}">افزودن به سبد</button>
              <button class="remove-wish" type="button" data-wish="${escapeHtml(p.id)}" aria-label="حذف"><svg><use href="#i-trash"/></svg></button>
            </div>`).join('')}</div>`
        : `<div class="cart-empty"><svg><use href="#i-heart"/></svg><p>هنوز محصولی به علاقه‌مندی‌ها اضافه نکرده‌اید.</p><a class="quick-add" href="${escapeHtml(WP.shopUrl || '/')}">مشاهده محصولات</a></div>`
    }</div>`;
  }

  /* -------------------- فیلتر زنده AJAX فروشگاه -------------------- */
  function initShopPage() {
    const grid = $('#shop-grid');
    const sidebar = $('#shop-sidebar');
    const pagination = $('#shop-pagination');
    if (!grid || !sidebar || !pagination) return;
    const buildUrl = () => {
      const url = new URL(window.location.href);
      url.pathname = url.pathname.replace(/\/page\/\d+\/?$/, '/');
      Array.from(url.searchParams.keys()).forEach(k => {
        if (['cat','brand','min_price','max_price','sale','in_stock','rs_sort','view','paged'].includes(k) || k.startsWith('rs_attr_')) url.searchParams.delete(k);
      });
      const cats = $$('input[name="cat"]:checked', sidebar).map(i => i.value);
      const brands = $$('input[name="brand"]:checked', sidebar).map(i => i.value);
      if (cats.length) url.searchParams.set('cat', cats.join(','));
      if (brands.length) url.searchParams.set('brand', brands.join(','));
      const attrs = {};
      $$('[data-attribute]:checked', sidebar).forEach(el => (attrs[el.dataset.attribute] ||= []).push(el.value));
      Object.entries(attrs).forEach(([key, values]) => url.searchParams.set('rs_attr_' + key, values.join(',')));
      const min = toEn($('#filter-min')?.value || '').replace(/\D+/g, '');
      const max = toEn($('#filter-max')?.value || '').replace(/\D+/g, '');
      if (min) url.searchParams.set('min_price', min);
      if (max) url.searchParams.set('max_price', max);
      if ($('#filter-sale')?.checked) url.searchParams.set('sale', '1');
      if ($('#filter-stock')?.checked) url.searchParams.set('in_stock', '1');
      const sort = $('#sort-select')?.value;
      if (sort && sort !== 'default') url.searchParams.set('rs_sort', sort);
      const view = $('.view-toggles button.active')?.dataset.view;
      if (view && view !== 'grid') url.searchParams.set('view', view);
      return url;
    };
    const reload = () => { window.location.href = buildUrl().toString(); };
    sidebar.addEventListener('change', e => { if (e.target.matches('input[type="checkbox"]')) reload(); });
    sidebar.addEventListener('keydown', e => { if (e.key === 'Enter' && e.target.matches('#filter-min,#filter-max')) { e.preventDefault(); reload(); } });
    ['#filter-min', '#filter-max'].forEach(sel => $(sel)?.addEventListener('change', reload));
    $('#sort-select')?.addEventListener('change', reload);
    $$('.view-toggles [data-view]').forEach(btn => btn.addEventListener('click', () => {
      $$('.view-toggles [data-view]').forEach(b => b.classList.toggle('active', b === btn)); reload();
    }));
    $('[data-open-filters]')?.addEventListener('click', () => {
      sidebar.classList.add('open', 'is-open'); showOverlay();
      $('.rs-close-filters', sidebar)?.focus();
    });
    sidebar.addEventListener('click', e => { if (e.target.closest('.rs-close-filters')) closeLayers(); });
    document.addEventListener('click', e => {
      const btn = e.target.closest('[data-clear]');
      if (!btn) return;
      const spec = btn.dataset.clear;
      if (spec === 'all') {
        $$('input[type="checkbox"]', sidebar).forEach(el => { el.checked = false; });
        if ($('#filter-min')) $('#filter-min').value = '';
        if ($('#filter-max')) $('#filter-max').value = '';
      } else if (spec.startsWith('cat:') || spec.startsWith('brand:')) {
        const [name, val] = spec.split(':');
        $$('input[name="' + name + '"]', sidebar).forEach(el => { if (el.value === val) el.checked = false; });
      } else if (spec === 'sale' && $('#filter-sale')) $('#filter-sale').checked = false;
      else if (spec === 'stock' && $('#filter-stock')) $('#filter-stock').checked = false;
      if (spec === 'q') { const u = buildUrl(); u.searchParams.delete('s'); u.searchParams.delete('q'); location.href = u.toString(); return; }
      reload();
    });
    let busy = false;
    const loadMore = async () => {
      const link = $('.rs-load-more', pagination);
      if (!link || busy) return;
      busy = true; link.setAttribute('aria-busy', 'true');
      try {
        const response = await fetch(link.href, {credentials: 'same-origin'});
        if (!response.ok) throw new Error('request failed');
        const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
        const nextGrid = doc.querySelector('#shop-grid');
        const nextPagination = doc.querySelector('#shop-pagination');
        if (!nextGrid || !nextPagination) throw new Error('invalid page');
        Array.from(nextGrid.children).forEach(el => grid.appendChild(el));
        pagination.innerHTML = nextPagination.innerHTML;
        refreshWishlistButtons();
        observeMore();
      } catch { window.location.href = link.href; }
      finally { busy = false; }
    };
    let observer;
    function observeMore() {
      observer?.disconnect();
      const link = $('.rs-load-more', pagination);
      if (link && pagination.dataset.mode === 'infinite' && 'IntersectionObserver' in window) {
        observer = new IntersectionObserver(entries => { if (entries.some(e => e.isIntersecting)) loadMore(); }, {rootMargin:'200px'});
        observer.observe(link);
      }
    }
    pagination.addEventListener('click', e => { if (e.target.closest('.rs-load-more')) { e.preventDefault(); loadMore(); } });
    observeMore();
  }

  /* -------------------- صفحه تک‌محصول -------------------- */
  function initSingleProduct() {
    const wrap = $('[data-single-product]');
    if (!wrap) return;

    const sticky = $('.sticky-buy');
    const price = $('.price-block', wrap);
    if (sticky && price) {
      const updateSticky = () => sticky.classList.toggle('is-visible', price.getBoundingClientRect().bottom < 0);
      window.addEventListener('scroll', updateSticky, { passive: true });
      window.addEventListener('resize', updateSticky);
      updateSticky();
    }
    const mainImg = $('#gallery-main img', wrap);
    $$('#gallery-thumbs [data-thumb]', wrap).forEach(btn => {
      btn.addEventListener('click', () => {
        if (mainImg) mainImg.src = btn.dataset.thumb;
        $$('#gallery-thumbs [data-thumb]', wrap).forEach(b => b.classList.toggle('active', b === btn));
      });
    });

    $('.gallery-zoom', wrap)?.addEventListener('click', () => {
      const content = $('#quick-modal-content');
      if (!content || !mainImg) return;
      content.innerHTML = `<img src="${escapeHtml(mainImg.src)}" alt="${escapeHtml(mainImg.alt)}" style="width:100%;max-height:80vh;object-fit:contain;border-radius:12px">`;
      openModal('quick-modal');
    });

    /* تعداد */
    const qtyInput = $('#qty-input', wrap);
    let qty = 1;
    const setQty = next => {
      qty = Math.max(1, Math.min(99, next));
      if (qtyInput) {
        qtyInput.value = toFa(qty);
        qtyInput.dataset.qty = String(qty);
      }
    };
    $('#qty-minus', wrap)?.addEventListener('click', () => setQty(qty - 1));
    $('#qty-plus', wrap)?.addEventListener('click', () => setQty(qty + 1));

    /* تطبیق همه گزینه‌ها با یک تنوع واقعی، پیش از اجازه خرید */
    let variations = [];
    try { variations = JSON.parse(wrap.dataset.variations || '[]'); } catch { variations = []; }
    const groups = $$('[data-wc-attribute]', wrap);
    const consent = $('#rs-product-consent');
    let selectedVarId = 0;
    const chosenAttributes = () => {
      const chosen = {};
      groups.forEach(g => { const active = $('.active', g); if (active) chosen[g.dataset.wcAttribute] = active.dataset.attrVal; });
      return chosen;
    };
    const updateVariation = () => {
      const chosen = chosenAttributes();
      const match = groups.length && Object.keys(chosen).length === groups.length
        ? variations.find(v => Object.keys(chosen).every(k => Object.prototype.hasOwnProperty.call(v.attributes, k) && (!v.attributes[k] || v.attributes[k] === chosen[k])))
        : null;
      selectedVarId = match?.variation_id || 0;
      if (match) {
        const pEl = $('#product-price'); if (pEl) pEl.textContent = match.price_fa;
        const stEl = $('#sticky-price'); if (stEl) stEl.textContent = match.price_fa + ' تومان';
        if (match.image && mainImg) mainImg.src = match.image;
      }
      $$('[data-single-add]').forEach(btn => {
        btn.disabled = !!btn.dataset.outOfStock || !!(consent && !consent.checked) || !!(groups.length && (!match || !match.is_in_stock || match.is_purchasable === false));
      });
    };
    $$('[data-wc-attribute] [data-attr-val]', wrap).forEach(btn => {
      btn.addEventListener('click', () => {
        const group = btn.closest('[data-wc-attribute]');
        $$('[data-attr-val]', group).forEach(b => b.classList.toggle('active', b === btn));
        updateVariation();
      });
    });
    consent?.addEventListener('change', updateVariation);
    updateVariation();
    $$('[data-single-add]').forEach(btn => {
      btn.addEventListener('click', () => {
        const chosen = {};
        $$('[data-wc-attribute]', wrap).forEach(g => {
          const active = $('.active', g);
          if (active) chosen[g.dataset.wcAttribute] = active.dataset.attrVal;
        });
        addProductToCart(btn.dataset.singleAdd, qty, selectedVarId, chosen, !!consent?.checked);
      });
    });

    /* تب‌های توضیحات / مشخصات / دیدگاه‌ها / ارسال */
    $$('.product-tabs .tab-nav [data-tab]').forEach(btn => {
      btn.addEventListener('click', () => {
        const target = btn.dataset.tab;
        $$('.product-tabs .tab-nav [data-tab]').forEach(b => b.classList.toggle('active', b === btn));
        $$('.product-tabs .tab-panel').forEach(p => p.classList.toggle('active', p.id === target));
      });
    });
  }

  // Progressive enhancement: WooCommerce remains responsible for checkout,
  // AJAX totals, gateway fields, validation and the final form submission.
  function initNativeCheckoutSteps() {
    const form = $('form.rs-native-checkout');
    if (!form) return;
    const address = $('[data-rs-checkout-panel="address"]', form);
    const review = $('[data-rs-checkout-panel="review"]', form);
    const back = $('[data-rs-checkout-back]', form);
    const next = $('[data-rs-checkout-next]', form);
    if (!address || !review || !back || !next) return;
    const shippingExtra = $('[data-rs-shipping-extra]', form);
    const paymentExtra = $('[data-rs-payment-extra]', form);
    // Relocate the genuine WooCommerce order fields, not copies of their inputs.
    const notes = $('.rs-order-notes', address);
    if (notes && shippingExtra) shippingExtra.appendChild(notes);
    const keys = ['address', 'shipping', 'payment'];
    let current = 0;
    form.classList.add('rs-steps-enabled');
    const picker = $('[data-rs-address-picker]', form);
    const savedList = $('[data-rs-saved-list]', form);
    const billingDetails = $('[data-rs-billing-details]', form);
    const newPanel = $('[data-rs-new-address]', form);
    const addButton = $('[data-rs-add-address]', form);
    const newError = $('[data-rs-new-error]', form);
    let applyingAddress = false;
    let selectedAddress = null;
    const changeField = (field, value) => {
      if (!field) return;
      if (field.tagName === 'SELECT') {
        const target = String(value || '').trim();
        const normalize = text => text.replace(/ي/g, 'ی').replace(/ك/g, 'ک').replace(/ة/g, 'ه').replace(/\u200c/g, ' ').trim().replace(/\s+/g, ' ').toLowerCase();
        const option = Array.from(field.options).find(o => o.value === target || normalize(o.textContent) === normalize(target));
        field.value = option ? option.value : '';
      } else field.value = value || '';
      field.dispatchEvent(new Event('input', { bubbles: true }));
      field.dispatchEvent(new Event('change', { bubbles: true }));
    };
    const applyAddress = (saved, targetShipping = false) => {
      if (!saved) return;
      applyingAddress = true;
      if (!targetShipping && saved.country && $('#billing_country', form)?.value !== saved.country) changeField($('#billing_country', form), saved.country);
      const prefixes = targetShipping ? ['shipping_'] : ['billing_'];
      if (!targetShipping && $('#ship-to-different-address-checkbox', form)?.checked) prefixes.push('shipping_');
      prefixes.forEach(prefix => {
        const values = { city: saved.city, postcode: toEn(saved.postal || '0000000000'), address_1: saved.address, address_2: saved.address2 || '', state: saved.province };
        Object.entries(values).forEach(([key, value]) => changeField(document.getElementById(prefix + key), value));
      });
      if (!targetShipping) {
        const names = (saved.name || '').trim().split(/\s+/);
        if (!$('#billing_first_name', form)?.value && names[0]) changeField($('#billing_first_name', form), names.shift());
        else names.shift();
        if (!$('#billing_last_name', form)?.value && names.length) changeField($('#billing_last_name', form), names.join(' '));
        if (!$('#billing_phone', form)?.value && saved.phone) changeField($('#billing_phone', form), toEn(saved.phone));
      }
      applyingAddress = false;
    };
    const syncSavedAddressFields = () => {
      const home = $('[data-rs-billing-field-home]', form), extra = $('[data-rs-contact-fields]', form), panel = $('[data-rs-contact-completion]', form);
      if (!home || !extra || !panel) return;
      const core = ['billing_first_name', 'billing_last_name', 'billing_company', 'billing_country', 'billing_state', 'billing_city', 'billing_postcode', 'billing_address_1', 'billing_address_2', 'billing_phone', 'billing_email'];
      const addressFields = ['billing_country', 'billing_state', 'billing_city', 'billing_postcode', 'billing_address_1', 'billing_address_2'];
      [...home.children, ...extra.children].forEach(row => {
        const field = row.querySelector('input,select,textarea');
        if (!field) return;
        const requiredMissing = row.classList.contains('validate-required') && !field.value.trim();
        const invalidEmail = field.id === 'billing_email' && field.value && !field.validity.valid;
        const unknownState = field.id === 'billing_state' && field.tagName === 'SELECT' && !!selectedAddress && !field.value && !!selectedAddress.province;
        const show = !core.includes(field.id) || unknownState || (requiredMissing && (selectedAddress || picker?.dataset.rsRequiresAddress !== '1' || !addressFields.includes(field.id))) || invalidEmail;
        (show ? extra : home).appendChild(row);
      });
      panel.hidden = !extra.children.length;
    };
    const selectAddress = label => {
      let saved;
      try { saved = JSON.parse(label.dataset.address); } catch (_) { return; }
      selectedAddress = saved;
      $$('.rs-saved-address', savedList).forEach(el => {
        const selected = el === label;
        el.classList.toggle('is-selected', selected);
        const radio = $('input[type="radio"]', el);
        if (radio) radio.checked = selected;
      });
      applyAddress(saved);
      syncSavedAddressFields();
      if (window.jQuery) window.jQuery(document.body).trigger('update_checkout');
      if (billingDetails) {
        billingDetails.open = true;
        const missing = $$('.validate-required input, .validate-required select, .validate-required textarea', billingDetails)
          .some(field => field.offsetParent !== null && !field.disabled && (field.type === 'checkbox' ? !field.checked : !field.value.trim()));
        billingDetails.open = missing;
      }
    };
    if (picker && savedList) {
      picker.hidden = false;
      savedList.addEventListener('change', event => {
        const label = event.target.closest('.rs-saved-address');
        if (label) selectAddress(label);
      });
      const cards = $$('.rs-saved-address', savedList);
      // Prefer a matching WooCommerce address; otherwise use the account's default.
      const currentAddress = $('#billing_address_1', form)?.value.trim();
      const initial = picker.dataset.rsSubmitted === '1' ? cards.find(el => $('input', el)?.value === picker.dataset.rsSelected) : (cards.find(el => { try { return currentAddress && JSON.parse(el.dataset.address).address === currentAddress; } catch (_) { return false; } }) || cards.find(el => { try { return JSON.parse(el.dataset.address).isDefault; } catch (_) { return false; } }) || cards[0]);
      if (initial) selectAddress(initial);
      else syncSavedAddressFields();
      form.addEventListener('input', event => {
        if (applyingAddress || !selectedAddress || event.target.closest('[data-rs-contact-completion]') || !/^(billing|shipping)_(state|city|postcode|address_1|address_2|country)$/.test(event.target.id)) return;
        selectedAddress = null;
        syncSavedAddressFields();
        $$('.rs-saved-address', savedList).forEach(el => { el.classList.remove('is-selected'); const radio = $('input', el); if (radio) radio.checked = false; });
      });
      $('#ship-to-different-address-checkbox', form)?.addEventListener('change', event => {
        if (event.target.checked && selectedAddress) applyAddress(selectedAddress, true);
      });
      const toggleNew = open => {
        if (!newPanel || !addButton) return;
        newPanel.hidden = !open;
        addButton.hidden = open;
        addButton.setAttribute('aria-expanded', String(open));
        if (newError) { newError.hidden = true; newError.textContent = ''; }
        if (open) $('[data-rs-new="title"]', newPanel)?.focus();
      };
      addButton?.addEventListener('click', () => toggleNew(true));
      $('[data-rs-cancel-address]', form)?.addEventListener('click', () => toggleNew(false));
      const saveButton = $('[data-rs-save-address]', form);
      const saveNew = async () => {
        if (!newPanel || !saveButton || !newError) return;
        const values = Object.fromEntries($$('[data-rs-new]', newPanel).map(el => [el.dataset.rsNew, el.value.trim()]));
        const invalid = ['title', 'name', 'phone', 'province', 'city', 'address'].find(key => !values[key]);
        if (invalid) { newError.textContent = 'فیلدهای ضروری آدرس را تکمیل کنید.'; newError.hidden = false; $(`[data-rs-new="${invalid}"]`, newPanel)?.focus(); return; }
        saveButton.disabled = true; newError.hidden = true;
        try {
          const response = await wpAjax('rs_checkout_address_save', values);
          if (!response.success || !response.data?.address) throw new Error(response.data?.message || 'ثبت آدرس ممکن نشد.');
          const item = response.data.address;
          const label = document.createElement('label');
          label.className = 'co-address rs-saved-address'; label.dataset.address = JSON.stringify(item);
          const radio = document.createElement('input'); radio.type = 'radio'; radio.name = 'rs_saved_address'; radio.value = item.id;
          const marker = document.createElement('span'); marker.className = 'co-radio'; marker.setAttribute('aria-hidden', 'true');
          const body = document.createElement('span'); body.className = 'co-address-body';
          const title = document.createElement('b'); title.textContent = item.title;
          if (item.isDefault) { const badge = document.createElement('span'); badge.className = 'default-chip'; badge.textContent = 'پیش‌فرض'; title.appendChild(badge); }
          const line = document.createElement('span'); line.className = 'co-address-text'; line.textContent = [item.province_label || item.province, item.city, item.address].filter(Boolean).join('، ');
          const meta = document.createElement('span'); meta.className = 'co-address-meta'; meta.textContent = [item.name, toFa(item.phone), item.postal ? 'کد پستی ' + toFa(item.postal) : ''].filter(Boolean).join(' — ');
          body.append(title, line, meta); label.append(radio, marker, body); savedList.appendChild(label);
          const empty = $('[data-rs-address-empty]', picker); if (empty) empty.hidden = true;
          selectAddress(label); toggleNew(false);
          $$('[data-rs-new]:not([type=hidden])', newPanel).forEach(el => { el.value = ''; });
          showToast(response.data.message || 'آدرس ثبت شد', 'i-check');
        } catch (error) { newError.textContent = error.message || 'ثبت آدرس ممکن نشد.'; newError.hidden = false; }
        finally { saveButton.disabled = false; }
      };
      saveButton?.addEventListener('click', saveNew);
      newPanel?.addEventListener('keydown', event => { if (event.key === 'Enter' && event.target.tagName !== 'TEXTAREA') { event.preventDefault(); saveNew(); } });
    }
    const self = $('#rs-receiver-self', form), receiverFields = $('[data-rs-receiver-fields]', form), selfInfo = $('[data-rs-self-info]', form);
    const recipientInvalid = () => {
      if (!self || self.checked) return null;
      const name = $('#rs-receiver-name', form), phone = $('#rs-receiver-phone', form);
      if (!name?.value.trim()) return name;
      const value = toEn(phone?.value || '').trim();
      if (!phone || value.replace(/\D/g, '').length < 7 || /[^\s#0-9_+\/().-]/.test(value)) return phone;
      return null;
    };
    const syncRecipient = () => {
      if (receiverFields && self) receiverFields.hidden = self.checked;
      if (selfInfo) {
        const name = [$('#billing_first_name', form)?.value, $('#billing_last_name', form)?.value].filter(Boolean).join(' ');
        const phone = $('#billing_phone', form)?.value;
        const text = [name, phone ? toFa(phone) : ''].filter(Boolean).join(' — ');
        selfInfo.textContent = text ? `(${text})` : '';
      }
    };
    self?.addEventListener('change', () => { syncRecipient(); if (!self.checked) $('#rs-receiver-name', form)?.focus(); });
    form.addEventListener('input', syncRecipient);
    syncRecipient();
    const syncShippingMessage = () => {
      $$('.woocommerce-shipping-methods li', form).forEach(item => {
        if (item.querySelector('.rs-shipping-icon')) return;
        const label = item.querySelector('label');
        if (!label) return;
        const icon = document.createElement('span');
        icon.className = 'co-option-icon rs-shipping-icon';
        icon.setAttribute('aria-hidden', 'true');
        icon.innerHTML = '<svg><use href="#i-truck"></use></svg>';
        item.insertBefore(icon, label);
      });
      const message = $('[data-rs-shipping-message]', form);
      if (message) message.hidden = current !== 1 || !!$('.woocommerce-checkout-review-order-table .shipping', form);
    };
    const selectedRates = () => $$('input[name^="shipping_method"]', form).filter(el => el.type === 'hidden' || el.checked);
    const syncDelivery = () => {
      const infos = selectedRates().map(el => el.closest('li')?.querySelector('.rs-rate-info')).filter(Boolean);
      const scheduled = infos.some(el => ['rs_store_courier', 'rs_store_pickup'].includes(el.dataset.rsRateKind));
      const pickupSelected = infos.some(el => el.dataset.rsRateKind === 'rs_store_pickup');
      const courierSelected = infos.some(el => el.dataset.rsRateKind === 'rs_store_courier');
      $$('[data-rs-pickup-day]', form).forEach(label => {
        const input = $('input[name="rs_delivery_day"]', label);
        const allowed = (!pickupSelected || label.dataset.rsPickupDay === '1') && (!courierSelected || label.dataset.rsCourierDay === '1');
        label.hidden = !allowed;
        if (input) { input.disabled = !allowed; if (!allowed) input.checked = false; }
      });
      const card = $('[data-rs-slot-card]', form), eta = $('[data-rs-eta-card]', form), text = $('[data-rs-eta-text]', form);
      if (card) card.hidden = !scheduled;
      const pickup = $('[data-rs-pickup-address]', form);
      if (pickup) pickup.hidden = !infos.some(el => el.dataset.rsRateKind === 'rs_store_pickup') || !pickup.textContent.trim();
      const others = infos.filter(el => !['rs_store_courier', 'rs_store_pickup'].includes(el.dataset.rsRateKind));
      if (eta) eta.hidden = !others.length;
      if (text) text.textContent = others.map(el => el.dataset.rsRateEta || 'زمان تحویل هنوز توسط فروشگاه تعیین نشده است.').join('؛ ');
      $$('[name="rs_delivery_day"], [name="rs_delivery_slot"]', form).forEach(el => el.closest('label')?.classList.toggle('is-selected', el.checked));
    };
    const deliveryMissing = () => {
      const card = $('[data-rs-slot-card]', form);
      return card && !card.hidden && (!$('[name="rs_delivery_day"]:checked', card) || !$('[name="rs_delivery_slot"]:checked', card));
    };
    const updateReview = () => {
      const different = $('#ship-to-different-address-checkbox', form)?.checked;
      const prefix = different ? 'shipping_' : 'billing_';
      const val = id => { const el = document.getElementById(id); return el && el.tagName === 'SELECT' ? (el.selectedOptions[0]?.textContent || '') : (el?.value || ''); };
      const recipient = self ? (self.checked ? [val('billing_first_name'), val('billing_last_name')].filter(Boolean).join(' ') : ($('#rs-receiver-name', form)?.value || '')) : [val(prefix + 'first_name'), val(prefix + 'last_name')].filter(Boolean).join(' ');
      const addressText = [recipient, ...['state', 'city', 'address_1', 'address_2', 'postcode'].map(k => val(prefix + k).trim())].filter(Boolean).join('، ');
      const ship = $$('input[name^="shipping_method"]', form).filter(el => el.type === 'hidden' || el.checked).map(el => $$('label', form).find(label => label.htmlFor === el.id)?.textContent.trim()).filter(Boolean).join('، ');
      const pay = $('input[name="payment_method"]:checked', form);
      const label = pay && $$('label', form).find(label => label.htmlFor === pay.id);
      const addressTarget = $('[data-rs-review-address]', form), shippingTarget = $('[data-rs-review-shipping]', form), paymentTarget = $('[data-rs-review-payment]', form);
      if (addressTarget) addressTarget.textContent = addressText || '—';
      if (shippingTarget) shippingTarget.textContent = ship || '—';
      if (paymentTarget) paymentTarget.textContent = label?.textContent.trim() || '—';
    };
    const show = (index, refresh = false) => {
      const previous = current;
      current = Math.max(0, Math.min(index, keys.length - 1));
      if (previous !== current) window.scrollTo({ top: 0, behavior: 'smooth' });
      form.dataset.rsStep = keys[current];
      address.hidden = current !== 0;
      review.hidden = current === 0;
      if (shippingExtra) shippingExtra.hidden = current !== 1;
      if (paymentExtra) paymentExtra.hidden = current !== 2;
      back.textContent = current === 0 ? 'بازگشت به سبد خرید' : 'مرحله قبل';
      next.hidden = current === 2;
      const submit = $('[data-rs-place-order]', form);
      if (submit) submit.hidden = current !== 2;
      next.textContent = current === 1 ? 'ادامه به پرداخت' : 'ادامه به روش ارسال';
      const heading = $('[data-rs-review-heading]', form), description = $('[data-rs-review-description]', form);
      if (heading) heading.textContent = current === 1 ? 'شیوه ارسال سفارش' : 'بازبینی سفارش';
      if (description) description.textContent = current === 1 ? 'روش ارسال را با توجه به نشانی خود انتخاب کنید.' : 'کالاها و مبلغ نهایی را بررسی کنید.';
      const progress = $('.rs-checkout-steps');
      if (progress) progress.style.setProperty('--p', String((current + 1) / 4));
      $$('.rs-checkout-steps [data-rs-indicator]').forEach((el) => {
        const i = keys.indexOf(el.dataset.rsIndicator);
        el.classList.toggle('is-active', i === current);
        el.classList.toggle('is-done', el.dataset.rsIndicator === 'cart' || (i >= 0 && i < current));
        if (i === current) el.setAttribute('aria-current', 'step'); else el.removeAttribute('aria-current');
        const button = $('[data-rs-goto]', el);
        if (button) button.disabled = i < 0 || i >= current;
      });
      syncShippingMessage(); syncDelivery(); updateReview();
      if (refresh && window.jQuery) window.jQuery(document.body).trigger('update_checkout');
    };
    next.addEventListener('click', () => {
      if (current === 0) {
        if (picker?.dataset.rsRequiresAddress === '1' && !selectedAddress) { showToast('لطفاً یک آدرس ذخیره‌شده انتخاب یا آدرس جدید ثبت کنید.', 'i-info'); addButton?.focus(); return; }
        if (picker) syncSavedAddressFields();
        const state = $('#billing_state', form);
        if (selectedAddress && state?.tagName === 'SELECT' && selectedAddress.province && !state.value) { state.focus(); showToast('استان این آدرس را از فهرست ووکامرس انتخاب کنید.', 'i-info'); return; }
        const postcode = $('#billing_postcode', form);
        if (postcode && !postcode.value.trim()) changeField(postcode, '0000000000');
        const wasClosed = billingDetails && !billingDetails.open;
        const shippingDetails = $('[data-rs-shipping-details]', form);
        const shippingWasClosed = shippingDetails && !shippingDetails.open;
        if (billingDetails) billingDetails.open = true;
        if (shippingDetails && $('#ship-to-different-address-checkbox', form)?.checked) shippingDetails.open = true;
        const missing = $$('.validate-required input, .validate-required select, .validate-required textarea', address)
          .find(field => field.offsetParent !== null && !field.disabled && (field.type === 'checkbox' ? !field.checked : !field.value.trim()));
        if (missing) { missing.focus(); showToast('لطفاً فیلدهای ضروری نشانی را تکمیل کنید.', 'i-info'); return; }
        if (wasClosed) billingDetails.open = false;
        if (shippingWasClosed) shippingDetails.open = false;
        const missingReceiver = recipientInvalid();
        if (missingReceiver) { missingReceiver.focus(); showToast('نام و شماره تماس معتبر گیرنده را وارد کنید.', 'i-info'); return; }
      }
      if (current === 1) {
        if (deliveryMissing()) {
          $('[data-rs-slot-card] input', form)?.focus();
          showToast('روز و بازه تحویل را انتخاب کنید.', 'i-info');
          return;
        }
      }
      show(current + 1, current === 0);
    });
    back.addEventListener('click', () => { if (current === 0) window.location.href = WP.cartUrl || '/'; else show(current - 1); });
    $$('.rs-checkout-steps [data-rs-goto]').forEach(button => button.addEventListener('click', () => { const target = keys.indexOf(button.dataset.rsGoto); if (target >= 0 && target < current) show(target); }));
    $('[data-rs-edit-address]', form)?.addEventListener('click', () => show(0));
    $('[name="rs_gift_wrap"][type="checkbox"]', form)?.addEventListener('change', () => { if (window.jQuery) window.jQuery(document.body).trigger('update_checkout'); });
    const placeOrder = $('[data-rs-place-order]', form);
    const syncPlaceOrder = () => {
      const native = $('#place_order', form);
      if (placeOrder && native) placeOrder.textContent = native.textContent.trim() || 'ثبت سفارش';
    };
    placeOrder?.addEventListener('click', () => {
      const terms = $('#terms', form);
      if (terms && !terms.checked) { terms.focus(); showToast('لطفاً قوانین و شرایط بازگشت کالا را بپذیرید.', 'i-info'); return; }
      if (!form.classList.contains('processing')) $('#place_order', form)?.click();
    });
    const finalReview = $('[data-rs-final-review]', form);
    if (finalReview) finalReview.hidden = false;
    form.addEventListener('change', () => { syncDelivery(); updateReview(); setTimeout(syncPlaceOrder, 0); });
    if (window.jQuery) {
      window.jQuery(document.body).on('updated_checkout', () => { syncShippingMessage(); syncDelivery(); updateReview(); syncPlaceOrder(); });
      window.jQuery(document.body).on('checkout_error', () => {
        const invalid = $('.woocommerce-invalid', address);
        if (invalid) { show(0); if (picker && invalid.closest('[data-rs-customer-fields]')) { const extra = $('[data-rs-contact-fields]', form), panel = $('[data-rs-contact-completion]', form); if (extra && panel) { extra.appendChild(invalid); panel.hidden = false; } } if (billingDetails && invalid.closest('[data-rs-billing-details]')) billingDetails.open = true; const shippingDetails = $('[data-rs-shipping-details]', form); if (shippingDetails && invalid.closest('[data-rs-shipping-details]')) shippingDetails.open = true; invalid.querySelector('input,select,textarea')?.focus(); return; }
        const badReceiver = recipientInvalid();
        if (badReceiver) { show(0); badReceiver.focus(); return; }
        if (deliveryMissing()) { show(1); $('[data-rs-slot-card] input', form)?.focus(); }

      });
    }
    const couponCard = $('[data-rs-coupon-card]', form), couponInput = $('[data-rs-coupon-input]', form), couponButton = $('[data-rs-coupon-submit]', form), couponNotice = $('[data-rs-coupon-notice]', form);
    const params = window.wc_checkout_params;
    if (couponCard && couponInput && couponButton && couponNotice && params?.wc_ajax_url && params?.apply_coupon_nonce && window.jQuery) {
      couponCard.hidden = false;
      document.body.classList.add('rs-custom-checkout-coupon');
      couponButton.addEventListener('click', async () => {
        const code = couponInput.value.trim();
        if (!code) { couponNotice.textContent = 'کد تخفیف را وارد کنید.'; couponInput.focus(); return; }
        couponButton.disabled = true; couponNotice.textContent = 'در حال بررسی کد…';
        try {
          const data = new URLSearchParams({ security: params.apply_coupon_nonce, coupon_code: code, billing_email: $('#billing_email', form)?.value || '' });
          const response = await fetch(params.wc_ajax_url.replace('%%endpoint%%', 'apply_coupon'), { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body: data });
          if (!response.ok) throw new Error('coupon request failed');
          const html = await response.text();
          const doc = new DOMParser().parseFromString(html, 'text/html');
          couponNotice.textContent = doc.body.textContent.trim() || 'پاسخی از فروشگاه دریافت نشد.';
          if (doc.querySelector('.woocommerce-message, .is-success')) couponInput.value = '';
          window.jQuery(document.body).trigger('applied_coupon_in_checkout', [code]);
          window.jQuery(document.body).trigger('update_checkout', { update_shipping_method: false });
        } catch (_) { couponNotice.textContent = 'بررسی کد ممکن نشد؛ لطفاً دوباره تلاش کنید.'; }
        finally { couponButton.disabled = false; }
      });
    }
    show(0);
  }

  /* -------------------- تسویه حساب ۴ مرحله‌ای متصل به ووکامرس -------------------- */
  function initCheckoutWizard() {
    const form = $('form.woocommerce-checkout#co-layout');
    if (!form) return;

    const stepsOrder = ['address', 'shipping', 'payment'];
    let stepIdx = 0;

    const syncHiddenBillingFromSelected = () => {
      const activeAddr = $('.co-address.is-selected', form);
      if (activeAddr) {
        $('#wc_billing_first_name').value = activeAddr.dataset.addrName || '';
        $('#wc_billing_phone').value = toEn(activeAddr.dataset.addrPhone || '');
        $('#wc_billing_state').value = activeAddr.dataset.addrProvince || 'مازندران';
        $('#wc_billing_city').value = activeAddr.dataset.addrCity || 'آمل';
        $('#wc_billing_postcode').value = toEn(activeAddr.dataset.addrPostal || '');
        $('#wc_billing_address_1').value = activeAddr.dataset.addrFull || '';
        const shipTo = $('#co-ship-to');
        if (shipTo) shipTo.textContent = activeAddr.dataset.addrCity || 'آمل';
      } else {
        /* اگر فرم دستی باز بود */
        const manualAddr = $('#na-address')?.value.trim() || '';
        if (manualAddr) {
          $('#wc_billing_first_name').value = $('#na-name')?.value.trim() || 'مشتری';
          $('#wc_billing_phone').value = toEn($('#na-phone')?.value.trim() || '');
          $('#wc_billing_state').value = $('#na-province')?.value || 'مازندران';
          $('#wc_billing_city').value = $('#na-city')?.value.trim() || 'آمل';
          $('#wc_billing_postcode').value = toEn($('#na-postal')?.value.trim() || '');
          $('#wc_billing_address_1').value = manualAddr;
        }
      }
    };

    const goStep = nextIdx => {
      if (nextIdx > 0) {
        syncHiddenBillingFromSelected();
        if (!$('#wc_billing_address_1').value.trim() || !$('#wc_billing_phone').value.trim()) {
          const err = $('#co-address-error');
          if (err) err.hidden = false;
          showToast('لطفاً نشانی کامل و شماره موبایل تحویل‌گیرنده را وارد یا انتخاب کنید.', 'i-info');
          return;
        }
        const err = $('#co-address-error');
        if (err) err.hidden = true;
      }

      stepIdx = Math.max(0, Math.min(stepsOrder.length - 1, nextIdx));
      const key = stepsOrder[stepIdx];

      $$('.co-step', form).forEach(sec => {
        const active = sec.dataset.step === key;
        sec.classList.toggle('is-active', active);
        sec.hidden = !active;
      });

      const bar = $('#co-steps');
      if (bar) {
        bar.style.setProperty('--p', String([0.25, 0.5, 0.78][stepIdx] || 0.25));
        $$('#co-steps li').forEach((li, i) => {
          li.classList.toggle('is-done', i <= stepIdx);
          li.classList.toggle('is-active', i === stepIdx + 1);
        });
      }

      const label = stepIdx === 2 ? 'ثبت سفارش و پرداخت امن' : 'ادامه';
      ['#co-next span', '#co-side-next', '#co-mobile-next'].forEach(sel => {
        const el = $(sel);
        if (el) el.textContent = label;
      });
      const backLabel = $('#co-back span');
      if (backLabel) backLabel.textContent = stepIdx === 0 ? 'بازگشت به سبد خرید' : 'مرحله قبل';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const advanceOrSubmit = async () => {
      if (stepIdx < 2) {
        goStep(stepIdx + 1);
        return;
      }

      if ($('#co-terms') && !$('#co-terms').checked) {
        const te = $('#co-terms-error');
        if (te) te.hidden = false;
        showToast('پذیرش قوانین فروشگاه الزامی است.', 'i-info');
        return;
      }

      syncHiddenBillingFromSelected();
      const proc = $('#co-processing');
      if (proc) proc.hidden = false;

      try {
        const endpoint = WP.wcAjaxUrl ? WP.wcAjaxUrl.replace('%%endpoint%%', 'checkout') : (form.action + '?wc-ajax=checkout');
        const fd = new FormData(form);
        const res = await fetch(endpoint, { method: 'POST', credentials: 'same-origin', body: fd });
        const json = await res.json();

        if (json.result === 'success' && json.redirect) {
          window.location.href = json.redirect;
          return;
        }

        if (proc) proc.hidden = true;
        const tmp = document.createElement('div');
        tmp.innerHTML = json.messages || '';
        showToast(tmp.textContent.trim() || 'لطفاً فیلدهای سفارش را بررسی کنید.', 'i-info');
      } catch {
        form.submit();
      }
    };

    ['#co-next', '#co-side-next', '#co-mobile-next'].forEach(sel => $(sel)?.addEventListener('click', advanceOrSubmit));
    $('#co-back')?.addEventListener('click', () => {
      if (stepIdx === 0) window.location.href = WP.cartUrl || '/';
      else goStep(stepIdx - 1);
    });
    $$('[data-goto]').forEach(btn => btn.addEventListener('click', () => {
      const idx = stepsOrder.indexOf(btn.dataset.goto);
      if (idx >= 0) goStep(idx);
    }));

    /* انتخاب کارت آدرس */
    form.addEventListener('change', e => {
      if (e.target.name === 'rs_selected_address') {
        $$('.co-address', form).forEach(lbl => lbl.classList.toggle('is-selected', lbl.contains(e.target)));
        syncHiddenBillingFromSelected();
      }
      if (e.target.name === 'rs_ship_method' || e.target.name === 'payment_method') {
        const wrap = e.target.closest('.co-options');
        if (wrap) $$('.co-option', wrap).forEach(o => o.classList.toggle('is-selected', o.contains(e.target)));
      }
    });

    /* باز/بستن فرم آدرس جدید و ذخیره AJAX */
    $('#co-add-address')?.addEventListener('click', () => {
      const f = $('#co-address-form');
      if (f) f.hidden = !f.hidden;
    });
    $('#co-cancel-address')?.addEventListener('click', () => {
      const f = $('#co-address-form');
      if (f) f.hidden = true;
    });
    $('#co-save-address-btn')?.addEventListener('click', async () => {
      const data = {
        title: $('#na-title')?.value.trim() || 'منزل',
        name: $('#na-name')?.value.trim() || '',
        phone: $('#na-phone')?.value.trim() || '',
        province: $('#na-province')?.value || 'مازندران',
        city: $('#na-city')?.value.trim() || 'آمل',
        postal: $('#na-postal')?.value.trim() || '',
        address: $('#na-address')?.value.trim() || ''
      };
      if (!data.address || !data.phone) {
        showToast('نشانی کامل و شماره موبایل الزامی است.', 'i-info');
        return;
      }
      if (WP.isLoggedIn) {
        const res = await wpAjax('rs_address_save', data);
        if (!res.success) {
          showToast(res.data?.message || 'خطا در ثبت آدرس', 'i-info');
          return;
        }
      }
      const list = $('#co-address-list');
      if (list) {
        $$('.co-address', list).forEach(a => a.classList.remove('is-selected'));
        const lbl = document.createElement('label');
        lbl.className = 'co-address is-selected';
        Object.assign(lbl.dataset, {
          addrName: data.name,
          addrPhone: data.phone,
          addrProvince: data.province,
          addrCity: data.city,
          addrPostal: data.postal,
          addrFull: data.address
        });
        lbl.innerHTML = `<input type="radio" name="rs_selected_address" checked><span class="co-radio"></span><span class="co-address-body"><b>${escapeHtml(data.title)}</b><span class="co-address-text">${escapeHtml(data.province)}، ${escapeHtml(data.city)}، ${escapeHtml(data.address)}</span></span>`;
        list.prepend(lbl);
      }
      syncHiddenBillingFromSelected();
      $('#co-address-form').hidden = true;
      showToast('آدرس ثبت و انتخاب شد.');
    });

    /* گیرنده سفارشی */
    $('#co-receiver-self')?.addEventListener('change', function () {
      const box = $('#co-receiver');
      if (box) box.hidden = this.checked;
    });

    /* روز و ساعت تحویل */
    $$('#co-days .co-day').forEach(btn => btn.addEventListener('click', () => {
      $$('#co-days .co-day').forEach(b => { b.classList.toggle('is-selected', b === btn); b.setAttribute('aria-checked', String(b === btn)); });
      $('#wc_rs_delivery_day').value = btn.dataset.dayVal || '';
    }));
    $$('#co-slots .co-slot').forEach(btn => btn.addEventListener('click', () => {
      $$('#co-slots .co-slot').forEach(b => { b.classList.toggle('is-selected', b === btn); b.setAttribute('aria-checked', String(b === btn)); });
      $('#wc_rs_delivery_slot').value = btn.dataset.slotVal || '';
    }));

    /* بسته‌بندی هدیه */
    $('#co-gift')?.addEventListener('change', async function () {
      const res = await wpAjax('rs_checkout_gift', { enabled: this.checked ? '1' : '' });
      if (res.success) {
        const tot = $('#co-sum-total');
        if (tot) tot.textContent = res.data.total_fa;
        const mob = $('#co-mobile-total');
        if (mob) mob.textContent = res.data.total_fa;
      }
    });

    /* کد تخفیف در تسویه حساب */
    $('#co-coupon-btn')?.addEventListener('click', async () => {
      const code = $('#co-coupon-input')?.value.trim() || '';
      const res = await wpAjax('rs_cart_coupon', { coupon: code });
      const note = $('#co-coupon-note');
      if (note) note.textContent = res.data?.message || '';
      if (res.success) {
        $('#co-sum-discount').textContent = res.data.discount_fa;
        $('#co-sum-total').textContent = res.data.total_fa;
        showToast(res.data.message);
      } else {
        showToast(res.data?.message || 'کد نامعتبر است', 'i-info');
      }
    });
  }

  /* -------------------- پنل کاربری، سفارش‌ها و فرم‌های عمومی -------------------- */
  function initAccountAndForms() {
    /* تب‌های حساب کاربری */
    const switchAccountTab = key => {
      if (!key) return;
      $$('.account-nav [data-account-tab]').forEach(b => b.classList.toggle('active', b.dataset.accountTab === key));
      $$('.account-panel').forEach(p => p.classList.toggle('active', p.id === 'panel-' + key));
    };

    $$('[data-account-tab]').forEach(btn => btn.addEventListener('click', () => {
      switchAccountTab(btn.dataset.accountTab);
    }));
    if (window.location.hash && $('#panel-' + window.location.hash.slice(1))) {
      switchAccountTab(window.location.hash.slice(1));
    }

    /* ذخیره پروفایل حساب */
    $('#account-profile-form')?.addEventListener('submit', async e => {
      e.preventDefault();
      const fd = new FormData(e.currentTarget);
      const data = Object.fromEntries(fd.entries());
      const res = await wpAjax('rs_profile_save', data);
      showToast(res.data?.message || 'ذخیره شد', res.success ? 'i-check' : 'i-info');
    });

    /* افزودن/حذف آدرس در پنل کاربری */
    $('#account-new-address-form')?.addEventListener('submit', async e => {
      e.preventDefault();
      const fd = new FormData(e.currentTarget);
      const res = await wpAjax('rs_address_save', Object.fromEntries(fd.entries()));
      showToast(res.data?.message || '', res.success ? 'i-check' : 'i-info');
      if (res.success) setTimeout(() => window.location.reload(), 700);
    });

    $$('[data-delete-address]').forEach(btn => btn.addEventListener('click', async () => {
      const id = btn.dataset.deleteAddress;
      const res = await wpAjax('rs_address_delete', { address_id: id });
      if (res.success) {
        btn.closest('[data-address-card]')?.remove();
        showToast(res.data.message);
      }
    }));

    /* کوپن و پاک کردن کل سبد در صفحه سبد خرید */
    $('#coupon-form')?.addEventListener('submit', async e => {
      e.preventDefault();
      const code = $('#coupon-input')?.value.trim() || '';
      const res = await wpAjax('rs_cart_coupon', { coupon: code });
      const note = $('#coupon-note');
      if (note && res.data?.message) note.textContent = res.data.message;
      if (res.success) {
        syncCartDom(res.data);
        showToast(res.data.message);
      } else {
        showToast(res.data?.message || 'کد تخفیف معتبر نیست', 'i-info');
      }
    });

    $('#cart-page-clear')?.addEventListener('click', () => mutateCart('', 'clear'));

    /* خبرنامه */
    $$('[data-newsletter-form]').forEach(form => form.addEventListener('submit', async e => {
      e.preventDefault();
      const res = await wpAjax('rs_newsletter_subscribe', Object.fromEntries(new FormData(form).entries()));
      showToast(res.data?.message || '', res.success ? 'i-check' : 'i-info');
      if (res.success) form.reset();
      $('.wpmc-refresh', form)?.click();
    }));

    /* فرم تماس با ما */
    $('#contact-form')?.addEventListener('submit', async e => {
      e.preventDefault();
      const form = e.currentTarget;
      const fd = new FormData(form);
      const res = await wpAjax('rs_contact_submit', Object.fromEntries(fd.entries()));
      showToast(res.data?.message || '', res.success ? 'i-check' : 'i-info');
      if (res.success) form.reset();
      $('.wpmc-refresh', form)?.click();
    });

    /* مودال ورود سریع */
    $('#login-form')?.addEventListener('submit', async e => {
      e.preventDefault();
      const form = e.currentTarget;
      const identity = $('#mobile-number')?.value.trim() || '';
      const password = $('#modal-password')?.value || '';
      const payload = Object.fromEntries(new FormData(form).entries());
      const res = await wpAjax('rs_modal_auth', { ...payload, identity, password });
      if (password) $('.wpmc-refresh', form)?.click();
      if (!res.success) {
        showToast(res.data?.message || 'خطا در ورود', 'i-info');
        return;
      }
      if (res.data.redirect) {
        showToast(res.data.message);
        window.location.href = res.data.redirect;
        return;
      }
      if (res.data.need_password) {
        const pwWrap = $('#login-modal-pass-wrap');
        if (pwWrap && pwWrap.hidden) {
          pwWrap.hidden = false;
          $('#modal-password')?.focus();
          showToast(res.data.message, 'i-info');
        } else if (res.data.account_url) {
          window.location.href = res.data.account_url;
        }
      }
    });
  }

  /* -------------------- رویدادهای کلیک عمومی -------------------- */
  function initDelegatedEvents() {
    document.addEventListener('click', async e => {
      const addBtn = e.target.closest('[data-add]');
      if (addBtn) {
        e.preventDefault();
        e.stopPropagation();
        addProductToCart(addBtn.dataset.add, 1);
        return;
      }

      const wishBtn = e.target.closest('[data-wish]');
      if (wishBtn) {
        e.preventDefault();
        e.stopPropagation();
        toggleWishlist(wishBtn.dataset.wish);
        return;
      }

      const cartAct = e.target.closest('[data-cart-action]');
      if (cartAct) {
        e.preventDefault();
        mutateCart(cartAct.dataset.id, cartAct.dataset.cartAction);
        return;
      }

      const searchChip = e.target.closest('[data-search-term]');
      if (searchChip) {
        const box = searchChip.closest('.search-box');
        const inp = $('input[type="search"]', box);
        if (inp) {
          inp.value = searchChip.dataset.searchTerm;
          inp.dispatchEvent(new Event('input'));
          inp.focus();
        }
        return;
      }

      const copyBtn = e.target.closest('[data-copy]');
      if (copyBtn) {
        try {
          await navigator.clipboard.writeText(copyBtn.dataset.copy);
          showToast('در حافظه کپی شد');
        } catch {
          showToast('امکان کپی خودکار وجود ندارد', 'i-info');
        }
        return;
      }

      if (e.target.closest('[data-print]')) {
        window.print();
        return;
      }

      const cancelOrd = e.target.closest('[data-cancel-order]');
      if (cancelOrd) {
        if (!window.confirm('آیا از لغو این سفارش اطمینان دارید؟')) return;
        const res = await wpAjax('rs_order_cancel', { order_id: cancelOrd.dataset.cancelOrder });
        showToast(res.data?.message || '', res.success ? 'i-check' : 'i-info');
        if (res.success) setTimeout(() => window.location.reload(), 900);
        return;
      }

      const reorderBtn = e.target.closest('[data-reorder]');
      if (reorderBtn) {
        const res = await wpAjax('rs_order_reorder', { order_id: reorderBtn.dataset.reorder });
        showToast(res.data?.message || '', res.success ? 'i-check' : 'i-info');
        if (res.success && res.data.cart_url) {
          setTimeout(() => { window.location.href = res.data.cart_url; }, 700);
        }
        return;
      }

      if (e.target.closest('#wishlist-top')) {
        openWishlistModal();
        return;
      }

      if (e.target.closest('[data-open-login]')) {
        openModal('login-modal');
        return;
      }

      if (e.target.closest('.drawer-close') || e.target.closest('.modal-close') || e.target.id === 'page-overlay') {
        closeLayers();
        return;
      }

      if (!e.target.closest('.search-box')) {
        $$('.search-box.open').forEach(b => b.classList.remove('open'));
      }

      if (!e.target.closest('.category-nav')) {
        $('.category-nav')?.classList.remove('open');
        $('#category-button')?.setAttribute('aria-expanded', 'false');
      }
    });

    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') {
        closeLayers();
        $$('.search-box.open').forEach(b => b.classList.remove('open'));
      }
    });
  }

  /* اجرای اولیه */
  initStickyAndScroll();
  initNavigation();
  initHeroAndCarousels();
  initLiveSearch();
  refreshWishlistButtons();
  initShopPage();
  initSingleProduct();
  initCheckoutWizard();
  initNativeCheckoutSteps();
  initAccountAndForms();
  initDelegatedEvents();

  window.RS = { showToast, addProductToCart, toggleWishlist, formatPrice, toFa };
})();
