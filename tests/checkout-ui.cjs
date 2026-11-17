/* Requires jsdom: npm install --no-save jsdom */
const { JSDOM } = require('jsdom');
const fs = require('node:fs');
const assert = require('node:assert/strict');
(async () => {
const dom = new JSDOM(`<!doctype html><html><body>
<div class="woocommerce-form-coupon-toggle">native coupon fallback</div>
<ol class="rs-checkout-steps"><li data-rs-indicator="cart"></li><li data-rs-indicator="address"></li><li data-rs-indicator="shipping"></li><li data-rs-indicator="payment"></li><li data-rs-indicator="done"></li></ol>
<form class="rs-native-checkout"><section data-rs-checkout-panel="address">
<div data-rs-saved-list hidden><button type="button" class="rs-saved-address" data-address='{"name":"Ali","city":"Amol","province":"Mazandaran","postal":"123","address":"Street"}'></button></div>
<label class="validate-required"><input id="billing_first_name" value="Ali"></label><input id="billing_city"><input id="billing_address_1"><select id="billing_state"><option value="">Select</option><option value="MAZ">Mazandaran</option></select><input id="billing_email" value="test@example.test">
<div class="rs-order-notes"><textarea name="order_comments">message</textarea></div></section>
<section data-rs-checkout-panel="review"><h2 data-rs-review-heading></h2><p data-rs-review-description></p><div class="woocommerce-checkout-review-order-table"><div class="shipping"><input type="radio" id="s1" name="shipping_method[0]" checked><label for="s1">Real shipping</label></div></div><div id="payment"><input type="radio" name="payment_method" id="pay1" checked><label for="pay1">Real gateway</label></div></section>
<div data-rs-shipping-extra><input type="date" id="rs_delivery_day" min="2030-01-01"><select id="rs_delivery_slot"><option value="">Choose</option><option>Morning</option></select><input type="checkbox" name="rs_gift_wrap"></div>
<div data-rs-payment-extra><div data-rs-final-review hidden><b data-rs-review-address></b><b data-rs-review-shipping></b><b data-rs-review-payment></b><button type="button" data-rs-edit-address></button></div><div data-rs-coupon-card hidden><input data-rs-coupon-input><button type="button" data-rs-coupon-submit></button><p data-rs-coupon-notice></p></div></div>
<nav><button type="button" data-rs-checkout-back></button><button type="button" data-rs-checkout-next></button></nav></form>
</body></html>`, { url: 'https://example.test/checkout/', runScripts: 'outside-only' });
const w = dom.window;
const events = [], handlers = {}, requests = [];
w.jQuery = () => ({ on(name, handler) { handlers[name] = handler; }, trigger(name) { events.push(name); } });
w.RS_WP = { cartUrl: '/cart/', wishlist: [] };
w.wc_checkout_params = { wc_ajax_url: '/?wc-ajax=%%endpoint%%', apply_coupon_nonce: 'nonce' };
w.fetch = async (url, options) => { requests.push({ url, options }); return { ok: true, text: async () => '<div class="woocommerce-message">Coupon applied</div>' }; };
w.matchMedia = () => ({ matches: false });
w.eval(fs.readFileSync('resources/js/theme.js', 'utf8'));
const form = w.document.querySelector('form');
const next = form.querySelector('[data-rs-checkout-next]');
const back = form.querySelector('[data-rs-checkout-back]');
const address = form.querySelector('[data-rs-checkout-panel="address"]');
const field = address.querySelector('#billing_first_name');
Object.defineProperty(field, 'offsetParent', { get: () => address.hidden ? null : address });
assert.equal(form.dataset.rsStep, 'address');
assert.equal(form.querySelector('[data-rs-checkout-panel="review"]').hidden, true);
assert.equal(form.querySelector('[data-rs-saved-list]').hidden, false);
field.value = ''; next.click(); assert.equal(form.dataset.rsStep, 'address'); assert.equal(w.document.activeElement, field);
form.querySelector('.rs-saved-address').click();
assert.equal(field.value, 'Ali'); assert.equal(form.querySelector('#billing_state').value, 'MAZ');
assert.equal(form.querySelector('#billing_address_1').value, 'Street');
next.click(); assert.equal(form.dataset.rsStep, 'shipping'); assert(events.includes('update_checkout'));
assert.equal(w.document.querySelector('[data-rs-indicator="shipping"]').getAttribute('aria-current'), 'step');
assert.equal(w.document.querySelector('.rs-checkout-steps').style.getPropertyValue('--p'), '0.5');
assert(form.querySelector('[data-rs-shipping-extra] .rs-order-notes textarea'));
form.querySelector('#rs_delivery_day').value = '2030-01-02'; next.click(); assert.equal(form.dataset.rsStep, 'shipping');
form.querySelector('#rs_delivery_slot').value = 'Morning'; next.click(); assert.equal(form.dataset.rsStep, 'payment'); assert.equal(next.hidden, true);
assert(form.querySelector('[data-rs-review-address]').textContent.includes('Street'));
assert.equal(form.querySelector('[data-rs-review-shipping]').textContent, 'Real shipping');
assert.equal(form.querySelector('[data-rs-review-payment]').textContent, 'Real gateway');
assert.equal(form.querySelector('[data-rs-coupon-card]').hidden, false);
form.querySelector('[data-rs-coupon-input]').value = 'REAL';
form.querySelector('[data-rs-coupon-submit]').click();
await new Promise(resolve => setTimeout(resolve, 0));
assert.equal(requests[0].url, '/?wc-ajax=apply_coupon');
assert.equal(requests[0].options.body.get('security'), 'nonce');
assert.equal(requests[0].options.body.get('coupon_code'), 'REAL');
assert.equal(form.querySelector('[data-rs-coupon-notice]').textContent, 'Coupon applied');
back.click(); assert.equal(form.dataset.rsStep, 'shipping');
const updates = events.filter(e => e === 'update_checkout').length;
form.querySelector('[name=rs_gift_wrap]').click(); assert(events.filter(e => e === 'update_checkout').length > updates);
next.click(); field.parentElement.classList.add('woocommerce-invalid'); handlers.checkout_error();
assert.equal(form.dataset.rsStep, 'address'); assert.equal(w.document.activeElement, field);
assert.equal(w.document.querySelector('[data-rs-indicator="done"]').className, '');
console.log('Checkout step, real input, coupon and fragment UI passed.'); w.close();
})().catch(e => { console.error(e); process.exitCode = 1; });
