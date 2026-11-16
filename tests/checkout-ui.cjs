/* Requires jsdom: npm install --no-save jsdom */
const { JSDOM } = require('jsdom');
const fs = require('node:fs');
const assert = require('node:assert/strict');
const dom = new JSDOM(`<!doctype html><html><body>
<ol class="rs-checkout-steps"><li data-rs-indicator="address"></li><li data-rs-indicator="shipping"></li><li data-rs-indicator="payment"></li></ol>
<form class="rs-native-checkout"><section data-rs-checkout-panel="address"><label class="validate-required"><input id="billing_first_name" value="Ali"></label></section><section data-rs-checkout-panel="review"><div id="payment"></div></section><nav><button type="button" data-rs-checkout-back></button><button type="button" data-rs-checkout-next></button></nav></form>
</body></html>`, { url: 'https://example.test/checkout/', runScripts: 'outside-only' });
const w = dom.window;
const events = [];
w.jQuery = () => ({ on() {}, trigger(name) { events.push(name); } });
w.RS_WP = { cartUrl: '/cart/', wishlist: [] };
w.matchMedia = () => ({ matches: false });
w.eval(fs.readFileSync('resources/js/theme.js', 'utf8'));
const form = w.document.querySelector('form');
const next = form.querySelector('[data-rs-checkout-next]');
const back = form.querySelector('[data-rs-checkout-back]');
assert.equal(form.dataset.rsStep, 'address');
assert.equal(form.querySelector('[data-rs-checkout-panel="review"]').hidden, true);
next.click();
assert.equal(form.dataset.rsStep, 'shipping');
assert(events.includes('update_checkout'));
next.click();
assert.equal(form.dataset.rsStep, 'payment');
assert.equal(next.hidden, true);
back.click();
assert.equal(form.dataset.rsStep, 'shipping');
console.log('Checkout step UI passed.');
w.close();
