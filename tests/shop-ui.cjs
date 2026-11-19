'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const { JSDOM } = require('jsdom');
const script = fs.readFileSync('resources/js/theme.js', 'utf8');
const tick = () => new Promise(resolve => setTimeout(resolve, 0));
function setup(html, url = 'https://example.test/shop/') {
  const dom = new JSDOM('<!doctype html><body>' + html + '</body>', { url, runScripts: 'outside-only' });
  const w = dom.window; w.RS_WP = { ajaxUrl:'/admin-ajax.php', nonce:'abc', wishlist:[] };
  w.matchMedia = () => ({ matches: false }); w.scrollTo = () => {};
  return {dom, w};
}
(async () => {
  {
    const {dom,w} = setup(`<div id="page-overlay"></div><button data-open-filters>Filters</button><aside id="shop-sidebar"><button class="rs-close-filters">Close</button></aside><div id="shop-grid"><div id="first"></div></div><nav id="shop-pagination" data-mode="more"><a class="rs-load-more" href="?paged=2">More</a></nav>`);
    w.fetch = async () => ({ ok:true, text:async () => '<div id="shop-grid"><div id="second"></div></div><nav id="shop-pagination" data-mode="more"></nav>' });
    w.eval(script); const q = s => w.document.querySelector(s);
    q('[data-open-filters]').click(); assert(q('#shop-sidebar').classList.contains('is-open')); assert(q('#page-overlay').classList.contains('active')); assert(w.document.body.classList.contains('no-scroll'));
    q('.rs-close-filters').click(); assert(!q('#shop-sidebar').classList.contains('is-open')); assert(!q('#page-overlay').classList.contains('active'));
    q('[data-open-filters]').click(); w.document.dispatchEvent(new w.KeyboardEvent('keydown',{key:'Escape',bubbles:true})); assert(!q('#shop-sidebar').classList.contains('is-open'));
    q('.rs-load-more').click(); await tick(); await tick(); assert(q('#second')); assert(!q('.rs-load-more')); dom.window.close();
  }
  {
    const variants = JSON.stringify([{variation_id:11,attributes:{attribute_color:'red',attribute_size:'small'},price_fa:'۱۰۰',is_in_stock:true,is_purchasable:true},{variation_id:22,attributes:{attribute_color:'blue',attribute_size:'large'},price_fa:'۲۰۰',is_in_stock:true,is_purchasable:true}]);
    const {dom,w} = setup(`<div data-single-product data-needs-preparation="1" data-variations='${variants}'><span id="product-stock"></span><div class="price-block"><span id="product-price"></span></div><div data-wc-attribute="attribute_color"><button class="active" data-attr-val="red"></button><button data-attr-val="blue"></button></div><div data-wc-attribute="attribute_size"><button class="active" data-attr-val="small"></button><button data-attr-val="large"></button></div><input type="checkbox" id="rs-product-consent"><button data-single-add="7" disabled></button></div><div class="sticky-buy"><span id="sticky-price"></span><button data-single-add="7" disabled></button></div>`);
    const calls=[]; w.fetch=async (url,options) => { calls.push(options.body); return {json:async()=>({success:false,data:{message:'test'}})}; };
    let bottom=50; w.document.querySelector('.price-block').getBoundingClientRect = () => ({bottom});
    w.eval(script); const q=s=>w.document.querySelector(s), add=q('[data-single-add]');
    assert.equal(q('#product-stock').textContent, 'نیازمند آماده‌سازی'); assert(add.disabled); assert(!q('.sticky-buy').classList.contains('is-visible')); bottom=-1; w.dispatchEvent(new w.Event('scroll')); assert(q('.sticky-buy').classList.contains('is-visible'));
    q('#rs-product-consent').click(); assert(!add.disabled); q('[data-attr-val="blue"]').click(); assert(add.disabled, 'nonexistent combination must not be purchased'); assert.equal(q('#product-stock').textContent, 'ناموجود');
    q('[data-attr-val="large"]').click(); assert(!add.disabled); assert.equal(q('#product-stock').textContent, 'نیازمند آماده‌سازی'); assert.equal(q('#product-price').textContent,'۲۰۰'); add.click(); await tick();
    assert.equal(calls[0].get('variation_id'),'22'); assert.equal(calls[0].get('rs_product_consent'),'yes');
    q('#rs-product-consent').click(); assert(add.disabled); bottom=10; w.dispatchEvent(new w.Event('scroll')); assert(!q('.sticky-buy').classList.contains('is-visible')); dom.window.close();
  }
  {
    const {dom,w}=setup(`<div id="page-overlay"></div><aside id="mobile-menu-drawer"><button data-drawer-tab="nav" class="active" aria-pressed="true">menu</button><button data-drawer-tab="cats" aria-pressed="false">categories</button><nav id="mobile-drawer-nav">menu</nav><div id="mobile-drawer-categories" hidden><details><summary>parent</summary><a href="/child">child</a></details></div></aside>`);
    w.eval(script); const q=s=>w.document.querySelector(s);
    q('[data-drawer-tab="cats"]').click(); assert(!q('#mobile-drawer-categories').hidden); assert(q('#mobile-drawer-nav').hidden); assert.equal(q('[data-drawer-tab="cats"]').getAttribute('aria-pressed'),'true');
    q('[data-drawer-tab="nav"]').click(); assert(q('#mobile-drawer-categories').hidden); assert(!q('#mobile-drawer-nav').hidden); dom.window.close();
  }
  {
    const {dom,w}=setup(`<form data-newsletter-form data-newsletter-action="rs_home_newsletter_subscribe"><input name="contact" value="home@example.test"><button type="submit">submit</button></form>`);
    let body; w.fetch=async (url,opts)=>{body=opts.body;return {json:async()=>({success:true,data:{message:'ok'}})};}; w.eval(script);
    w.document.querySelector('form').dispatchEvent(new w.Event('submit',{bubbles:true,cancelable:true})); await tick(); await tick();
    assert.equal(body.get('action'),'rs_home_newsletter_subscribe'); assert.equal(body.get('contact'),'home@example.test'); dom.window.close();
  }
  {
    const {dom,w}=setup(`<div class="search-box"><form data-search-form><input type="search" name="s"></form><div class="search-results"></div></div>`);
    const requests=[]; w.fetch=(url)=>new Promise(resolve=>requests.push({url,resolve})); w.eval(script);
    const input=w.document.querySelector('input'), box=w.document.querySelector('.search-box'), results=w.document.querySelector('.search-results');
    input.value='old'; input.dispatchEvent(new w.Event('focus')); assert.equal(requests.length,1);
    input.value='new'; input.dispatchEvent(new w.Event('focus')); assert.equal(requests.length,2);
    const payload=(title)=>({success:true,data:{count_fa:'۱',search_url:'/search',items:[{url:'/p',image:'/i',title,price_formatted:'۱۰۰ تومان',stock_label:'ناموجود',in_stock:false}]}});
    requests[1].resolve({json:async()=>payload('new title')}); await tick(); await tick();
    assert(results.textContent.includes('new title')); assert(results.textContent.includes('ناموجود')); assert(results.querySelector('.search-see-all'));
    requests[0].resolve({json:async()=>payload('old title')}); await tick(); await tick();
    assert(!results.textContent.includes('old title'), 'Stale response replaced newer search');
    input.dispatchEvent(new w.KeyboardEvent('keydown',{key:'Escape',bubbles:true})); assert(!box.classList.contains('open')); dom.window.close();
  }
  {
    const {dom,w}=setup(`<form id="login-form"><input id="mobile-number" value="admin"><input id="modal-password" value="secret"><input name="wpmc_token" value="token"><input name="wpmc_answer" value="5"><button class="wpmc-refresh" type="button"></button></form>`);
    let refresh=0, body; w.fetch=async (url,opts) => {body=opts.body; return {json:async()=>({success:false,data:{message:'bad'}})};};
    w.document.querySelector('.wpmc-refresh').addEventListener('click',()=>refresh++); w.eval(script);
    w.document.querySelector('form').dispatchEvent(new w.Event('submit',{bubbles:true,cancelable:true})); await tick(); await tick();
    assert.equal(body.get('wpmc_token'),'token'); assert.equal(body.get('wpmc_answer'),'5'); assert.equal(refresh,1); dom.window.close();
  }
  console.log('Shop drawer, pagination, variation consent, sticky bar and modal captcha UI passed.');
})().catch(error => {console.error(error); process.exitCode=1;});
