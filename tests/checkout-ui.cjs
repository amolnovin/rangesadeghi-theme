/* Requires jsdom: npm install --no-save jsdom */
const { JSDOM } = require('jsdom');
const fs = require('node:fs');
const assert = require('node:assert/strict');
(async () => {
const dom = new JSDOM(`<!doctype html><html><body>
<div class="woocommerce-form-coupon-toggle">native coupon fallback</div>
<ol class="rs-checkout-steps"><li data-rs-indicator="cart"></li><li data-rs-indicator="address"></li><li data-rs-indicator="shipping"></li><li data-rs-indicator="payment"></li><li data-rs-indicator="done"></li></ol>
<form class="rs-native-checkout"><section data-rs-checkout-panel="address">
<div data-rs-address-picker data-rs-requires-address="1" data-rs-submitted="0" hidden><div data-rs-saved-list>
<label class="rs-saved-address" data-address='{"id":"home","title":"Home","name":"Different Recipient","phone":"09123456789","city":"Amol","province":"Mazandaran","postal":"12345","address":"Home Street","isDefault":true}'><input type="radio" name="rs_saved_address" value="home"></label>
<label class="rs-saved-address" data-address='{"id":"work","title":"Work","city":"Sari","province":"Unknown","postal":"45678","address":"Work Street"}'><input type="radio" name="rs_saved_address" value="work"></label>
</div><p data-rs-address-empty hidden></p><button type="button" data-rs-add-address></button>
<div data-rs-new-address hidden><input data-rs-new="title"><input data-rs-new="name"><input data-rs-new="phone"><select data-rs-new="province"><option value="">Select</option><option value="MAZ">Mazandaran</option></select><input type="hidden" data-rs-new="country" value="IR"><input data-rs-new="city"><input data-rs-new="postal"><textarea data-rs-new="address"></textarea><p data-rs-new-error hidden></p><button type="button" data-rs-save-address></button><button type="button" data-rs-cancel-address></button></div></div>
<div class="rs-order-contact" data-rs-customer-fields><div data-rs-billing-field-home>
<p class="form-row validate-required"><input id="billing_first_name" value="Buyer"></p><p class="form-row validate-required"><input id="billing_last_name" value="Name"></p><p class="form-row validate-required"><input id="billing_phone" value="09120000000"></p><p class="form-row validate-required"><input id="billing_city"></p><p class="form-row"><input id="billing_postcode"></p><p class="form-row validate-required"><input id="billing_address_1"></p><p class="form-row"><input id="billing_address_2"></p><p class="form-row validate-required"><select id="billing_state"><option value="">Select</option><option value="MAZ">Mazandaran</option></select></p><p class="form-row validate-required"><input type="email" id="billing_email" value="test@example.test"></p><p class="form-row validate-required"><input id="billing_plugin" value="extra"></p>
</div></div><div data-rs-contact-completion hidden><div data-rs-contact-fields></div></div>
<label><input id="rs-receiver-self" type="checkbox" checked><span data-rs-self-info></span></label><div data-rs-receiver-fields><input id="rs-receiver-name"><input id="rs-receiver-phone"></div>

<div class="rs-order-notes"><textarea name="order_comments">message</textarea></div></section>
<section data-rs-checkout-panel="review"><h2 data-rs-review-heading></h2><p data-rs-review-description></p><div class="woocommerce-checkout-review-order-table"><div class="shipping"><ul class="woocommerce-shipping-methods"><li><input type="radio" id="s1" name="shipping_method[0]" checked><label for="s1">Real shipping</label><span class="rs-rate-info" data-rs-rate-kind="rs_store_courier" data-rs-rate-eta="" hidden></span></li><li><input type="radio" id="s3" name="shipping_method[0]"><label for="s3">Store pickup</label><span class="rs-rate-info" data-rs-rate-kind="rs_store_pickup" data-rs-rate-eta="" hidden></span></li><li><input type="radio" id="s2" name="shipping_method[0]"><label for="s2">Other method</label><span class="rs-rate-info" data-rs-rate-kind="flat_rate" data-rs-rate-eta="Three days" hidden></span></li></ul></div></div><div class="amol-wallet-checkout-box"><strong class="woocommerce-Price-amount">107,000</strong><span class="amol-progress-text">2% از حداقل: 5,000,000</span><input id="wallet-input" type="number" value="1000" min="1000" max="5000000"><span id="wallet-error"></span></div><div id="payment"><input type="radio" name="payment_method" id="pay1" checked><label for="pay1">Real gateway</label><div class="form-row place-order"><button type="submit" id="place_order">Pay securely</button></div></div></section>
<div data-rs-shipping-extra><div data-rs-slot-card hidden><label data-rs-courier-day="1" data-rs-pickup-day="1"><input type="radio" name="rs_delivery_day" value="2030-01-02"></label><label data-rs-courier-day="0" data-rs-pickup-day="0"><input type="radio" name="rs_delivery_day" value="2030-01-04"></label><label data-rs-courier-day="1" data-rs-pickup-day="1"><input type="radio" name="rs_delivery_day" value="2030-01-09"></label><label><input type="radio" name="rs_delivery_slot" value="Morning"></label></div><div data-rs-eta-card hidden><p data-rs-eta-text></p></div><input type="checkbox" name="rs_gift_wrap"></div>
<div data-rs-payment-extra><div data-rs-final-review><input type="checkbox" name="terms" id="terms"><b data-rs-review-address></b><b data-rs-review-shipping></b><b data-rs-review-payment></b><button type="button" data-rs-edit-address></button></div><div data-rs-coupon-card hidden><input data-rs-coupon-input><button type="button" data-rs-coupon-submit></button><p data-rs-coupon-notice></p></div></div>
<nav><button type="button" data-rs-checkout-back></button><button type="button" data-rs-checkout-next></button><button type="button" data-rs-place-order hidden></button></nav></form>
</body></html>`, { url: 'https://example.test/checkout/', runScripts: 'outside-only' });
const w = dom.window;
const events = [], handlers = {}, requests = [], scrolls = [];
w.scrollTo = options => scrolls.push(options);
w.jQuery = () => ({ on(name, handler) { handlers[name] = handler; }, trigger(name) { events.push(name); } });
w.RS_WP = { cartUrl: '/cart/', ajaxUrl: '/admin-ajax.php', nonce: 'public-nonce', wishlist: [] };
w.wc_checkout_params = { wc_ajax_url: '/?wc-ajax=%%endpoint%%', apply_coupon_nonce: 'nonce' };
w.fetch = async (url, options) => {
  requests.push({ url, options });
  if (url === '/admin-ajax.php') return { json: async () => ({ success: true, data: { message: 'Saved', address: { id: 'new', title: options.body.get('title'), name: options.body.get('name'), phone: options.body.get('phone'), city: options.body.get('city'), province: options.body.get('province'), province_label: 'Mazandaran', postal: options.body.get('postal'), address: options.body.get('address') } } }) };
  return { ok: true, text: async () => '<div class="woocommerce-message">Coupon applied</div>' };
};
w.matchMedia = () => ({ matches: false });
w.eval(fs.readFileSync('resources/js/theme.js', 'utf8'));
const form = w.document.querySelector('form'), q = sel => form.querySelector(sel);
const next = q('[data-rs-checkout-next]'), back = q('[data-rs-checkout-back]');
const address = q('[data-rs-checkout-panel="address"]'), field = q('#billing_first_name');
Object.defineProperty(field, 'offsetParent', { get: () => address.hidden ? null : address });
assert.equal(form.dataset.rsStep, 'address');
assert.equal(q('.woocommerce-Price-amount').textContent, '۱۰۷,۰۰۰');
assert(q('.amol-progress-text').textContent.includes('۲%') && q('.amol-progress-text').textContent.includes('۵,۰۰۰,۰۰۰'));
assert.equal(q('#wallet-input').value, '1000'); assert.equal(q('#wallet-input').getAttribute('max'), '5000000');
q('#wallet-error').textContent = 'حداقل 1,000 تومان';
await new Promise(resolve => setTimeout(resolve, 0));
assert.equal(q('#wallet-error').textContent, 'حداقل ۱,۰۰۰ تومان');

assert.equal(q('[data-rs-address-picker]').hidden, false);
assert.equal(q('.rs-saved-address input').checked, true);
assert.equal(q('#billing_address_1').value, 'Home Street');
assert.equal(q('#billing_state').value, 'MAZ');
assert.equal(q('#billing_plugin').closest('[data-rs-contact-completion]') !== null, true, 'Plugin billing fields remain accessible');
assert.equal(field.value, 'Buyer', 'choosing a delivery address must not overwrite billing identity');
assert.equal(q('#billing_last_name').value, 'Name');
assert.equal(q('[data-rs-receiver-fields]').hidden, true);
assert(q('[data-rs-self-info]').textContent.includes('Buyer'));
const work = q('[value=work]'); work.checked = true; work.dispatchEvent(new w.Event('change', { bubbles: true }));
assert.equal(q('#billing_address_1').value, 'Work Street'); assert.equal(q('#billing_state').value, '', 'unknown province must not become a fabricated state');
assert(q('#billing_state').closest('[data-rs-contact-completion]'), 'Unrecognized legacy state must remain editable');
q('[value=home]').click();
q('[data-rs-add-address]').click(); assert.equal(q('[data-rs-new-address]').hidden, false);
q('[data-rs-save-address]').click(); assert.equal(q('[data-rs-new-error]').hidden, false);
const newValues = { title: 'New place', name: 'Real User', phone: '09123456789', province: 'MAZ', city: 'Amol', postal: '', address: 'A new street' };
Object.entries(newValues).forEach(([key, val]) => { q(`[data-rs-new="${key}"]`).value = val; });
q('[data-rs-save-address]').click(); await new Promise(resolve => setTimeout(resolve, 0));
assert.equal(requests[0].options.body.get('action'), 'rs_checkout_address_save');
assert.equal(requests[0].options.body.get('nonce'), 'public-nonce');
assert.equal(q('[value=new]').checked, true); assert.equal(q('#billing_address_1').value, 'A new street');
assert.equal(q('#billing_state').value, 'MAZ'); assert.equal(q('#billing_postcode').value, '0000000000'); assert.equal(requests[0].options.body.get('country'), 'IR');
assert(q('#billing_state').closest('[data-rs-customer-fields]')); assert(q('[value=new]').closest('label').textContent.includes('Mazandaran'));
assert.equal(field.value, 'Buyer'); assert.equal(q('[data-rs-new-address]').hidden, true);
q('#rs-receiver-self').click(); assert.equal(q('[data-rs-receiver-fields]').hidden, false);
field.value = ''; next.click(); assert.equal(form.dataset.rsStep, 'address'); assert.equal(w.document.activeElement, field); assert(field.closest('[data-rs-contact-completion]'));
field.value = 'Buyer'; next.click(); assert.equal(form.dataset.rsStep, 'address'); assert.equal(w.document.activeElement, q('#rs-receiver-name'));
q('#rs-receiver-name').value = 'Other Person'; q('#rs-receiver-phone').value = '۰۹۱۲۳۴۵۶۷۸۹';
next.click(); assert.equal(form.dataset.rsStep, 'shipping'); assert(events.includes('update_checkout'));
assert.equal(w.document.querySelector('[data-rs-indicator="shipping"]').getAttribute('aria-current'), 'step');
assert(form.querySelector('[data-rs-shipping-extra] .rs-order-notes textarea'));
assert.equal(scrolls.length, 1, 'Successful step change should scroll to top');
const friday = q('[value="2030-01-04"]'); assert.equal(friday.disabled, true); assert.equal(friday.closest('label').hidden, true);
q('#s3').click(); assert.equal(friday.disabled, true); assert.equal(friday.checked, false); assert.equal(friday.closest('label').hidden, true);
assert.equal(q('[value="2030-01-09"]').disabled, false, 'Pickup extends available days after Friday');
q('#s1').click(); assert.equal(friday.disabled, true); assert.equal(q('[value="2030-01-09"]').disabled, false);
q('[name=rs_delivery_day]').click(); next.click(); assert.equal(form.dataset.rsStep, 'shipping');
q('[name=rs_delivery_slot]').click(); next.click(); assert.equal(form.dataset.rsStep, 'payment'); assert.equal(next.hidden, true);
assert.equal(scrolls.length, 2);
const proxy = q('[data-rs-place-order]'); assert.equal(proxy.hidden, false);
let submitted = 0; q('#place_order').addEventListener('click', e => { e.preventDefault(); submitted++; });
proxy.click(); assert.equal(submitted, 0, 'Cannot submit without agreeing to policies');
q('#terms').checked = true; proxy.click(); assert.equal(submitted, 1, 'Proxy should use real WooCommerce submit');
const newPlace = w.document.createElement('button'); newPlace.type = 'submit'; newPlace.id = 'place_order'; newPlace.textContent = 'Pay via gateway';
newPlace.addEventListener('click', e => { e.preventDefault(); submitted++; }); q('#place_order').replaceWith(newPlace);
handlers.updated_checkout(); assert.equal(proxy.textContent, 'Pay via gateway'); assert.equal(q('#terms').checked, true);
proxy.click(); assert.equal(submitted, 2, 'Proxy must use the latest WooCommerce fragment button');
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
