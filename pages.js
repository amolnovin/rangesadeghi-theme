/* ==========================================================================
   رنگ صادقی | اسکریپت صفحات داخلی (pages.js)
   هر ماژول فقط زمانی اجرا می‌شود که صفحه مربوطه باز باشد (body[data-page])
   ========================================================================== */
(function () {
  'use strict';

  const page = document.body.dataset.page || 'home';
  const params = new URLSearchParams(window.location.search);

  document.addEventListener('rs:ready', boot);
  if (window.RS && document.readyState !== 'loading') boot();

  let booted = false;
  function boot() {
    if (booted || !window.RS) return;
    booted = true;
    const modules = { shop, category, product, blog, article, contact, about, stores, cart, checkout, order, account, categories: categoryIndex };
    (modules[page] || (() => {}))();
    common();
  }

  const { $, $$, products, CATEGORIES, BRANDS, formatPrice, toFa, toEn, getProduct, imagePath, productUrl, productCard, showToast } = window.RS || {};

  /* ---------------------------------------------------------------- عمومی */
  function common() {
    /* شمارنده دسته‌بندی‌ها و برندها */
    $$('[data-cat-count]').forEach(el => {
      const slug = el.dataset.catCount;
      const count = products.filter(p => (p.cats || []).includes(slug)).length;
      el.textContent = `${toFa(count)} کالا`;
    });
    /* فرم خبرنامه */
    $$('[data-newsletter]').forEach(form => {
      form.addEventListener('submit', event => {
        event.preventDefault();
        const input = $('input', form);
        if (!input.value.trim() || input.value.trim().length < 5) { showToast('ایمیل خود را درست وارد کنید', 'i-mail'); return; }
        showToast('عضویت شما در خبرنامه ثبت شد (نمایشی)');
        form.reset();
      });
    });
    /* دکمه‌های نمایشی و ورود */
    $$('[data-toast]').forEach(button => button.addEventListener('click', () => showToast(button.dataset.toast)));
    $$('[data-open-login]').forEach(button => button.addEventListener('click', () => window.RS.openModal('login-modal')));
    $$('[data-demo-form]').forEach(form => form.addEventListener('submit', event => {
      event.preventDefault();
      showToast(form.dataset.demoForm || 'اطلاعات ثبت شد (نمایشی)');
    }));

    /* وضعیت منوی ثابت پایین */
    const pageKey = document.body.dataset.page;
    const current = $('.mobile-toolbar a[href="' + pageKey + '.html"]');
    if (current) { current.classList.add('active'); current.setAttribute('aria-current', 'page'); }

    /* اشتراک‌گذاری */
    $$('[data-copy-link]').forEach(button => button.addEventListener('click', async () => {
      try { await navigator.clipboard.writeText(window.location.href); showToast('لینک صفحه کپی شد'); }
      catch { showToast('کپی لینک در این مرورگر ممکن نیست', 'i-close'); }
    }));
  }

  /* ================================================================ فروشگاه */
  function shop() {
    const grid = $('#shop-grid');
    if (!grid) return;

    const state = {
      cats: new Set(params.get('cat') ? [params.get('cat')] : []),
      brands: new Set(),
      min: 0,
      max: 0,
      sale: params.get('sale') === '1',
      inStock: false,
      sort: 'default',
      view: 'grid',
      page: 1,
      perPage: 9
    };

    const catList = $('[data-filter-list="cats"]');
    const brandList = $('[data-filter-list="brands"]');

    function renderFilterLists() {
      if (catList) {
        catList.innerHTML = CATEGORIES.map(category => {
          const count = products.filter(p => (p.cats || []).includes(category.slug)).length;
          return `<label class="filter-option"><input type="checkbox" value="${category.slug}" ${state.cats.has(category.slug) ? 'checked' : ''}><span>${category.title}</span><span class="count">${toFa(count)}</span></label>`;
        }).join('');
      }
      if (brandList) {
        brandList.innerHTML = BRANDS.map(brand => {
          const count = products.filter(p => p.brand === brand.slug).length;
          if (!count) return '';
          return `<label class="filter-option"><input type="checkbox" value="${brand.slug}" ${state.brands.has(brand.slug) ? 'checked' : ''}><span>${brand.title}</span><span class="count">${toFa(count)}</span></label>`;
        }).join('');
      }
      const sale = $('#filter-sale');
      if (sale) sale.checked = state.sale;
      const stock = $('#filter-stock');
      if (stock) stock.checked = state.inStock;
      const min = $('#filter-min');
      if (min && document.activeElement !== min && state.min) min.value = formatPrice(state.min);
      const max = $('#filter-max');
      if (max && document.activeElement !== max && state.max) max.value = formatPrice(state.max);
    }

    function filtered() {
      let list = products.slice();
      if (state.cats.size) list = list.filter(p => (p.cats || []).some(c => state.cats.has(c)));
      if (state.brands.size) list = list.filter(p => state.brands.has(p.brand));
      if (state.sale) list = list.filter(p => p.discount);
      if (state.inStock) list = list.filter(p => (p.stock || 0) > 0);
      if (state.min) list = list.filter(p => p.price >= state.min);
      if (state.max) list = list.filter(p => p.price <= state.max);

      const sorters = {
        newest: (a, b) => new Date(b.date) - new Date(a.date),
        popular: (a, b) => (b.rating || 0) - (a.rating || 0),
        bestseller: (a, b) => (b.sold || 0) - (a.sold || 0),
        discount: (a, b) => (b.discount || 0) - (a.discount || 0),
        cheap: (a, b) => a.price - b.price,
        expensive: (a, b) => b.price - a.price,
        name: (a, b) => a.title.localeCompare(b.title, 'fa')
      };
      if (sorters[state.sort]) list.sort(sorters[state.sort]);
      return list;
    }

    function renderChips() {
      const activeBox = $('#active-filters');
      if (!activeBox) return;
      const chips = [];
      state.cats.forEach(slug => {
        const category = CATEGORIES.find(c => c.slug === slug);
        chips.push(`<span class="chip">${category ? category.title : slug}<button class="chip-close" data-clear="cat" data-value="${slug}" aria-label="حذف فیلتر"><svg><use href="#i-close"/></svg></button></span>`);
      });
      state.brands.forEach(slug => {
        const brand = BRANDS.find(b => b.slug === slug);
        chips.push(`<span class="chip">${brand ? brand.title : slug}<button class="chip-close" data-clear="brand" data-value="${slug}" aria-label="حذف فیلتر"><svg><use href="#i-close"/></svg></button></span>`);
      });
      if (state.sale) chips.push('<span class="chip">فقط تخفیف‌دار<button class="chip-close" data-clear="sale" aria-label="حذف فیلتر"><svg><use href="#i-close"/></svg></button></span>');
      if (state.inStock) chips.push('<span class="chip">فقط موجود<button class="chip-close" data-clear="stock" aria-label="حذف فیلتر"><svg><use href="#i-close"/></svg></button></span>');
      if (state.min || state.max) chips.push('<span class="chip">محدوده قیمت<button class="chip-close" data-clear="price" aria-label="حذف فیلتر"><svg><use href="#i-close"/></svg></button></span>');
      activeBox.innerHTML = chips.length ? `<span>فیلترهای فعال:</span>${chips.join('')}<button class="chip" data-clear="all">پاک کردن همه</button>` : '';
    }

    function renderPagination(total, pages) {
      const box = $('#shop-pagination');
      if (!box) return;
      if (pages <= 1) { box.innerHTML = ''; return; }
      let html = `<button data-page-go="${state.page - 1}" ${state.page === 1 ? 'disabled' : ''} aria-label="صفحه قبلی"><svg><use href="#i-chevron-right"/></svg></button>`;
      for (let i = 1; i <= pages; i++) {
        if (pages > 6 && i > 2 && i < pages - 1 && Math.abs(i - state.page) > 1) {
          if (i === 3) html += '<span class="pagination-dots">…</span>';
          continue;
        }
        html += `<button class="${i === state.page ? 'active' : ''}" data-page-go="${i}" aria-label="صفحه ${toFa(i)}">${toFa(i)}</button>`;
      }
      html += `<button data-page-go="${state.page + 1}" ${state.page === pages ? 'disabled' : ''} aria-label="صفحه بعدی"><svg><use href="#i-chevron-left"/></svg></button>`;
      box.innerHTML = html;
    }

    function render() {
      const list = filtered();
      const pages = Math.max(1, Math.ceil(list.length / state.perPage));
      if (state.page > pages) state.page = pages;
      const start = (state.page - 1) * state.perPage;
      const items = list.slice(start, start + state.perPage);

      grid.classList.toggle('is-list', state.view === 'list');
      grid.innerHTML = items.length
        ? items.map(p => productCard(p, { list: state.view === 'list' })).join('')
        : '<div class="empty-state" style="grid-column:1/-1"><svg><use href="#i-search"/></svg><h3>محصولی با این فیلترها پیدا نشد</h3><p>می‌توانید فیلترها را تغییر دهید یا همه آن‌ها را پاک کنید.</p><button class="btn btn-primary btn-sm" data-clear="all">پاک کردن فیلترها</button></div>';

      const countEl = $('#result-count');
      if (countEl) countEl.innerHTML = `نمایش <b>${toFa(items.length)}</b> از <b>${toFa(list.length)}</b> کالا`;

      renderChips();
      renderPagination(list.length, pages);
    }

    document.addEventListener('change', event => {
      const input = event.target;
      if (input.closest('[data-filter-list="cats"]')) {
        input.checked ? state.cats.add(input.value) : state.cats.delete(input.value);
        state.page = 1; render();
      } else if (input.closest('[data-filter-list="brands"]')) {
        input.checked ? state.brands.add(input.value) : state.brands.delete(input.value);
        state.page = 1; render();
      } else if (input.id === 'filter-sale') { state.sale = input.checked; state.page = 1; render(); }
      else if (input.id === 'filter-stock') { state.inStock = input.checked; state.page = 1; render(); }
      else if (input.id === 'filter-max') { state.max = Number(toEn(input.value).replace(/\D/g, '')); state.page = 1; render(); }
      else if (input.id === 'sort-select') { state.sort = input.value; state.page = 1; render(); }
      else if (input.id === 'filter-min') { state.min = Number(toEn(input.value).replace(/\D/g, '')); state.page = 1; render(); }
    });

    document.addEventListener('input', event => {
      if (event.target.id === 'filter-min') {
        state.min = Number(toEn(event.target.value).replace(/\D/g, '')); state.page = 1; render();
      }
    });

    document.addEventListener('click', event => {
      const pageGo = event.target.closest('[data-page-go]');
      if (pageGo) {
        const target = Number(pageGo.dataset.pageGo);
        if (target >= 1) {
          state.page = target; render();
          $('#shop-grid')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        return;
      }
      const viewButton = event.target.closest('[data-view]');
      if (viewButton) {
        state.view = viewButton.dataset.view;
        $$('[data-view]').forEach(b => b.classList.toggle('active', b === viewButton));
        render();
        return;
      }
      if (event.target.closest('[data-open-filters]')) {
        $('.shop-sidebar')?.classList.toggle('is-open');
        return;
      }
      const clear = event.target.closest('[data-clear]');
      if (clear) {
        const type = clear.dataset.clear;
        if (type === 'cat') state.cats.delete(clear.dataset.value);
        if (type === 'brand') state.brands.delete(clear.dataset.value);
        if (type === 'sale') state.sale = false;
        if (type === 'stock') state.inStock = false;
        if (type === 'price') { state.min = state.max = 0; }
        if (type === 'all') {
          state.cats.clear(); state.brands.clear(); state.sale = false; state.inStock = false; state.min = state.max = 0;
          ['#filter-min', '#filter-max'].forEach(sel => { const el = $(sel); if (el) el.value = ''; });
        }
        state.page = 1;
        renderFilterLists();
        render();
      }
    });

    renderFilterLists();
    render();
  }

  /* =========================================================== دسته بندی ها */
  function categoryIndex() {
    /* فیلتر سریع داخل صفحه دسته‌بندی‌ها */
    $$('[data-cat-search]').forEach(input => input.addEventListener('input', () => {
      const value = input.value.trim();
      $$('.cat-card').forEach(card => {
        card.style.display = card.textContent.includes(value) || !value ? '' : 'none';
      });
    }));
  }

  function category() { categoryIndex(); }

  /* ============================================================== محصول */
  function product() {
    const id = params.get('id');
    const data = (id && getProduct(id)) || getProduct('p2');
    if (!data) return;

    /* پر کردن اطلاعات محصول */
    const setText = (selector, value) => { const el = $(selector); if (el) el.textContent = value; };
    document.title = `${data.title} | فروشگاه رنگ صادقی`;
    setText('#product-title', data.title);
    setText('#product-cat', data.cat);
    setText('#product-brand', (BRANDS.find(b => b.slug === data.brand) || {}).title || 'رنگ صادقی');
    setText('#product-sku', toFa(data.sku));
    setText('#product-short', data.short || '');
    setText('#product-desc', data.short ? `${data.short} این محصول با تضمین اصالت کالا از فروشگاه رنگ صادقی عرضه می‌شود و برای اجرای حرفه‌ای پروژه‌های رنگ، پتینه و کارهای هنری انتخاب مناسبی است.` : '');
    setText('#product-price', formatPrice(data.price));
    setText('#product-off', `-${toFa(data.discount || 0)}%`);
    setText('#product-old', data.old ? `${formatPrice(data.old)} تومان` : '');

    const stockEl = $('#product-stock');
    if (stockEl) stockEl.innerHTML = (data.stock || 0) > 0 ? `<svg><use href="#i-check"/></svg> موجود در انبار (${toFa(data.stock)} عدد)` : '<svg><use href="#i-close"/></svg> ناموجود';

    const ratingEl = $('#product-rating');
    if (ratingEl) ratingEl.innerHTML = `${'★'.repeat(Math.round(data.rating || 5))}${'☆'.repeat(5 - Math.round(data.rating || 5))}`;
    setText('#product-rate-value', toFa(data.rating || 5));
    setText('#product-reviews', `(${toFa(data.reviews || 0)} دیدگاه)`);

    /* گالری */
    const gallery = data.gallery && data.gallery.length ? data.gallery : [data.image];
    const mainImage = $('#gallery-main img');
    if (mainImage) { mainImage.src = imagePath(gallery[0]); mainImage.alt = data.title; }
    const thumbs = $('#gallery-thumbs');
    if (thumbs) {
      thumbs.innerHTML = gallery.map((image, index) =>
        `<button type="button" class="${index === 0 ? 'active' : ''}" data-gallery-image="${image}" aria-label="تصویر ${toFa(index + 1)}"><img src="${imagePath(image)}" alt=""></button>`
      ).join('');
      thumbs.addEventListener('click', event => {
        const button = event.target.closest('[data-gallery-image]');
        if (!button) return;
        $$('#gallery-thumbs button').forEach(b => b.classList.toggle('active', b === button));
        if (mainImage) { mainImage.src = imagePath(button.dataset.galleryImage); }
      });
    }

    /* مشخصات فنی */
    const specs = $('#spec-rows');
    if (specs) {
      specs.innerHTML = (data.specs || []).map(([key, value]) => `<tr><th>${key}</th><td>${value}</td></tr>`).join('') +
        `<tr><th>کد کالا</th><td>${toFa(data.sku)}</td></tr><tr><th>برند</th><td>${(BRANDS.find(b => b.slug === data.brand) || {}).title || '—'}</td></tr>`;
    }

    /* متغیرها: وزن/بسته‌بندی با تغییر قیمت */
    const variants = [
      { label: '۱ کیلوگرم', factor: 1 },
      { label: '۵ کیلوگرم', factor: 4.5 },
      { label: 'بسته پیمانکاری', factor: 8 }
    ];
    let variantFactor = 1;
    let quantity = 1;
    const variantBox = $('#variant-options');
    if (variantBox) {
      variantBox.addEventListener('click', event => {
        const button = event.target.closest('button');
        if (!button) return;
        $$('button', variantBox).forEach(b => b.classList.toggle('active', b === button));
        variantFactor = Number(button.dataset.factor || 1);
        updatePrice();
      });
    }
    function updatePrice() {
      const total = Math.round(data.price * variantFactor * quantity);
      setText('#product-price', formatPrice(total));
      const stickyPrice = $('#sticky-price');
      if (stickyPrice) stickyPrice.textContent = `${formatPrice(total)} تومان`;
      if (data.old) setText('#product-old', `${formatPrice(Math.round(data.old * variantFactor * quantity))} تومان`);
    }

    /* تعداد */
    const qtyInput = $('#qty-input');
    $('#qty-plus')?.addEventListener('click', () => { quantity = Math.min(20, quantity + 1); if (qtyInput) qtyInput.value = toFa(quantity); updatePrice(); });
    $('#qty-minus')?.addEventListener('click', () => { quantity = Math.max(1, quantity - 1); if (qtyInput) qtyInput.value = toFa(quantity); updatePrice(); });

    /* افزودن به سبد */
    $('#product-add')?.addEventListener('click', () => window.RS.addToCart(data.id, quantity));
    $('#sticky-add')?.addEventListener('click', () => window.RS.addToCart(data.id, quantity));

    /* رنگ ها */
    $$('.color-options button').forEach(button => button.addEventListener('click', () => {
      $$('.color-options button').forEach(b => b.classList.toggle('active', b === button));
    }));

    /* تب ها */
    $$('.tab-nav button').forEach(button => button.addEventListener('click', () => {
      $$('.tab-nav button').forEach(b => b.classList.toggle('active', b === button));
      $$('.tab-panel').forEach(panel => panel.classList.toggle('active', panel.id === button.dataset.tab));
    }));

    /* محصولات مرتبط */
    const related = products.filter(p => p.id !== data.id && (p.cats || []).some(c => (data.cats || []).includes(c))).slice(0, 8);
    const rail = $('#related-products');
    if (rail) rail.innerHTML = related.map(p => productCard(p)).join('');

    updatePrice();
    window.RS.$$(`[data-wish="${data.id}"]`).forEach(button => button.addEventListener('click', () => button.classList.toggle('active')));
  }

  /* ============================================================== وبلاگ */
  function blog() {
    const chips = $$('[data-blog-filter]');
    const cards = $$('.post-card');
    const more = $('#load-more');
    const searchInput = $('#blog-search-input');

    cards.forEach(card => { card.dataset.hidden = card.dataset.hidden || 'false'; });

    function apply(tag) {
      const query = (searchInput?.value || '').trim();
      const matched = cards.filter(card => {
        const matchTag = !tag || tag === 'all' || (card.dataset.tags || '').split(' ').includes(tag);
        const matchQuery = !query || card.textContent.includes(query);
        return matchTag && matchQuery;
      });
      const limit = Number(more?.dataset.limit || 6);
      cards.forEach(card => card.classList.add('is-hidden'));
      matched.forEach((card, index) => card.classList.toggle('is-hidden', index >= limit));
      if (more) more.style.display = matched.length > limit ? '' : 'none';
      const counter = $('#blog-result-count');
      if (counter) counter.textContent = toFa(matched.length);
      const empty = $('#blog-empty');
      if (empty) empty.style.display = matched.length ? 'none' : '';
    }

    chips.forEach(chip => chip.addEventListener('click', () => {
      chips.forEach(c => c.classList.toggle('active', c === chip));
      apply(chip.dataset.blogFilter);
    }));

    searchInput?.addEventListener('input', () => {
      const active = chips.find(chip => chip.classList.contains('active'));
      apply(active ? active.dataset.blogFilter : 'all');
    });

    more?.addEventListener('click', () => {
      more.dataset.limit = String(Number(more.dataset.limit || 6) + 4);
      const active = chips.find(chip => chip.classList.contains('active'));
      apply(active ? active.dataset.blogFilter : 'all');
      if (Number(more.dataset.limit) >= cards.length) more.style.display = 'none';
    });

    apply('all');
  }

  /* ========================================================== تک نوشته */
  function article() {
    /* نوار پیشرفت مطالعه */
    const bar = $('#read-progress span');
    const article = $('.article-body');
    function progress() {
      if (!bar || !article) return;
      const rect = article.getBoundingClientRect();
      const total = rect.height - window.innerHeight * .5;
      const passed = Math.min(Math.max(-rect.top + 120, 0), Math.max(total, 1));
      bar.style.width = `${Math.min(100, (passed / Math.max(total, 1)) * 100)}%`;
    }
    window.addEventListener('scroll', progress, { passive: true });
    window.addEventListener('resize', progress);
    progress();

    /* فهرست مطالب */
    const toc = $('#article-toc');
    const headings = $$('.article-body h2, .article-body h3');
    if (toc && headings.length) {
      headings.forEach((heading, index) => {
        if (!heading.id) heading.id = `section-${index + 1}`;
      });
      toc.innerHTML = headings.map(heading =>
        `<li class="${heading.tagName === 'H3' ? 'toc-sub' : ''}"><a href="#${heading.id}">${heading.textContent}</a></li>`
      ).join('');
      const links = $$('a', toc);
      const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            links.forEach(link => link.classList.toggle('active', link.getAttribute('href') === `#${entry.target.id}`));
          }
        });
      }, { rootMargin: '-90px 0px -70% 0px' });
      headings.forEach(heading => observer.observe(heading));
    }

    /* نوار پیشرفت متحرک در موبایل: بستن منوی اشتراک */
    $$('.article-tags a').forEach(link => link.addEventListener('click', event => event.preventDefault()));
  }


  /* ============================================================== سبد خرید */
  function cart() {
    const box = $('#cart-page-items');
    if (!box || !window.RS) return;

    const SHIPPING_FREE_LIMIT = 10000000;
    const SHIPPING_COST = 89000;
    let coupon = null;

    const money = value => `${formatPrice(Math.round(value))} تومان`;

    function totals() {
      const items = window.RS.getCartItems();
      let count = 0, subtotal = 0, discount = 0;
      items.forEach(({ product, quantity }) => {
        count += quantity;
        subtotal += product.price * quantity;
        if (product.old) discount += (product.old - product.price) * quantity;
      });
      return { items, count, subtotal, discount };
    }

    function couponValue(subtotal) {
      if (!coupon) return 0;
      return Math.round(subtotal * coupon.percent / 100);
    }

    function render() {
      const { items, count, subtotal, discount } = totals();
      const couponAmount = couponValue(subtotal);
      const shipping = subtotal >= SHIPPING_FREE_LIMIT || !count ? 0 : SHIPPING_COST;
      const payable = Math.max(0, subtotal - couponAmount) + shipping;

      /* شمارش و عنوان */
      const countEl = $('#cart-page-count');
      if (countEl) countEl.textContent = count ? `(${toFa(count)} کالا)` : '';

      /* نوار ارسال رایگان */
      const remaining = Math.max(0, SHIPPING_FREE_LIMIT - subtotal);
      const freeText = $('#free-ship-text');
      const freeBar = $('#free-ship-progress');
      if (freeText) {
        freeText.textContent = !count
          ? 'با افزودن کالا، هزینه ارسال محاسبه می‌شود.'
          : remaining > 0
            ? `${toFa(formatPrice(remaining))} تومان تا ارسال رایگان باقی مانده است.`
            : 'ارسال این سفارش رایگان است 🎉';
      }
      if (freeBar) freeBar.style.width = `${Math.min(100, (subtotal / SHIPPING_FREE_LIMIT) * 100)}%`;

      /* لیست کالاها */
      if (!items.length) {
        box.innerHTML = `<div class="cart-empty-lg">
          <svg><use href="#i-bag"/></svg>
          <h3>سبد خرید شما خالی است</h3>
          <p>هنوز محصولی به سبد خرید اضافه نکرده‌اید. از فروشگاه رنگ، پتینه و ابزار مورد نیازتان را انتخاب کنید.</p>
          <a class="btn btn-primary" href="shop.html"><svg><use href="#i-grid"/></svg>مشاهده محصولات</a>
        </div>`;
      } else {
        box.innerHTML = items.map(({ product, quantity }) => `
          <article class="cart-line" data-line="${product.id}">
            <a class="cart-line-img" href="${productUrl(product.id)}"><img src="${imagePath(product.image)}" alt="${product.title}" loading="lazy"></a>
            <div class="cart-line-body">
              <a class="cart-line-title" href="${productUrl(product.id)}">${product.title}</a>
              <div class="cart-line-meta"><span>${product.cat}</span><span aria-hidden="true">•</span><span>کد ${toFa(product.sku)}</span></div>
              <div class="cart-line-flags">
                ${product.stock ? '<span class="mini-flag">موجود در انبار</span>' : '<span class="mini-flag is-muted">ناموجود</span>'}
                ${product.discount ? `<span class="mini-flag is-sale">${toFa(product.discount)}٪ تخفیف</span>` : ''}
                <span class="mini-flag is-muted">ارسال از انبار آمل</span>
              </div>
              <div class="cart-line-actions">
                <div class="qty-stepper qty-sm">
                  <button type="button" data-cart-plus aria-label="افزایش تعداد"><svg><use href="#i-plus"/></svg></button>
                  <input value="${toFa(quantity)}" readonly aria-label="تعداد">
                  <button type="button" data-cart-minus aria-label="کاهش تعداد"><svg><use href="#i-minus"/></svg></button>
                </div>
                <button class="cart-line-btn is-danger" type="button" data-cart-remove><svg><use href="#i-trash"/></svg>حذف</button>
                <button class="cart-line-btn" type="button" data-cart-wish><svg><use href="#i-heart"/></svg>ذخیره در علاقه‌مندی</button>
              </div>
            </div>
            <div class="cart-line-price">
              <span class="old">${product.old ? `${formatPrice(product.old * quantity)} تومان` : ''}</span>
              <b>${formatPrice(product.price * quantity)}</b>
              <small>تومان</small>
            </div>
          </article>`).join('');
      }

      /* خلاصه سفارش */
      const set = (selector, value) => { const el = $(selector); if (el) el.textContent = value; };
      set('#summary-count', toFa(count));
      set('#summary-subtotal', money(subtotal));
      set('#summary-discount', couponAmount ? `− ${money(discount + couponAmount)}` : (discount ? `− ${money(discount)}` : '۰ تومان'));
      set('#summary-shipping', !count ? 'پس از انتخاب آدرس' : shipping ? money(shipping) : 'رایگان');
      set('#summary-total', money(payable));
      set('#cart-mobile-total', money(payable));

      /* نوار پایین موبایل فقط وقتی کالایی در سبد باشد */
      const mobileBar = $('.cart-mobile-bar');
      if (mobileBar) mobileBar.classList.toggle('is-empty', !count);

      /* دکمه پرداخت */
      const disabled = !count;
      ['#cart-checkout', '#cart-mobile-checkout'].forEach(selector => {
        const button = $(selector);
        if (button) button.disabled = disabled;
      });

      window.RS.updateCartUI();
      renderSuggested();
    }

    function renderSuggested() {
      const track = $('#cart-suggested');
      if (!track) return;
      const inCart = new Set(Object.keys(window.RS.getCart()));
      const list = products.filter(product => !inCart.has(product.id)).slice(0, 10);
      track.innerHTML = list.map(product => productCard(product)).join('');
    }

    /* رویدادها */
    box.addEventListener('click', event => {
      const line = event.target.closest('[data-line]');
      if (!line) return;
      const id = line.dataset.line;
      const quantity = window.RS.getCart()[id] || 0;

      if (event.target.closest('[data-cart-plus]')) { window.RS.setQuantity(id, quantity + 1); render(); }
      if (event.target.closest('[data-cart-minus]')) { window.RS.setQuantity(id, quantity - 1); render(); }
      if (event.target.closest('[data-cart-remove]')) { window.RS.setQuantity(id, 0); showToast('کالا از سبد خرید حذف شد', 'i-trash'); render(); }
      if (event.target.closest('[data-cart-wish]')) { if (!window.RS.wishlist.has(id)) window.RS.toggleWishlist(id); showToast('محصول در علاقه‌مندی‌ها ذخیره شد', 'i-heart'); }
    });

    $('#cart-page-clear')?.addEventListener('click', () => {
      if (!totals().count) { showToast('سبد خرید خالی است', 'i-bag'); return; }
      window.RS.clearCart(); coupon = null; render(); showToast('سبد خرید خالی شد', 'i-trash');
    });

    $('#coupon-form')?.addEventListener('submit', event => {
      event.preventDefault();
      const input = $('#coupon-input');
      const note = $('#coupon-note');
      const code = (input?.value || '').trim().toUpperCase();
      if (!code) { if (note) { note.textContent = 'کد تخفیف را وارد کنید.'; note.className = 'coupon-note is-error'; } return; }
      const codes = { RANG15: 15, PATINA10: 10, SADEGHI5: 5 };
      if (codes[code]) {
        coupon = { code, percent: codes[code] };
        if (note) { note.textContent = `کد «${code}» اعمال شد؛ ${toFa(codes[code])}٪ تخفیف روی مبلغ کالاها.`; note.className = 'coupon-note is-ok'; }
        render();
      } else {
        coupon = null;
        if (note) { note.textContent = 'این کد تخفیف معتبر نیست. کدهای فعال: RANG15 و PATINA10'; note.className = 'coupon-note is-error'; }
        render();
      }
    });

    ['#cart-checkout', '#cart-mobile-checkout'].forEach(selector => {
      $(selector)?.addEventListener('click', () => {
        if (!totals().count) { showToast('سبد خرید شما خالی است', 'i-bag'); return; }
        try { sessionStorage.setItem('sadeghi-coupon', JSON.stringify(coupon ? coupon.code : null)); } catch { /* نادیده */ }
        window.location.href = 'checkout.html';
      });
    });

    render();
  }

  /* ========================================================= تسویه حساب */
  function checkout() {
    const layout = $('#co-layout');
    if (!layout || !window.RS) return;

    const STEPS = ['address', 'shipping', 'payment', 'done'];
    const STEP_INDEX = { cart: 0, address: 1, shipping: 2, payment: 3, done: 4 };
    const FREE_LIMIT = 10000000;
    const GIFT_COST = 35000;
    const WALLET_BALANCE = 120000;
    const COUPONS = { RANG15: 15, PATINA10: 10, SADEGHI5: 5 };
    const money = value => `${formatPrice(Math.round(value))} تومان`;
    const toEn = value => String(value || '').replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d)).replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d));
    const esc = value => String(value).replace(/[&<>"']/g, ch => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]));

    const METHODS = {
      courier: { title: 'پیک فروشگاه', desc: 'ویژه شهر آمل؛ تحویل در بازه زمانی انتخابی شما', price: 49000, eta: 'همان روز یا فردا', icon: 'i-truck', slot: true, amolOnly: true, free: true },
      post: { title: 'پست پیشتاز', desc: 'ارسال به سراسر ایران همراه کد رهگیری', price: 89000, eta: '۲ تا ۴ روز کاری', icon: 'i-box', etaDays: [2, 4], free: true },
      tipax: { title: 'تیپاکس', desc: 'مناسب گالن‌ها و سطل‌های رنگ و کالاهای حجیم', price: 135000, eta: '۱ تا ۳ روز کاری', icon: 'i-layers', etaDays: [1, 3], free: false },
      pickup: { title: 'تحویل حضوری از فروشگاه', desc: 'آمل، خیابان طالب آملی، دریای ۳۰/۱ — شنبه تا پنج‌شنبه', price: 0, eta: 'آماده تحویل از ۲ ساعت بعد', icon: 'i-store', slot: true, free: true }
    };
    const PAYMENTS = {
      gateway: { title: 'پرداخت اینترنتی', desc: 'پرداخت آنلاین با تمام کارت‌های عضو شبکه شتاب', icon: 'i-card', badges: ['سامان', 'ملت', 'زرین‌پال'] },
      cod: { title: 'پرداخت در محل', desc: 'با کارت‌خوان هنگام تحویل سفارش', icon: 'i-wallet', onlyFor: ['courier', 'pickup'] },
      transfer: { title: 'کارت به کارت', desc: 'شماره کارت پس از ثبت سفارش نمایش داده می‌شود؛ تأیید تا ۲ ساعت کاری', icon: 'i-refresh' }
    };
    const SLOTS = ['۹ تا ۱۲', '۱۲ تا ۱۵', '۱۵ تا ۱۸', '۱۸ تا ۲۰:۳۰'];
    const DEFAULT_ADDRESSES = [
      { id: 'home', title: 'منزل', name: 'علی رضایی', phone: '09123456789', province: 'مازندران', city: 'آمل', postal: '4614853721', address: 'بلوار طالب آملی، کوچه ۱۲، ساختمان نگین، طبقه ۳', isDefault: true },
      { id: 'work', title: 'محل کار', name: 'علی رضایی', phone: '09113294688', province: 'مازندران', city: 'ساری', postal: '4816793451', address: 'خیابان فرهنگ، مجتمع تجاری پارسه، طبقه دوم، واحد ۱۸' }
    ];

    /* ---------- وضعیت (در sessionStorage تا با رفرش از دست نرود) ---------- */
    const store = {
      get(key, fallback) { try { return JSON.parse(sessionStorage.getItem(key)) ?? fallback; } catch { return fallback; } },
      set(key, value) { try { sessionStorage.setItem(key, JSON.stringify(value)); } catch { /* نادیده */ } }
    };
    const saved = store.get('sadeghi-checkout', {});
    const state = Object.assign({
      step: 'address', addresses: DEFAULT_ADDRESSES, address: 'home', self: true, receiver: { name: '', phone: '' },
      method: 'courier', day: 0, slot: 1, gift: false, note: '', payment: 'gateway', wallet: false, invoice: false
    }, saved);
    const savedCoupon = store.get('sadeghi-coupon', null);
    state.coupon = savedCoupon && COUPONS[savedCoupon] ? savedCoupon : (state.coupon && COUPONS[state.coupon] ? state.coupon : null);
    const persist = () => { const { step, ...rest } = state; store.set('sadeghi-checkout', { ...rest, step }); store.set('sadeghi-coupon', state.coupon); };

    const currentAddress = () => state.addresses.find(item => item.id === state.address) || null;
    const isAmol = () => (currentAddress()?.city || '').replace(/\s/g, '') === 'آمل';
    const methodAvailable = key => !METHODS[key].amolOnly || isAmol();
    const paymentAvailable = key => !PAYMENTS[key].onlyFor || PAYMENTS[key].onlyFor.includes(state.method);

    /* ---------- روزهای تحویل (بدون جمعه) ---------- */
    const weekdayFmt = new Intl.DateTimeFormat('fa-IR', { weekday: 'long' });
    const dateFmt = new Intl.DateTimeFormat('fa-IR-u-ca-persian', { day: 'numeric', month: 'long' });
    const fullDateFmt = new Intl.DateTimeFormat('fa-IR-u-ca-persian', { year: 'numeric', month: 'long', day: 'numeric' });
    const DAYS = [];
    for (let offset = 0, cursor = new Date(); DAYS.length < 6 && offset < 10; offset++) {
      const date = new Date(cursor.getFullYear(), cursor.getMonth(), cursor.getDate() + offset);
      if (date.getDay() === 5) continue; /* جمعه تعطیل */
      if (offset === 0 && cursor.getHours() >= 17) continue;
      DAYS.push({ date, label: offset === 0 ? 'امروز' : offset === 1 ? 'فردا' : weekdayFmt.format(date), sub: dateFmt.format(date) });
    }
    if (state.day >= DAYS.length) state.day = 0;
    const slotDisabled = (dayIndex, slotIndex) => {
      const day = DAYS[dayIndex];
      if (!day) return true;
      const isToday = day.date.toDateString() === new Date().toDateString();
      if (isToday) { const hour = new Date().getHours(); return [12, 15, 18, 20][slotIndex] <= hour + 1; }
      return dayIndex === 1 && slotIndex === 0; /* نمونه: ظرفیت تکمیل */
    };
    const firstFreeSlot = dayIndex => SLOTS.findIndex((_, index) => !slotDisabled(dayIndex, index));
    const addDays = days => { const d = new Date(); d.setDate(d.getDate() + days); return d; };

    /* ---------- محاسبات ---------- */
    function totals() {
      const items = window.RS.getCartItems();
      let count = 0, original = 0, subtotal = 0;
      items.forEach(({ product, quantity }) => {
        count += quantity;
        subtotal += product.price * quantity;
        original += (product.old || product.price) * quantity;
      });
      const discount = original - subtotal;
      const couponAmount = state.coupon ? Math.round(subtotal * COUPONS[state.coupon] / 100) : 0;
      const method = METHODS[state.method];
      const shipping = !count ? 0 : (method.free && subtotal >= FREE_LIMIT ? 0 : method.price);
      const gift = state.gift ? GIFT_COST : 0;
      const before = Math.max(0, subtotal - couponAmount) + shipping + gift;
      const wallet = state.wallet ? Math.min(WALLET_BALANCE, before) : 0;
      return { items, count, original, subtotal, discount, couponAmount, shipping, gift, wallet, payable: before - wallet };
    }

    /* ---------- رندر بخش‌ها ---------- */
    function renderAddresses() {
      const list = $('#co-address-list');
      list.innerHTML = state.addresses.map(item => `
        <label class="co-address${item.id === state.address ? ' is-selected' : ''}">
          <input type="radio" name="co-address" value="${esc(item.id)}"${item.id === state.address ? ' checked' : ''}>
          <span class="co-radio" aria-hidden="true"></span>
          <span class="co-address-body">
            <b>${esc(item.title)}${item.isDefault ? '<span class="default-chip">پیش‌فرض</span>' : ''}${item.isNew ? '<span class="default-chip is-new">جدید</span>' : ''}</b>
            <span class="co-address-text">${esc(item.province)}، ${esc(item.city)}، ${esc(item.address)}</span>
            <span class="co-address-meta">
              <span><svg><use href="#i-user"/></svg>${esc(item.name)}</span>
              <span><svg><use href="#i-phone"/></svg><bdo dir="ltr">${toFa(item.phone)}</bdo></span>
              <span><svg><use href="#i-mail"/></svg>کد پستی <bdo dir="ltr">${toFa(item.postal)}</bdo></span>
            </span>
          </span>
          ${item.city.replace(/\s/g, '') === 'آمل' ? '<span class="co-address-flag"><svg><use href="#i-truck"/></svg>امکان ارسال با پیک</span>' : ''}
        </label>`).join('');
      const address = currentAddress();
      const info = $('#co-self-info');
      if (info && address) info.textContent = `(${address.name} — ${toFa(address.phone)})`;
    }

    function optionCard({ name, value, icon, title, desc, side = '', sideSub = '', disabled = false, note = '', selected = false, badges = [] }) {
      return `
        <label class="co-option${selected ? ' is-selected' : ''}${disabled ? ' is-disabled' : ''}">
          <input type="radio" name="${name}" value="${value}"${selected ? ' checked' : ''}${disabled ? ' disabled' : ''}>
          <span class="co-radio" aria-hidden="true"></span>
          <span class="co-option-icon"><svg><use href="#${icon}"/></svg></span>
          <span class="co-option-body">
            <b>${title}</b>
            <small>${desc}</small>
            ${badges.length ? `<span class="co-badges">${badges.map(b => `<span>${b}</span>`).join('')}</span>` : ''}
            ${note ? `<em class="co-option-note">${note}</em>` : ''}
          </span>
          ${side ? `<span class="co-option-side"><b>${side}</b>${sideSub ? `<small>${sideSub}</small>` : ''}</span>` : ''}
        </label>`;
    }

    function renderMethods() {
      if (!methodAvailable(state.method)) state.method = 'post';
      const { subtotal } = totals();
      $('#co-methods').innerHTML = Object.entries(METHODS).map(([key, m]) => {
        const free = m.price === 0 || (m.free && subtotal >= FREE_LIMIT);
        return optionCard({
          name: 'co-method', value: key, icon: m.icon, title: m.title, desc: m.desc,
          side: free ? 'رایگان' : money(m.price), sideSub: m.eta,
          disabled: !methodAvailable(key), note: !methodAvailable(key) ? 'فقط برای آدرس‌های شهر آمل فعال است' : '',
          selected: key === state.method
        });
      }).join('');
      const address = currentAddress();
      const shipTo = $('#co-ship-to');
      if (shipTo && address) shipTo.textContent = `${address.title} — ${address.city}`;
      renderSlots();
    }

    function renderSlots() {
      const method = METHODS[state.method];
      const slotCard = $('#co-slot-card');
      const etaCard = $('#co-eta-card');
      slotCard.hidden = !method.slot;
      etaCard.hidden = !!method.slot;
      if (!method.slot) {
        const [from, to] = method.etaDays || [2, 4];
        $('#co-eta-text').textContent = `${method.title}: بین ${dateFmt.format(addDays(from))} تا ${dateFmt.format(addDays(to))} (${method.eta}) — کد رهگیری پیامک می‌شود.`;
        return;
      }
      $('#co-slot-hint').textContent = state.method === 'pickup'
        ? 'زمان مراجعه به فروشگاه را انتخاب کنید؛ سفارش شما آماده و بسته‌بندی‌شده تحویل داده می‌شود.'
        : 'روز و بازه زمانی دلخواه برای تحویل با پیک را انتخاب کنید.';
      if (slotDisabled(state.day, state.slot)) state.slot = firstFreeSlot(state.day);
      $('#co-days').innerHTML = DAYS.map((day, index) => `
        <button type="button" role="radio" class="co-day${index === state.day ? ' is-selected' : ''}" aria-checked="${index === state.day}" data-day="${index}">
          <b>${day.label}</b><small>${day.sub}</small>
        </button>`).join('');
      $('#co-slots').innerHTML = SLOTS.map((slot, index) => {
        const disabled = slotDisabled(state.day, index);
        return `<button type="button" role="radio" class="co-slot${index === state.slot ? ' is-selected' : ''}" aria-checked="${index === state.slot}" data-slot="${index}"${disabled ? ' disabled' : ''}>
          <svg><use href="#i-clock"/></svg><span>ساعت ${slot}</span>${disabled ? '<small>تکمیل ظرفیت</small>' : ''}
        </button>`;
      }).join('');
    }

    function renderShipment() {
      const { items, count } = totals();
      $('#co-shipment').innerHTML = `
        <div class="co-shipment-head"><b>مرسوله ۱ از ۱</b><span>${toFa(count)} کالا</span><span class="mini-flag">موجود در انبار</span></div>
        <div class="co-shipment-thumbs">${items.map(({ product, quantity }) => `
          <span class="co-thumb" title="${esc(product.title)}"><img src="${imagePath(product.image)}" alt="${esc(product.title)}" loading="lazy">${quantity > 1 ? `<em>${toFa(quantity)}</em>` : ''}</span>`).join('')}
        </div>`;
    }

    function renderPayments() {
      if (!paymentAvailable(state.payment)) state.payment = 'gateway';
      $('#co-payments').innerHTML = Object.entries(PAYMENTS).map(([key, p]) => optionCard({
        name: 'co-payment', value: key, icon: p.icon, title: p.title, desc: p.desc, badges: p.badges || [],
        disabled: !paymentAvailable(key), note: !paymentAvailable(key) ? 'فقط برای پیک فروشگاه و تحویل حضوری' : '',
        selected: key === state.payment
      })).join('');
      $('#co-wallet').checked = state.wallet;
      $('.co-wallet')?.classList.toggle('is-on', state.wallet);
      const note = $('#co-coupon-note');
      const input = $('#co-coupon-input');
      if (state.coupon) {
        if (input && !input.value) input.value = state.coupon;
        note.textContent = `کد «${state.coupon}» فعال است؛ ${toFa(COUPONS[state.coupon])}٪ تخفیف روی مبلغ کالاها.`;
        note.className = 'coupon-note is-ok';
      }
      $('#co-invoice').checked = state.invoice;
      renderReview();
    }

    function renderReview() {
      const address = currentAddress();
      const method = METHODS[state.method];
      const t = totals();
      const receiver = state.self ? `${address?.name} (${toFa(address?.phone || '')})` : `${state.receiver.name} (${toFa(state.receiver.phone)})`;
      const when = method.slot && DAYS[state.day] && state.slot >= 0 ? `${DAYS[state.day].label} ${DAYS[state.day].sub}، ساعت ${SLOTS[state.slot]}` : method.eta;
      $('#co-review').innerHTML = `
        <div class="co-review-row"><svg><use href="#i-pin"/></svg><div><small>ارسال به</small><b>${esc(address ? `${address.title}: ${address.city}، ${address.address}` : '—')}</b></div><button type="button" data-goto="address">ویرایش</button></div>
        <div class="co-review-row"><svg><use href="#i-user"/></svg><div><small>گیرنده</small><b>${esc(receiver)}</b></div><button type="button" data-goto="address">ویرایش</button></div>
        <div class="co-review-row"><svg><use href="#${method.icon}"/></svg><div><small>شیوه و زمان ارسال</small><b>${method.title} — ${when}</b></div><button type="button" data-goto="shipping">ویرایش</button></div>
        <div class="co-review-row"><svg><use href="#i-box"/></svg><div><small>کالاها</small><b>${toFa(t.count)} کالا${state.gift ? ' + بسته‌بندی هدیه' : ''}</b></div><a href="cart.html">ویرایش</a></div>`;
    }

    function renderSummary() {
      const t = totals();
      const set = (selector, value) => { const el = $(selector); if (el) el.textContent = value; };
      set('#co-count', `(${toFa(t.count)} کالا)`);
      $('#co-items-list').innerHTML = t.items.map(({ product, quantity }) => `
        <div class="co-item">
          <span class="co-thumb"><img src="${imagePath(product.image)}" alt="" loading="lazy">${quantity > 1 ? `<em>${toFa(quantity)}</em>` : ''}</span>
          <div><a href="${productUrl(product.id)}">${esc(product.title)}</a><small>${toFa(quantity)} × ${formatPrice(product.price)} تومان</small></div>
        </div>`).join('');
      set('#co-sum-original', money(t.original));
      set('#co-sum-discount', t.discount ? `− ${money(t.discount)}` : '۰ تومان');
      $('#co-sum-coupon-row').hidden = !t.couponAmount;
      set('#co-sum-coupon-code', state.coupon ? `(${state.coupon})` : '');
      set('#co-sum-coupon', `− ${money(t.couponAmount)}`);
      set('#co-sum-method', `(${METHODS[state.method].title})`);
      set('#co-sum-shipping', t.shipping ? money(t.shipping) : 'رایگان');
      $('#co-sum-gift-row').hidden = !t.gift;
      set('#co-sum-gift-row b', money(GIFT_COST));
      $('#co-sum-wallet-row').hidden = !t.wallet;
      set('#co-sum-wallet', `− ${money(t.wallet)}`);
      set('#co-sum-total', money(t.payable));
      set('#co-mobile-total', money(t.payable));
      const profit = t.discount + t.couponAmount;
      $('#co-profit').hidden = !profit;
      set('#co-profit-value', money(profit));
      updateCta();
    }

    function ctaLabel() {
      if (state.step === 'address') return 'ادامه: انتخاب شیوه ارسال';
      if (state.step === 'shipping') return 'ادامه: پرداخت';
      if (state.step === 'payment') return state.payment === 'gateway' ? `پرداخت ${money(totals().payable)}` : 'ثبت نهایی سفارش';
      return '';
    }
    function updateCta() {
      const label = ctaLabel();
      $('#co-next span').textContent = label;
      $('#co-side-next').textContent = label;
      $('#co-mobile-next').textContent = state.step === 'payment' ? (state.payment === 'gateway' ? 'پرداخت' : 'ثبت سفارش') : 'ادامه';
      const back = $('#co-back span');
      if (back) back.textContent = state.step === 'address' ? 'بازگشت به سبد خرید' : state.step === 'shipping' ? 'بازگشت: آدرس' : 'بازگشت: شیوه ارسال';
    }

    function renderStepper() {
      const index = STEP_INDEX[state.step];
      const stepper = $('#co-steps');
      stepper.style.setProperty('--p', (index / 4).toFixed(2));
      stepper.classList.toggle('is-complete', state.step === 'done');
      $$('li', stepper).forEach(li => {
        const i = STEP_INDEX[li.dataset.stepKey];
        const done = i < index || state.step === 'done';
        li.classList.toggle('is-done', done);
        li.classList.toggle('is-active', i === index && state.step !== 'done');
        if (i === index) li.setAttribute('aria-current', 'step'); else li.removeAttribute('aria-current');
        const button = $('button', li);
        if (button) button.disabled = !(i < index && state.step !== 'done');
      });
    }

    /* ---------- اعتبارسنجی ---------- */
    function fieldError(input, message) {
      const field = input.closest('.field');
      field?.classList.toggle('has-error', !!message);
      const holder = field?.querySelector('.error');
      if (holder) holder.textContent = message || '';
      input.setAttribute('aria-invalid', message ? 'true' : 'false');
      return !message;
    }
    const validPhone = value => /^09\d{9}$/.test(toEn(value).replace(/[\s-]/g, ''));

    function validateNewAddress() {
      const form = $('#co-address-form');
      const v = name => form.elements[name].value.trim();
      const checks = [
        fieldError(form.elements.title, v('title').length < 2 ? 'عنوان آدرس را وارد کنید.' : ''),
        fieldError(form.elements.name, v('name').length < 3 ? 'نام گیرنده را کامل وارد کنید.' : ''),
        fieldError(form.elements.phone, !validPhone(v('phone')) ? 'شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.' : ''),
        fieldError(form.elements.city, v('city').length < 2 ? 'نام شهر را وارد کنید.' : ''),
        fieldError(form.elements.postal, !/^\d{10}$/.test(toEn(v('postal')).replace(/[\s-]/g, '')) ? 'کد پستی باید ۱۰ رقم باشد.' : ''),
        fieldError(form.elements.address, v('address').length < 10 ? 'نشانی را کامل‌تر بنویسید (حداقل ۱۰ حرف).' : '')
      ];
      const ok = checks.every(Boolean);
      if (!ok) form.querySelector('.has-error input, .has-error textarea')?.focus();
      return ok;
    }

    function validateStep(step) {
      if (step === 'address') {
        const error = $('#co-address-error');
        if (!currentAddress()) { error.hidden = false; error.scrollIntoView({ behavior: 'smooth', block: 'center' }); return false; }
        error.hidden = true;
        if (!state.self) {
          const name = $('#rc-name'), phone = $('#rc-phone');
          const ok = [
            fieldError(name, name.value.trim().length < 3 ? 'نام گیرنده را وارد کنید.' : ''),
            fieldError(phone, !validPhone(phone.value) ? 'شماره موبایل گیرنده معتبر نیست.' : '')
          ].every(Boolean);
          if (!ok) { (name.value.trim().length < 3 ? name : phone).focus(); return false; }
          state.receiver = { name: name.value.trim(), phone: toEn(phone.value).replace(/[\s-]/g, '') };
        }
        if (!$('#co-address-form').hidden) { showToast('ابتدا فرم آدرس جدید را ثبت یا لغو کنید', 'i-pin'); return false; }
        return true;
      }
      if (step === 'shipping') {
        if (METHODS[state.method].slot && (state.slot < 0 || slotDisabled(state.day, state.slot))) {
          showToast('یک بازه زمانی آزاد برای تحویل انتخاب کنید', 'i-clock'); return false;
        }
        return true;
      }
      if (step === 'payment') {
        const terms = $('#co-terms');
        $('#co-terms-error').hidden = terms.checked;
        if (!terms.checked) { terms.closest('.co-check').classList.add('shake'); setTimeout(() => terms.closest('.co-check').classList.remove('shake'), 500); terms.focus(); return false; }
        return true;
      }
      return true;
    }

    /* ---------- ناوبری بین مراحل ---------- */
    function showStep(step, { push = true, scroll = true } = {}) {
      state.step = step;
      $$('.co-step').forEach(section => {
        const active = section.dataset.step === step;
        section.hidden = !active;
        section.classList.toggle('is-active', active);
      });
      if (step === 'shipping') { renderMethods(); renderShipment(); $('#co-gift').checked = state.gift; $('#co-note').value = state.note; }
      if (step === 'payment') renderPayments();
      const done = step === 'done';
      layout.classList.toggle('is-done', done);
      $('#co-nav').hidden = done;
      $('#co-side').hidden = done;
      $('#co-mobile-bar').classList.toggle('is-empty', done);
      renderStepper();
      renderSummary();
      if (step !== 'done') persist();
      if (push && history.pushState) history.pushState({ coStep: step }, '', `#${step}`);
      if (scroll) {
        const top = $('#co-steps').getBoundingClientRect().top + window.scrollY - (parseInt(getComputedStyle(document.documentElement).getPropertyValue('--sticky-offset'), 10) || 0) - 70;
        window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
      }
      const heading = $(`#co-step-${step} h1`);
      if (heading && scroll) { heading.setAttribute('tabindex', '-1'); heading.focus({ preventScroll: true }); }
    }

    function next() {
      if (state.step === 'payment') { if (validateStep('payment')) placeOrder(); return; }
      if (!validateStep(state.step)) return;
      showStep(STEPS[STEPS.indexOf(state.step) + 1]);
    }
    function back() {
      if (state.step === 'address') { window.location.href = 'cart.html'; return; }
      showStep(STEPS[STEPS.indexOf(state.step) - 1]);
    }
    function goTo(step) {
      if (STEP_INDEX[step] >= STEP_INDEX[state.step]) return;
      showStep(step);
    }

    /* ---------- ثبت سفارش ---------- */
    function placeOrder() {
      const t = totals();
      if (!t.count) { renderEmpty(); return; }
      const overlay = $('#co-processing');
      const title = $('#co-processing-title');
      const text = $('#co-processing-text');
      const isGateway = state.payment === 'gateway';
      title.textContent = isGateway ? 'در حال اتصال به درگاه پرداخت امن…' : 'در حال ثبت سفارش شما…';
      text.textContent = 'لطفاً تا پایان عملیات صفحه را نبندید.';
      overlay.hidden = false;
      requestAnimationFrame(() => overlay.classList.add('show'));
      document.body.classList.add('no-scroll');
      setTimeout(() => { title.textContent = isGateway ? 'تأیید پرداخت از بانک…' : 'رزرو کالاها در انبار…'; text.textContent = 'تقریباً تمام شد.'; }, 1100);
      setTimeout(() => {
        const address = currentAddress();
        const method = METHODS[state.method];
        const id = `RS-${toFa(String(Date.now()).slice(-8))}`;
        const when = method.slot && DAYS[state.day] ? `${DAYS[state.day].label} ${DAYS[state.day].sub}، ساعت ${SLOTS[state.slot]}` : method.eta;
        const order = {
          id, date: fullDateFmt.format(new Date()), total: t.payable, count: t.count,
          items: t.items.map(({ product, quantity }) => ({ id: product.id, title: product.title, image: product.image, quantity })),
          method: method.title, when, payment: PAYMENTS[state.payment].title,
          address: address ? `${address.province}، ${address.city}، ${address.address}` : '',
          status: state.payment === 'gateway' ? 'paid' : 'pending'
        };
        try {
          const orders = JSON.parse(localStorage.getItem('sadeghi-orders') || '[]');
          orders.unshift(order);
          localStorage.setItem('sadeghi-orders', JSON.stringify(orders.slice(0, 10)));
        } catch { /* نادیده */ }
        try { sessionStorage.removeItem('sadeghi-checkout'); sessionStorage.removeItem('sadeghi-coupon'); } catch { /* نادیده */ }
        window.RS.clearCart();
        overlay.classList.remove('show');
        overlay.hidden = true;
        document.body.classList.remove('no-scroll');
        renderDone(order, t);
        showStep('done', { push: false });
        if (history.replaceState) history.replaceState({ coStep: 'done' }, '', '#done');
        showToast('سفارش شما با موفقیت ثبت شد', 'i-check-circle');
      }, 2300);
    }

    function renderDone(order, t) {
      const transfer = state.payment === 'transfer';
      $('#co-step-done').innerHTML = `
        <div class="co-card co-success">
          <div class="co-success-icon"><svg viewBox="0 0 52 52" aria-hidden="true"><circle cx="26" cy="26" r="24"/><path d="M15 27l7 7 15-16"/></svg></div>
          <h1 tabindex="-1">سفارش شما با موفقیت ثبت شد</h1>
          <p>${order.status === 'paid' ? 'پرداخت با موفقیت انجام شد.' : transfer ? 'پس از واریز، رسید را از بخش سفارش‌ها ارسال کنید.' : 'مبلغ سفارش هنگام تحویل دریافت می‌شود.'} جزئیات سفارش برای شماره موبایل شما پیامک شد.</p>
          <div class="co-order-id"><span>کد سفارش</span><b>${order.id}</b><button type="button" data-copy="${order.id}"><svg><use href="#i-link"/></svg>کپی</button></div>
          ${transfer ? '<div class="co-transfer"><span>شماره کارت فروشگاه</span><b dir="ltr">۶۰۳۷ ۹۹۷۵ ۱۲۳۴ ۵۶۷۸</b><small>به نام: فروشگاه رنگ صادقی — بانک ملی</small></div>' : ''}
          <dl class="co-success-grid">
            <div><dt>مبلغ ${order.status === 'paid' ? 'پرداخت‌شده' : 'قابل پرداخت'}</dt><dd>${money(order.total)}</dd></div>
            <div><dt>روش پرداخت</dt><dd>${order.payment}</dd></div>
            <div><dt>شیوه ارسال</dt><dd>${order.method}</dd></div>
            <div><dt>زمان تحویل</dt><dd>${order.when}</dd></div>
            <div class="wide"><dt>آدرس</dt><dd>${esc(order.address)}</dd></div>
          </dl>
          <div class="co-success-thumbs">${order.items.map(item => `<span class="co-thumb"><img src="${imagePath(item.image)}" alt="${esc(item.title)}">${item.quantity > 1 ? `<em>${toFa(item.quantity)}</em>` : ''}</span>`).join('')}</div>
          <ol class="co-timeline">
            <li class="is-done"><i><svg><use href="#i-check"/></svg></i><span>ثبت سفارش</span></li>
            <li class="${order.status === 'paid' ? 'is-done' : ''}"><i><svg><use href="#i-card"/></svg></i><span>تأیید پرداخت</span></li>
            <li><i><svg><use href="#i-box"/></svg></i><span>آماده‌سازی</span></li>
            <li><i><svg><use href="#i-truck"/></svg></i><span>ارسال</span></li>
            <li><i><svg><use href="#i-home"/></svg></i><span>تحویل</span></li>
          </ol>
          <div class="co-success-actions">
            <a class="btn btn-primary" href="order-details.html?id=${encodeURIComponent(order.id)}"><svg><use href="#i-box"/></svg>پیگیری سفارش</a>
            <a class="btn btn-outline" href="shop.html"><svg><use href="#i-bag"/></svg>ادامه خرید</a>
          </div>
        </div>`;
      $('#co-step-done [data-copy]')?.addEventListener('click', async event => {
        try { await navigator.clipboard.writeText(event.currentTarget.dataset.copy); showToast('کد سفارش کپی شد'); }
        catch { showToast(`کد سفارش: ${order.id}`); }
      });
    }

    function renderEmpty() {
      layout.hidden = true;
      $('#co-empty').hidden = false;
      $('#co-mobile-bar').classList.add('is-empty');
      $$('#co-steps button').forEach(button => { button.disabled = true; });
    }

    /* ---------- رویدادها ---------- */
    $('#co-address-list').addEventListener('change', event => {
      if (event.target.name !== 'co-address') return;
      state.address = event.target.value;
      $('#co-address-error').hidden = true;
      renderAddresses(); persist(); renderSummary();
    });

    const form = $('#co-address-form');
    const addButton = $('#co-add-address');
    const toggleForm = open => {
      form.hidden = !open;
      addButton.hidden = open;
      addButton.setAttribute('aria-expanded', String(open));
      if (open) form.elements.title.focus();
    };
    addButton.addEventListener('click', () => toggleForm(true));
    $('#co-cancel-address').addEventListener('click', () => { form.reset(); $$('.field', form).forEach(f => f.classList.remove('has-error')); $$('.error', form).forEach(e => { e.textContent = ''; }); toggleForm(false); });
    $$('input, textarea', form).forEach(input => input.addEventListener('input', () => { if (input.closest('.has-error')) fieldError(input, ''); }));
    form.addEventListener('submit', event => {
      event.preventDefault();
      if (!validateNewAddress()) return;
      const v = name => form.elements[name].value.trim();
      const item = {
        id: `new-${Date.now()}`, title: v('title'), name: v('name'), phone: toEn(v('phone')).replace(/[\s-]/g, ''),
        province: v('province'), city: v('city'), postal: toEn(v('postal')).replace(/[\s-]/g, ''), address: v('address'), isNew: true
      };
      state.addresses = [...state.addresses, item];
      state.address = item.id;
      form.reset();
      toggleForm(false);
      renderAddresses(); persist();
      showToast('آدرس جدید ثبت و انتخاب شد', 'i-pin');
    });

    $('#co-receiver-self').checked = state.self;
    $('#co-receiver').hidden = state.self;
    $('#rc-name').value = state.receiver.name || '';
    $('#rc-phone').value = state.receiver.phone ? toFa(state.receiver.phone) : '';
    $('#co-receiver-self').addEventListener('change', event => {
      state.self = event.target.checked;
      $('#co-receiver').hidden = state.self;
      if (!state.self) $('#rc-name').focus();
      persist();
    });

    $('#co-methods').addEventListener('change', event => {
      if (event.target.name !== 'co-method') return;
      state.method = event.target.value;
      if (!paymentAvailable(state.payment)) state.payment = 'gateway';
      renderMethods(); renderSummary(); persist();
    });
    $('#co-days').addEventListener('click', event => {
      const button = event.target.closest('[data-day]');
      if (!button) return;
      state.day = Number(button.dataset.day);
      if (slotDisabled(state.day, state.slot)) state.slot = firstFreeSlot(state.day);
      renderSlots(); persist();
    });
    $('#co-slots').addEventListener('click', event => {
      const button = event.target.closest('[data-slot]');
      if (!button || button.disabled) return;
      state.slot = Number(button.dataset.slot);
      renderSlots(); persist();
    });
    /* کیبورد: پیمایش با جهت‌نماها در روز/ساعت */
    [['#co-days', '[data-day]'], ['#co-slots', '[data-slot]:not([disabled])']].forEach(([box, selector]) => {
      $(box).addEventListener('keydown', event => {
        if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
        const buttons = $$(selector, $(box));
        const index = buttons.indexOf(document.activeElement);
        if (index < 0) return;
        event.preventDefault();
        const nextIndex = (index + (event.key === 'ArrowLeft' ? 1 : -1) + buttons.length) % buttons.length;
        buttons[nextIndex].click();
        $$(selector, $(box))[nextIndex]?.focus();
      });
    });

    $('#co-gift').addEventListener('change', event => { state.gift = event.target.checked; renderSummary(); persist(); });
    $('#co-note').addEventListener('input', event => { state.note = event.target.value; persist(); });

    $('#co-payments').addEventListener('change', event => {
      if (event.target.name !== 'co-payment') return;
      state.payment = event.target.value;
      renderPayments(); renderSummary(); persist();
    });
    $('#co-wallet').addEventListener('change', event => {
      state.wallet = event.target.checked;
      $('.co-wallet').classList.toggle('is-on', state.wallet);
      renderSummary(); persist();
    });
    $('#co-invoice').addEventListener('change', event => { state.invoice = event.target.checked; persist(); });
    $('#co-terms').addEventListener('change', event => { if (event.target.checked) $('#co-terms-error').hidden = true; });

    $('#co-coupon-form').addEventListener('submit', event => {
      event.preventDefault();
      const input = $('#co-coupon-input');
      const note = $('#co-coupon-note');
      const code = toEn(input.value).trim().toUpperCase();
      if (!code) { note.textContent = 'کد تخفیف را وارد کنید.'; note.className = 'coupon-note is-error'; return; }
      if (COUPONS[code]) {
        state.coupon = code;
        note.textContent = `کد «${code}» اعمال شد؛ ${toFa(COUPONS[code])}٪ تخفیف روی مبلغ کالاها.`;
        note.className = 'coupon-note is-ok';
        showToast('کد تخفیف اعمال شد', 'i-ticket');
      } else {
        state.coupon = null;
        note.textContent = 'این کد تخفیف معتبر نیست یا منقضی شده است.';
        note.className = 'coupon-note is-error';
      }
      renderSummary(); renderReview(); persist();
    });

    ['#co-next', '#co-side-next', '#co-mobile-next'].forEach(selector => $(selector).addEventListener('click', next));
    $('#co-back').addEventListener('click', back);
    document.addEventListener('click', event => {
      const target = event.target.closest('[data-goto]');
      if (!target || target.disabled) return;
      if (!layout.contains(target) && !target.closest('#co-steps')) return;
      event.preventDefault();
      goTo(target.dataset.goto);
    });
    window.addEventListener('popstate', event => {
      if (state.step === 'done') return;
      const step = event.state?.coStep || 'address';
      if (STEPS.includes(step) && step !== 'done') showStep(step, { push: false });
    });

    /* ---------- شروع ---------- */
    if (!totals().count) { renderEmpty(); return; }
    renderAddresses();
    const hashStep = window.location.hash.replace('#', '');
    let start = 'address';
    if ((hashStep === 'shipping' || hashStep === 'payment') && currentAddress()) start = hashStep;
    if (history.replaceState) history.replaceState({ coStep: start }, '', `#${start}`);
    showStep(start, { push: false, scroll: false });
  }


  /* ========================================================== جزئیات سفارش */
  function order() {
    if (!document.querySelector('.od-hero') || !window.RS) return;

    const DEMO_ITEMS = [
      { id: 'p2', quantity: 1 },
      { id: 'p10', quantity: 2 },
      { id: 'p1', quantity: 1 }
    ];

    const toEn = value => String(value || '').replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d));
    const normId = value => toEn(value).replace(/\s/g, '').toUpperCase();
    const money = value => `${formatPrice(Math.round(value))} تومان`;

    /* پیدا کردن سفارش: از آدرس (?id=) یا آخرین سفارش ثبت‌شده در مرورگر */
    const params = new URLSearchParams(window.location.search);
    const wanted = params.get('id');
    let stored = [];
    try { stored = JSON.parse(localStorage.getItem('sadeghi-orders') || '[]'); } catch { /* نادیده */ }
    let current = null;
    if (wanted) current = stored.find(item => normId(item.id) === normId(wanted)) || null;
    else if (stored.length) current = stored[0];

    const baseItems = DEMO_ITEMS.map(entry => {
      const product = getProduct(entry.id) || {};
      return {
        title: product.title || 'محصول', image: product.image || 'sale-kardak.webp', cat: product.cat || 'رنگ و پوشش',
        quantity: entry.quantity, price: product.price || 0, old: product.old || product.price || 0, url: productUrl(entry.id)
      };
    });

    let items = baseItems;
    if (current && Array.isArray(current.items) && current.items.length) {
      items = current.items.map(item => {
        const product = getProduct(item.id) || {};
        const price = product.price || Math.round((current.total || 0) / Math.max(1, current.items.length));
        return {
          title: item.title || product.title || 'محصول', image: item.image || product.image || 'sale-kardak.webp',
          cat: product.cat || 'رنگ و پوشش', quantity: item.quantity || 1,
          price, old: product.old || price, url: productUrl(item.id)
        };
      });
    }

    const subtotal = items.reduce((sum, item) => sum + item.price * item.quantity, 0);
    const beforeDiscount = items.reduce((sum, item) => sum + (item.old || item.price) * item.quantity, 0);
    const count = items.reduce((sum, item) => sum + item.quantity, 0);
    const storedTotal = current
      ? Number(current.total) || 0
      : baseItems.reduce((sum, item) => sum + item.price * item.quantity, 0) + 35000;
    const discount = Math.max(0, beforeDiscount - subtotal);
    const paid = current ? storedTotal : subtotal + 35000;
    const shippingFinal = current ? Math.max(0, paid - subtotal) : 35000;

    /* --- سربرگ --- */
    const setText = (selector, value) => { const el = $(selector); if (el) el.textContent = value; };
    const code = current ? current.id : 'RS-۱۴۰۵۰۸۲۳';
    setText('#od-code', code);
    if (current) {
      setText('#od-date', current.date || '—');
      setText('#od-total', money(paid));
      setText('#od-tracking', toFa('2456789012345'));
      const status = $('#od-status');
      if (status) {
        const map = { paid: ['is-shipping', 'i-truck', 'پرداخت‌شده — در حال آماده‌سازی'], pending: ['is-processing', 'i-clock', 'در انتظار پرداخت'] };
        const [cls, , label] = map[current.status] || map.pending;
        status.className = `status-chip ${cls}`;
        status.innerHTML = `<svg><use href="#i-box"/></svg>${label}`;
      }
    }
    setText('#od-count', `${toFa(count)} عدد کالا`);
    const heroCopy = $('.od-hero [data-copy]');
    if (heroCopy) heroCopy.dataset.copy = code;

    /* --- اقلام سفارش --- */
    const list = $('#od-items-list');
    if (list) {
      list.innerHTML = items.map(item => `
        <article class="od-item">
          <a class="od-item-img" href="${item.url}"><img src="${imagePath(item.image)}" alt="${item.title}" loading="lazy"></a>
          <div class="od-item-body">
            <a class="od-item-title" href="${item.url}">${item.title}</a>
            <div class="od-item-meta"><span>فروشگاه رنگ صادقی</span><span aria-hidden="true">•</span><span>ارسال از انبار آمل</span><span aria-hidden="true">•</span><span>گارانتی اصالت کالا</span></div>
            <div class="od-item-flags"><span class="mini-flag">${current && current.status === 'pending' ? 'در انتظار پرداخت' : 'در حال ارسال'}</span><span class="mini-flag is-muted">${item.cat}</span></div>
            <div class="od-item-actions">
              <a class="cart-line-btn" href="${item.url}"><svg><use href="#i-eye"/></svg>مشاهده محصول</a>
              <button class="cart-line-btn" type="button" data-toast="درخواست مرجوعی این کالا ثبت شد (نمایشی)"><svg><use href="#i-refresh"/></svg>درخواست مرجوعی</button>
              <button class="cart-line-btn" type="button" data-toast="ثبت دیدگاه پس از تحویل کالا فعال می‌شود"><svg><use href="#i-quote"/></svg>ثبت دیدگاه</button>
            </div>
          </div>
          <div class="od-item-price">
            ${item.old > item.price ? `<span class="old">${money(item.old * item.quantity)}</span>` : ''}
            <b>${formatPrice(item.price * item.quantity)}</b>
            <small>تومان × ${toFa(item.quantity)} عدد</small>
          </div>
        </article>`).join('');
      list.querySelectorAll('[data-toast]').forEach(button => button.addEventListener('click', () => showToast(button.dataset.toast)));
    }
    setText('#od-items-hint', `${toFa(count)} عدد کالا در ۱ بسته`);

    /* --- جدول فاکتور --- */
    const body = $('#od-invoice-body');
    if (body) {
      body.innerHTML = items.map((item, index) => `
        <tr>
          <td>${toFa(index + 1)}</td>
          <td>${item.title}</td>
          <td>${toFa(item.quantity)}</td>
          <td>${formatPrice(item.price)}</td>
          <td>${formatPrice(item.price * item.quantity)}</td>
        </tr>`).join('');
    }

    /* --- جمع‌بندی‌ها --- */
    document.querySelectorAll('.od-totals, .od-summary').forEach(box => {
      const values = {
        before: money(beforeDiscount), discount: `− ${money(discount)}`,
        shipping: money(shippingFinal), paid: money(paid)
      };
      box.querySelectorAll('div').forEach(row => {
        const label = row.querySelector('span');
        const value = row.querySelector('b');
        if (!label || !value) return;
        const text = label.textContent.trim();
        if (text.includes('کد تخفیف')) return;                 /* ردیف کد تخفیف دست‌نخورده بماند */
        if (text.includes('پیش از تخفیف')) value.textContent = values.before;
        else if (text.includes('تخفیف')) value.textContent = values.discount;
        else if (text.includes('هزینه ارسال')) value.textContent = values.shipping;
        else if (text.includes('پرداخت‌شده')) value.textContent = values.paid;
        else if (text.includes('جمع کالاها')) value.textContent = values.before;
      });
    });
    const sumCount = $('#od-summary-title')?.closest('.od-summary')?.querySelector('.od-sum-row span');
    if (sumCount) sumCount.textContent = `جمع کالاها (${toFa(count)} عدد)`;

    /* --- کپی کدها --- */
    $$('[data-copy]').forEach(button => button.addEventListener('click', async () => {
      const value = button.dataset.copy || '';
      try {
        await navigator.clipboard.writeText(value);
        showToast('کپی شد: ' + value, 'i-link');
      } catch {
        showToast('کپی ممکن نشد؛ کد را دستی بردارید', 'i-info');
      }
    }));

    /* --- چاپ فاکتور --- */
    $$('[data-print]').forEach(button => button.addEventListener('click', () => {
      showToast('برای ذخیره فاکتور، در پنجره چاپ «Save as PDF» را انتخاب کنید', 'i-download');
      setTimeout(() => window.print(), 350);
    }));

    /* --- لغو سفارش --- */
    $('#od-cancel')?.addEventListener('click', () => {
      const status = $('#od-status');
      if (status && status.dataset.cancelled) { showToast('این سفارش قبلاً لغو شده است', 'i-info'); return; }
      if (!window.confirm('آیا از لغو این سفارش مطمئن هستید؟ مبلغ تا ۷۲ ساعت کاری بازگردانده می‌شود.')) return;
      if (status) {
        status.dataset.cancelled = 'true';
        status.className = 'status-chip is-processing';
        status.innerHTML = '<svg><use href="#i-close"/></svg>لغو شده — در انتظار بازگشت وجه';
      }
      $$('.od-steps li').forEach((step, index) => {
        step.classList.remove('is-active');
        if (index > 2) { step.classList.remove('is-done'); }
      });
      showToast('سفارش لغو شد و درخواست بازگشت وجه ثبت شد', 'i-check-circle');
      try {
        const orders = JSON.parse(localStorage.getItem('sadeghi-orders') || '[]');
        const index = orders.findIndex(item => normId(item.id) === normId(code));
        if (index > -1) { orders[index].status = 'cancelled'; localStorage.setItem('sadeghi-orders', JSON.stringify(orders)); }
      } catch { /* نادیده */ }
    });

    /* --- ناوبری داخلی --- */
    const pills = $$('.od-subnav a');
    const sections = pills.map(pill => $(pill.getAttribute('href'))).filter(Boolean);
    pills.forEach(pill => pill.addEventListener('click', event => {
      const target = $(pill.getAttribute('href'));
      if (!target) return;
      event.preventDefault();
      window.scrollTo({ top: target.getBoundingClientRect().top + window.scrollY - 90, behavior: 'smooth' });
      pills.forEach(item => item.classList.toggle('is-active', item === pill));
      history.replaceState && history.replaceState(null, '', pill.getAttribute('href'));
    }));
    if ('IntersectionObserver' in window && sections.length) {
      const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
          if (!entry.isIntersecting) return;
          const active = pills.find(pill => pill.getAttribute('href') === '#' + entry.target.id);
          if (active) pills.forEach(item => item.classList.toggle('is-active', item === active));
        });
      }, { rootMargin: '-110px 0px -65% 0px', threshold: 0 });
      sections.forEach(section => observer.observe(section));
    }
  }

  /* ========================================================= حساب کاربری */
  function account() {
    const tabs = $$('[data-account-tab]');
    const panels = $$('.account-panel');

    function activate(name) {
      tabs.forEach(tab => tab.classList.toggle('active', tab.dataset.accountTab === name && tab.closest('.account-nav')));
      panels.forEach(panel => panel.classList.toggle('active', panel.id === `panel-${name}`));
      if (window.innerWidth <= 1024) {
        const nav = $('.account-nav');
        nav?.querySelector(`[data-account-tab="${name}"]`)?.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
      }
      window.scrollTo({ top: $('.account-layout')?.offsetTop - 90 || 0, behavior: 'smooth' });
    }

    tabs.forEach(tab => tab.addEventListener('click', () => activate(tab.dataset.accountTab)));

    /* سفارش‌های ثبت‌شده از صفحه تسویه حساب */
    try {
      const orders = JSON.parse(localStorage.getItem('sadeghi-orders') || '[]');
      const head = $('#panel-orders .panel-head');
      if (orders.length && head) {
        head.insertAdjacentHTML('afterend', orders.map(order => `
          <article class="order-card is-new-order">
            <header class="order-head">
              <div><b>${order.id}</b><span>${order.date}</span></div>
              <span class="status-chip is-processing">${order.status === 'paid' ? 'پرداخت‌شده — در صف آماده‌سازی' : 'در انتظار پرداخت'}</span>
            </header>
            <div class="order-body">
              <div class="order-thumbs">${(order.items || []).slice(0, 4).map(item => `<img src="${imagePath(item.image)}" alt="">`).join('')}</div>
              <p class="order-desc">${(order.items || []).map(item => item.title).join('، ')}<br><small>${order.method} — ${order.when}</small></p>
              <div class="order-price"><span>مبلغ کل</span><b>${formatPrice(order.total)} تومان</b></div>
            </div>
            <footer class="order-foot">
              <a class="btn btn-outline btn-sm" href="order-details.html?id=${encodeURIComponent(order.id)}"><svg><use href="#i-box"/></svg>جزئیات سفارش</a>
              <span class="order-pay">${order.payment}</span>
            </footer>
          </article>`).join(''));
      }
    } catch { /* نادیده */ }

    /* باز کردن تب از روی آدرس (مثلاً account.html#orders) */
    const hashTab = window.location.hash.replace('#', '');
    if (hashTab && $(`#panel-${hashTab}`)) activate(hashTab);

    /* علاقه‌مندی‌ها از localStorage */
    function renderFavorites() {
      const wishlist = window.RS.wishlist;
      const items = [...wishlist].map(getProduct).filter(Boolean);
      const grid = $('#account-favorites');
      const strip = $('#overview-favorites');
      const counter = $('#favorites-count');
      const quick = $('#quick-wishlist');

      if (quick) quick.textContent = toFa(items.length);
      if (counter) counter.textContent = items.length ? `${toFa(items.length)} کالای ذخیره‌شده` : '';

      if (grid) {
        grid.innerHTML = items.length
          ? items.map(product => productCard(product)).join('')
          : `<div class="empty-state" style="grid-column:1/-1"><svg><use href="#i-heart"/></svg><h3>هنوز محصولی ذخیره نکرده‌اید</h3><p>با زدن آیکون قلب روی هر محصول، آن را برای بررسی بعدی ذخیره کنید.</p><a class="btn btn-primary btn-sm" href="shop.html">مشاهده محصولات</a></div>`;
      }
      if (strip) {
        strip.innerHTML = items.length
          ? items.slice(0, 8).map(product => `
            <a class="fav-card" href="${productUrl(product.id)}">
              <img src="${imagePath(product.image)}" alt="${product.title}" loading="lazy">
              <strong>${product.title}</strong>
              <span class="fav-foot">${formatPrice(product.price)} تومان</span>
            </a>`).join('')
          : '<p style="font-size:12px;color:#9a9a9a;margin:0">لیست علاقه‌مندی شما خالی است.</p>';
      }
    }

    renderFavorites();
    document.addEventListener('rs:ready', renderFavorites);
    $$('[data-wish]').forEach(button => button.addEventListener('click', () => setTimeout(renderFavorites, 60)));
  }

  /* ============================================================ تماس با ما */
  function contact() {
    const form = $('#contact-form');
    if (!form) return;
    const fields = $$('input, textarea', form).filter(el => el.type !== 'checkbox');

    function validate() {
      let valid = true;
      fields.forEach(field => {
        const error = field.closest('.field')?.querySelector('.error');
        let message = '';
        const value = field.value.trim();
        if (field.required && !value) message = 'این فیلد الزامی است.';
        else if (field.type === 'tel' && value && !/^0?9\d{9}$/.test(value.replace(/\D/g, ''))) message = 'شماره موبایل معتبر نیست.';
        else if (field.type === 'email' && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) message = 'ایمیل معتبر نیست.';
        if (error) error.textContent = message;
        if (message) { valid = false; field.style.borderColor = '#df3639'; } else { field.style.borderColor = ''; }
      });
      return valid;
    }

    fields.forEach(field => field.addEventListener('input', () => {
      const error = field.closest('.field')?.querySelector('.error');
      if (error) error.textContent = '';
      field.style.borderColor = '';
    }));

    form.addEventListener('submit', event => {
      event.preventDefault();
      if (!validate()) { showToast('لطفاً خطاهای فرم را برطرف کنید', 'i-close'); return; }
      const button = $('button[type="submit"]', form);
      const original = button.innerHTML;
      button.disabled = true;
      button.innerHTML = 'در حال ارسال…';
      setTimeout(() => {
        button.disabled = false;
        button.innerHTML = original;
        form.reset();
        showToast('پیام شما ارسال شد؛ به‌زودی با شما تماس می‌گیریم');
      }, 900);
    });
  }

  /* ============================================================ درباره ما */
  function about() {
    const stats = $$('[data-count]');
    if (!stats.length) return;
    const animate = el => {
      const target = Number(el.dataset.count);
      const duration = 1100;
      const started = performance.now();
      const step = now => {
        const ratio = Math.min((now - started) / duration, 1);
        el.textContent = toFa(Math.round(target * (1 - Math.pow(1 - ratio, 3))));
        if (ratio < 1) requestAnimationFrame(step);
      };
      requestAnimationFrame(step);
    };
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) { animate(entry.target); observer.unobserve(entry.target); }
      });
    }, { threshold: .4 });
    stats.forEach(stat => observer.observe(stat));
  }

  /* ======================================================= فروشگاه های ما */
  function stores() {
    const tabs = $$('[data-store-tab]');
    tabs.forEach(tab => tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.toggle('active', t === tab));
    }));
  }
})();
