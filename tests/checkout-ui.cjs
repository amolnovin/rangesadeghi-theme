/* Requires jsdom: npm install --no-save jsdom */
const { JSDOM } = require('jsdom');
const fs = require('node:fs');
const assert = require('node:assert/strict');
(async () => {
const dom = new JSDOM(`<!doctype html><html><body>
<div class="woocommerce-form-coupon-toggle">native coupon fallback</div>
<ol class="rs-checkout-steps"><li data-rs-indicator="cart"></li><li data-rs-indicator="address"></li><li data-rs-indicator="shipping"></li><li data-rs-indicator="payment"></li><li data-rs-indicator="done"></li></ol>
<form class="rs-native-checkout"><section data-rs-checkout-panel="address">
<div data-rs-address-picker data-rs-submitted="0" hidden><div data-rs-saved-list>
<label class="rs-saved-address" data-address='{"id":"home","title":"Home","name":"Different Recipient","phone":"09123456789","city":"Amol","province":"Mazandaran","postal":"12345","address":"Home Street","isDefault":true}'><input type="radio" name="rs_saved_address" value="home"></label>
<label class="rs-saved-address" data-address='{"id":"work","title":"Work","city":"Sari","province":"Unknown","postal":"45678","address":"Work Street"}'><input type="radio" name="rs_saved_address" value="work"></label>
</div><p data-rs-address-empty hidden></p><button type="button" data-rs-add-address></button>
<div data-rs-new-address hidden><input data-rs-new="title"><input data-rs-new="name"><input data-rs-new="phone"><input data-rs-new="province"><input data-rs-new="city"><input data-rs-new="postal"><textarea data-rs-new="address"></textarea><p data-rs-new-error hidden></p><button type="button" data-rs-save-address></button><button type="button" data-rs-cancel-address></button></div></div>
<div data-rs-customer-fields><label class="validate-required"><input id="billing_first_name" value="Buyer"></label><input id="billing_last_name" value="Name"><input id="billing_phone" value="09120000000"><input id="billing_city"><input id="billing_postcode"><input id="billing_address_1"><input id="billing_address_2"><select id="billing_state"><option value="">Select</option><option value="MAZ">Mazandaran</option></select><input id="billing_email" value="test@example.test"></div>
<label><input id="rs-receiver-self" type="checkbox" checked><span data-rs-self-info></span></label><div data-rs-receiver-fields><input id="rs-receiver-name"><input id="rs-receiver-phone"></div>

<div class="rs-order-notes"><textarea name="order_comments">message</textarea></div></section>
<section data-rs-checkout-panel="review"><h2 data-rs-review-heading></h2><p data-rs-review-description></p><div class="woocommerce-checkout-review-order-table"><div class="shipping"><ul class="woocommerce-shipping-methods"><li><input type="radio" id="s1" name="shipping_method[0]" checked><label for="s1">Real shipping</label><span class="rs-rate-info" data-rs-rate-kind="rs_store_courier" data-rs-rate-eta="" hidden></span></li><li><input type="radio" id="s2" name="shipping_method[0]"><label for="s2">Other method</label><span class="rs-rate-info" data-rs-rate-kind="flat_rate" data-rs-rate-eta="Three days" hidden></span></li></ul></div></div><div id="payment"><input type="radio" name="payment_method" id="pay1" checked><label for="pay1">Real gateway</label></div></section>
<div data-rs-shipping-extra><div data-rs-slot-card hidden><label><input type="radio" name="rs_delivery_day" value="2030-01-02"></label><label><input type="radio" name="rs_delivery_slot" value="Morning"></label></div><div data-rs-eta-card hidden><p data-rs-eta-text></p></div><input type="checkbox" name="rs_gift_wrap"></div>
<div data-rs-payment-extra><div data-rs-final-review hidden><b data-rs-review-address></b><b data-rs-review-shipping></b><b data-rs-review-payment></b><button type="button" data-rs-edit-address></button></div><div data-rs-coupon-card hidden><input data-rs-coupon-input><button type="button" data-rs-coupon-submit></button><p data-rs-coupon-notice></p></div></div>
<nav><button type="button" data-rs-checkout-back></button><button type="button" data-rs-checkout-next></button></nav></form>
</body></html>`, { url: 'https://example.test/checkout/', runScripts: 'outside-only' });
const w = dom.window;
const events = [], handlers = {}, requests = [];
w.jQuery = () => ({ on(name, handler) { handlers[name] = handler; }, trigger(name) { events.push(name); } });
w.RS_WP = { cartUrl: '/cart/', ajaxUrl: '/admin-ajax.php', nonce: 'public-nonce', wishlist: [] };
w.wc_checkout_params = { wc_ajax_url: '/?wc-ajax=%%endpoint%%', apply_coupon_nonce: 'nonce' };
w.fetch = async (url, options) => {
  requests.push({ url, options });
  if (url === '/admin-ajax.php') return { json: async () => ({ success: true, data: { message: 'Saved', address: { id: 'new', title: options.body.get('title'), name: options.body.get('name'), phone: options.body.get('phone'), city: options.body.get('city'), province: options.body.get('province'), postal: options.body.get('postal'), address: options.body.get('address') } } }) };
  return { ok: true, text: async () => '<div class="woocommerce-message">Coupon applied</div>' };
};
w.matchMedia = () => ({ matches: false });
w.eval(fs.readFileSync('resources/js/theme.js', 'utf8'));
const form = w.document.querySelector('form'), q = sel => form.querySelector(sel);
const next = q('[data-rs-checkout-next]'), back = q('[data-rs-checkout-back]');
const address = q('[data-rs-checkout-panel="address"]'), field = q('#billing_first_name');
Object.defineProperty(field, 'offsetParent', { get: () => address.hidden ? null : address });
assert.equal(form.dataset.rsStep, 'address');
assert.equal(q('[data-rs-address-picker]').hidden, false);
assert.equal(q('.rs-saved-address input').checked, true);
assert.equal(q('#billing_address_1').value, 'Home Street');
assert.equal(q('#billing_state').value, 'MAZ');
assert.equal(field.value, 'Buyer', 'choosing a delivery address must not overwrite billing identity');
assert.equal(q('#billing_last_name').value, 'Name');
assert.equal(q('[data-rs-receiver-fields]').hidden, true);
assert(q('[data-rs-self-info]').textContent.includes('Buyer'));
const work = q('[value=work]'); work.checked = true; work.dispatchEvent(new w.Event('change', { bubbles: true }));
assert.equal(q('#billing_address_1').value, 'Work Street'); assert.equal(q('#billing_state').value, '', 'unknown province must not become a fabricated state');
q('[value=home]').click();
q('[data-rs-add-address]').click(); assert.equal(q('[data-rs-new-address]').hidden, false);
q('[data-rs-save-address]').click(); assert.equal(q('[data-rs-new-error]').hidden, false);
const newValues = { title: 'New place', name: 'Real User', phone: '09123456789', province: 'Mazandaran', city: 'Amol', postal: '12345', address: 'A new street' };
Object.entries(newValues).forEach(([key, val]) => { q(`[data-rs-new="${key}"]`).value = val; });
q('[data-rs-save-address]').click(); await new Promise(resolve => setTimeout(resolve, 0));
assert.equal(requests[0].options.body.get('action'), 'rs_checkout_address_save');
assert.equal(requests[0].options.body.get('nonce'), 'public-nonce');
assert.equal(q('[value=new]').checked, true); assert.equal(q('#billing_address_1').value, 'A new street');
assert.equal(field.value, 'Buyer'); assert.equal(q('[data-rs-new-address]').hidden, true);
q('#rs-receiver-self').click(); assert.equal(q('[data-rs-receiver-fields]').hidden, false);
field.value = ''; next.click(); assert.equal(form.dataset.rsStep, 'address'); assert.equal(w.document.activeElement, field);
field.value = 'Buyer'; next.click(); assert.equal(form.dataset.rsStep, 'address'); assert.equal(w.document.activeElement, q('#rs-receiver-name'));
q('#rs-receiver-name').value = 'Other Person'; q('#rs-receiver-phone').value = '۰۹۱۲۳۴۵۶۷۸۹';
next.click(); assert.equal(form.dataset.rsStep, 'shipping'); assert(events.includes('update_checkout'));
assert.equal(w.document.querySelector('[data-rs-indicator="shipping"]').getAttribute('aria-current'), 'step');
assert(form.querySelector('[data-rs-shipping-extra] .rs-order-notes textarea'));
q('[name=rs_delivery_day]').click(); next.click(); assert.equal(form.dataset.rsStep, 'shipping');
q('[name=rs_delivery_slot]').click(); next.click(); assert.equal(form.dataset.rsStep, 'payment'); assert.equal(next.hidden, true);
assert(q('[data-rs-review-address]').textContent.includes('A new street'));
assert.equal(q('[data-rs-review-shipping]').textContent, 'Real shipping'); assert.equal(q('[data-rs-review-payment]').textContent, 'Real gateway');
q('[data-rs-coupon-input]').value = 'REAL'; q('[data-rs-coupon-submit]').click(); await new Promise(resolve => setTimeout(resolve, 0));
assert.equal(requests[1].url, '/?wc-ajax=apply_coupon'); assert.equal(requests[1].options.body.get('security'), 'nonce');
assert.equal(q('[data-rs-coupon-notice]').textContent, 'Coupon applied');
back.click(); assert.equal(form.dataset.rsStep, 'shipping');
q('#s2').click(); assert.equal(q('[data-rs-slot-card]').hidden, true); assert.equal(q('[data-rs-eta-card]').hidden, false); assert.equal(q('[data-rs-eta-text]').textContent, 'Three days');
q('#s1').click(); assert.equal(q('[data-rs-slot-card]').hidden, false);
next.click(); field.parentElement.classList.add('woocommerce-invalid'); handlers.checkout_error();
assert.equal(form.dataset.rsStep, 'address'); assert.equal(w.document.activeElement, field);
q('#billing_city').value = 'Changed'; q('#billing_city').dispatchEvent(new w.Event('input', { bubbles: true }));
assert.equal(q('[value=new]').checked, false, 'manual changes must clear the selected saved address');
console.log('Checkout address, recipient, shipping and coupon UI passed.'); w.close();
})().catch(e => { console.error(e); process.exitCode = 1; });
