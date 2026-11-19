'use strict';
// Real Chromium layout test. The wallet markup models the installed plugin's before-payment hook;
// it is not a test of wallet accounting or payment processing.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const { chromium } = require('playwright');
const css = ['styles.css', 'pages.css', 'home.css', 'resources/css/wp-compat.css'].map(p => fs.readFileSync(p, 'utf8')).join('\n');
const script = fs.readFileSync('resources/js/theme.js', 'utf8');
const days = Array.from({length: 30}, (_, i) => `<label class="co-day" data-rs-courier-day="1" data-rs-pickup-day="1"><input type="radio" name="rs_delivery_day" value="day-${i}"><b>چهارشنبه</b><small>۰۴ آذر ۱۴۰۵</small></label>`).join('');
const html = `<!doctype html><html dir="rtl"><head><meta name="viewport" content="width=device-width,initial-scale=1"><style>${css}</style>
<style>.amol-wallet-checkout-box{background:#f0f9ff;padding:15px 20px;margin-bottom:20px}.amol-progress-text{display:flex;justify-content:space-between;gap:20px}</style></head>
<body class="page-checkout" data-page="checkout"><main class="page-main"><div class="page-container">
<ol class="co-steps rs-checkout-steps">${['cart','address','shipping','payment','done'].map(x=>`<li data-rs-indicator="${x}"><button class="co-step-btn"><b>شیوه ارسال سفارش</b></button></li>`).join('')}</ol>
<form class="checkout cart-layout co-layout rs-native-checkout"><div class="co-main"><section class="co-step" data-rs-checkout-panel="address"><p>نشانی</p></section>
<section class="co-step rs-review-step" data-rs-checkout-panel="review"><div id="order_review" class="woocommerce-checkout-review-order"><table class="woocommerce-checkout-review-order-table"><tfoot><tr class="shipping"><td><ul class="woocommerce-shipping-methods"><li><input type="radio" name="shipping_method[0]" id="rate" checked><label for="rate">پیک فروشگاه با نام بسیار طولانی جهت بررسی شکستن متن</label><span class="rs-rate-info" data-rs-rate-kind="rs_store_courier" hidden></span></li></ul></td></tr></tfoot></table>
<tr class="amol-wallet-payment-row"><td><div class="amol-wallet-checkout-box"><p>موجودی <strong class="woocommerce-Price-amount">107,000 تومان</strong></p><div class="amol-progress-text"><span>2% از حداقل مجاز</span><span>فاصله تا حداقل: 4,893,000 تومان</span></div><input type="number" name="amol_wallet_custom_amount" value="1000" max="5000000"></div></td></tr>
<div id="payment">درگاه پرداخت<button id="place_order" type="button">ثبت</button></div></div></section>
<div data-rs-shipping-extra><div class="co-card" data-rs-slot-card hidden><div class="co-days">${days}</div><div class="co-slots"><label class="co-slot"><input type="radio" name="rs_delivery_slot" value="Morning">۹ تا ۱۲</label></div></div></div>
<div data-rs-payment-extra><div class="co-card">پرداخت</div></div><nav class="rs-checkout-controls"><button type="button" data-rs-checkout-back>بازگشت</button><button type="button" data-rs-checkout-next>ادامه</button><button type="button" data-rs-place-order hidden>ثبت</button></nav></div><aside class="cart-side"><div class="summary-card"><span class="woocommerce-Price-amount">5,000,000</span></div></aside></form></div></main></body></html>`;
(async () => {
 const browser = await chromium.launch({headless: true});
 try {
  for (const width of [320, 360, 390, 768]) {
   const page = await browser.newPage({viewport: {width, height: 800}});
   await page.setContent(html); await page.addScriptTag({content: script});
   const form = page.locator('.rs-native-checkout');
   await page.locator('[data-rs-checkout-next]').click();
   assert.equal(await form.getAttribute('data-rs-step'), 'shipping');
   assert.equal(await page.locator('.amol-wallet-checkout-box').isVisible(), false, `wallet visible during shipping at ${width}`);
   const dims = await page.evaluate(() => ({ doc: document.documentElement.scrollWidth, view: document.documentElement.clientWidth, days: document.querySelector('.co-days').scrollWidth, daysWidth: document.querySelector('.co-days').clientWidth }));
   assert(dims.doc <= dims.view + 1, `horizontal page overflow at ${width}: ${JSON.stringify(dims)}`);
   assert(dims.days <= dims.daysWidth + 1, `days overflow at ${width}: ${JSON.stringify(dims)}`);
   await page.locator('.co-day').first().click(); await page.locator('.co-slot').first().click(); await page.locator('[data-rs-checkout-next]').click();
   assert.equal(await form.getAttribute('data-rs-step'), 'payment');
   assert.equal(await page.locator('.amol-wallet-checkout-box').isVisible(), true, `wallet hidden during payment at ${width}`);
   assert.equal(await page.locator('.amol-wallet-checkout-box .woocommerce-Price-amount').innerText(), '۱۰۷,۰۰۰ تومان');
   assert.equal(await page.locator('[name=amol_wallet_custom_amount]').inputValue(), '1000');
   assert(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1), `payment overflow at ${width}`);
   // Replacing the plugin fragment must not make it visible on shipping or break digit localization.
   await page.locator('[data-rs-checkout-back]').click();
   await page.evaluate(() => { const box = document.querySelector('.amol-wallet-checkout-box'); box.outerHTML = '<div class="amol-wallet-checkout-box"><span class="woocommerce-Price-amount">42,000</span></div>'; });
   await page.waitForFunction(() => document.querySelector('.amol-wallet-checkout-box').textContent.includes('۴۲,۰۰۰'));
   assert.equal(await page.locator('.amol-wallet-checkout-box').isVisible(), false);
   await page.close();
  }
 } finally { await browser.close(); }
 console.log('Chromium mobile checkout layout and wallet step visibility passed.');
})().catch(e => { console.error(e); process.exitCode = 1; });
