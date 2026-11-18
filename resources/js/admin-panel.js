(function () {
  'use strict';

  const cfg = window.RS_ADMIN || {};
  const noticeEl = document.getElementById('rs-admin-notice');

  function notify(message, ok = true) {
    if (!noticeEl) return;
    noticeEl.textContent = message;
    noticeEl.className = 'rs-admin-notice ' + (ok ? 'is-ok' : 'is-err');
    noticeEl.hidden = false;
    noticeEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  async function postAjax(action, extra = {}) {
    const fd = new FormData();
    fd.append('action', action);
    fd.append('nonce', cfg.nonce || '');
    Object.entries(extra).forEach(([k, v]) => fd.append(k, v));

    const res = await fetch(cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: fd });
    return res.json();
  }

  /* جابه‌جایی تب‌ها */
  document.querySelectorAll('[data-rs-tab]').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = btn.dataset.rsTab;
      document.querySelectorAll('[data-rs-tab]').forEach(b => b.classList.toggle('is-active', b === btn));
      document.querySelectorAll('[data-rs-panel]').forEach(p => p.classList.toggle('is-active', p.dataset.rsPanel === target));
      const url = new URL(window.location.href);
      url.searchParams.set('tab', target);
      window.history.replaceState({}, '', url.toString());
    });
  });

  /* انتخابگر کتابخانه رسانه وردپرس */
  document.querySelectorAll('.rs-media-pick').forEach(btn => {
    btn.addEventListener('click', () => {
      if (!window.wp || !window.wp.media) {
        notify('کتابخانه رسانه وردپرس در دسترس نیست.', false);
        return;
      }
      const input = btn.parentElement.querySelector('input');
      const frame = window.wp.media({
        title: 'انتخاب تصویر',
        button: { text: 'استفاده از این تصویر' },
        multiple: false
      });
      frame.on('select', () => {
        const attachment = frame.state().get('selection').first().toJSON();
        if (input && attachment && attachment.url) {
          input.value = attachment.url;
        }
      });
      frame.open();
    });
  });

  /* ویرایش بصری آرایه‌های صفحه اصلی، بدون حذف کلیدهای تنظیمات موجود */
  const visualFields = {
    'home.hero_slides': [['desktop','تصویر دسکتاپ','image'],['mobile','تصویر موبایل','image'],['alt','متن جایگزین'],['link','پیوند']],
    'home.hero_side_cards': [['image','تصویر','image'],['tag','برچسب'],['tag_style','رنگ برچسب (red یا gold)'],['title','عنوان'],['subtitle','زیرعنوان'],['url','پیوند دلخواه'],['cat_slug','نامک دسته'],['sale_only','فقط تخفیف‌دار','bool']],
    'home.projects': [['image','تصویر','image'],['title','عنوان'],['subtitle','زیرعنوان'],['url','پیوند دلخواه'],['cat','نامک دسته'],['tall','کارت بلند','bool']]
  };
  Object.entries(visualFields).forEach(([key, fields]) => {
    const source = document.querySelector('[data-opt-json="' + key + '"]');
    if (!source) return;
    let items;
    try { items = JSON.parse(source.value || '[]'); if (!Array.isArray(items)) return; }
    catch { return; } // فیلد JSON برای اصلاح داده نامعتبر باقی می‌ماند.
    const editor = document.createElement('div');
    const list = document.createElement('div');
    const add = document.createElement('button');
    add.type = 'button'; add.className = 'button'; add.textContent = 'افزودن کارت';
    editor.append(list, add);
    source.before(editor);
    source.hidden = true;
    const sync = () => { source.value = JSON.stringify(items); };
    const render = () => {
      list.replaceChildren();
      items.forEach((item, index) => {
        if (!item || typeof item !== 'object' || Array.isArray(item)) item = items[index] = {};
        const card = document.createElement('fieldset');
        card.style.cssText = 'border:1px solid #ddd;padding:12px;margin:12px 0;border-radius:8px';
        const legend = document.createElement('legend'); legend.textContent = 'کارت ' + (index + 1); card.append(legend);
        fields.forEach(([field, label, type]) => {
          const row = document.createElement('div'); row.style.cssText = 'margin:8px 0';
          const caption = document.createElement('label'); caption.textContent = label + ' '; row.append(caption);
          const input = document.createElement('input'); input.type = type === 'bool' ? 'checkbox' : 'text';
          if (type === 'bool') input.checked = !!item[field]; else { input.value = item[field] || ''; input.style.width = '65%'; }
          input.addEventListener('input', () => { item[field] = type === 'bool' ? input.checked : input.value; sync(); });
          caption.append(input);
          if (type === 'image') {
            const pick = document.createElement('button'); pick.type = 'button'; pick.className = 'button'; pick.textContent = 'انتخاب تصویر';
            pick.addEventListener('click', () => {
              if (!window.wp?.media) return notify('کتابخانه رسانه در دسترس نیست.', false);
              const frame = window.wp.media({title:'انتخاب تصویر', button:{text:'استفاده از تصویر'}, multiple:false});
              frame.on('select', () => { const image = frame.state().get('selection').first().toJSON(); if (image?.url) { input.value = item[field] = image.url; sync(); } });
              frame.open();
            }); row.append(' ', pick);
          }
          card.append(row);
        });
        [['↑', -1], ['↓', 1]].forEach(([label, delta]) => {
          const btn = document.createElement('button'); btn.type = 'button'; btn.className = 'button'; btn.textContent = label;
          btn.disabled = index + delta < 0 || index + delta >= items.length;
          btn.addEventListener('click', () => { const other = index + delta; [items[index], items[other]] = [items[other], items[index]]; sync(); render(); }); card.append(btn, ' ');
        });
        const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'button'; remove.textContent = 'حذف';
        remove.addEventListener('click', () => { items.splice(index, 1); sync(); render(); }); card.append(remove); list.append(card);
      });
    };
    add.addEventListener('click', () => { items.push({}); sync(); render(); });
    render();
  });

  /* جمع‌آوری تمام فیلدها در ساختار درختی options */
  function collectOptions() {
    const out = {};
    const setDeep = (path, val) => {
      const parts = path.split('.');
      let cur = out;
      for (let i = 0; i < parts.length - 1; i++) {
        cur[parts[i]] = cur[parts[i]] || {};
        cur = cur[parts[i]];
      }
      cur[parts[parts.length - 1]] = val;
    };

    document.querySelectorAll('[data-opt]').forEach(el => {
      const key = el.dataset.opt;
      if (el.type === 'checkbox') {
        setDeep(key, el.checked);
      } else if (el.type === 'number') {
        setDeep(key, Number(el.value || 0));
      } else {
        setDeep(key, el.value);
      }
    });

    for (const el of document.querySelectorAll('[data-opt-json]')) {
      const key = el.dataset.optJson;
      try {
        setDeep(key, JSON.parse(el.value || '[]'));
      } catch (err) {
        throw new Error('خطای ساختار JSON در فیلد ' + key);
      }
    }

    return out;
  }

  /* ذخیره تنظیمات */
  document.getElementById('rs-save-options-btn')?.addEventListener('click', async function () {
    let payload;
    try {
      payload = collectOptions();
    } catch (e) {
      notify(e.message, false);
      return;
    }

    this.disabled = true;
    const orig = this.textContent;
    this.textContent = 'در حال ذخیره…';

    try {
      const res = await postAjax('rs_admin_save_options', { options_json: JSON.stringify(payload) });
      notify(res.data?.message || 'تنظیمات ذخیره شد.', !!res.success);
    } catch {
      notify('خطا در ارتباط با سرور وردپرس.', false);
    } finally {
      this.disabled = false;
      this.textContent = orig;
    }
  });

  /* پاکسازی کش */
  document.getElementById('rs-flush-cache-btn')?.addEventListener('click', async () => {
    const res = await postAjax('rs_admin_flush_cache');
    notify(res.data?.message || 'کش پاکسازی شد.', !!res.success);
  });

  /* درون‌ریزی دمو */
  document.getElementById('rs-seed-demo-btn')?.addEventListener('click', async function () {
    if (!window.confirm('آیا مایل به ایجاد خودکار برگه‌ها، دسته‌بندی‌ها، برندها، ۳۲ محصول نمونه و ۸ مقاله آموزشی هستید؟ (موارد تکراری ایجاد نمی‌شوند)')) return;
    this.disabled = true;
    const orig = this.textContent;
    this.textContent = 'در حال درون‌ریزی…';
    try {
      const res = await postAjax('rs_admin_seed_demo');
      notify(res.data?.message || 'درون‌ریزی انجام شد.', !!res.success);
    } finally {
      this.disabled = false;
      this.textContent = orig;
    }
  });

  /* خروجی JSON تنظیمات */
  document.getElementById('rs-export-json-btn')?.addEventListener('click', async () => {
    const res = await postAjax('rs_admin_export_options');
    if (!res.success) return notify('خطا در تهیه خروجی.', false);
    const blob = new Blob([res.data.json], { type: 'application/json' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = res.data.filename || 'rangesadeghi-settings.json';
    a.click();
  });

  /* درون‌ریزی JSON تنظیمات */
  document.getElementById('rs-import-json-input')?.addEventListener('change', async function () {
    const file = this.files?.[0];
    if (!file) return;
    const text = await file.text();
    const res = await postAjax('rs_admin_import_options', { json: text });
    notify(res.data?.message || '', !!res.success);
    if (res.success) setTimeout(() => window.location.reload(), 900);
  });

  /* بازنشانی تنظیمات */
  document.getElementById('rs-reset-options-btn')?.addEventListener('click', async () => {
    if (!window.confirm('تمام تنظیمات قالب به حالت پیش‌فرض بازنشانی شوند؟')) return;
    const res = await postAjax('rs_admin_reset_options');
    notify(res.data?.message || '', !!res.success);
    if (res.success) setTimeout(() => window.location.reload(), 900);
  });

  /* آپلود و نصب پچ آفلاین */
  document.getElementById('rs-apply-patch-btn')?.addEventListener('click', async function () {
    const fileInput = document.getElementById('rs-patch-file-input');
    const file = fileInput?.files?.[0];
    if (!file) return notify('ابتدا فایل پچ (.zip یا .json) را انتخاب کنید.', false);

    this.disabled = true;
    const orig = this.textContent;
    this.textContent = 'در حال بررسی امضای SHA-256 و اعمال پچ…';
    try {
      const res = await postAjax('rs_admin_upload_patch', { patch_file: file });
      notify(res.data?.message || 'نتیجه نامشخص', !!res.success);
      if (res.success) setTimeout(() => window.location.reload(), 1200);
    } finally {
      this.disabled = false;
      this.textContent = orig;
    }
  });

  /* بررسی آنلاین سرور پچ */
  document.getElementById('rs-check-remote-patch-btn')?.addEventListener('click', async function () {
    const installBtn = document.getElementById('rs-install-remote-patch-btn');
    const box = document.getElementById('rs-remote-patch-result');
    this.disabled = true;
    try {
      const res = await postAjax('rs_admin_check_patch');
      if (!res.success) {
        notify(res.data?.message || 'خطا در بررسی بروزرسانی.', false);
        return;
      }
      const d = res.data;
      if (box) {
        box.innerHTML = `<div class="rs-admin-notice ${d.has_update ? 'is-ok' : ''}" style="margin-top:8px">${d.message}${d.notes ? '<br><small>' + d.notes + '</small>' : ''}</div>`;
      }
      if (installBtn) installBtn.hidden = !d.has_update;
    } finally {
      this.disabled = false;
    }
  });

  /* نصب پچ آنلاین */
  document.getElementById('rs-install-remote-patch-btn')?.addEventListener('click', async function () {
    this.disabled = true;
    this.textContent = 'در حال دانلود و نصب پچ…';
    try {
      const res = await postAjax('rs_admin_install_remote_patch');
      notify(res.data?.message || '', !!res.success);
      if (res.success) setTimeout(() => window.location.reload(), 1200);
    } finally {
      this.disabled = false;
    }
  });

  document.getElementById('rs-feedback-test-send')?.addEventListener('click', async function () {
    const id = document.getElementById('rs-feedback-test-order')?.value;
    if (!id || !window.confirm('پیامک واقعی به شماره مشتری سفارش ' + id + ' ارسال شود؟')) return;
    this.disabled = true;
    try { const res = await postAjax('rs_delivery_test', { order_id: id }); notify(res.data?.message || 'نتیجه نامشخص', !!res.success); }
    catch (_) { notify('خطا در ارتباط با سرور.', false); }
    finally { this.disabled = false; }
  });

  /* بازگردانی یک‌کلیکی (Rollback) */
  document.querySelectorAll('.rs-rollback-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
      const backupId = btn.dataset.backupId;
      if (!window.confirm('آیا قالب به بکاپ ' + backupId + ' بازگردانی شود؟')) return;
      const res = await postAjax('rs_admin_rollback_patch', { backup_id: backupId });
      notify(res.data?.message || '', !!res.success);
      if (res.success) setTimeout(() => window.location.reload(), 1100);
    });
  });
})();
