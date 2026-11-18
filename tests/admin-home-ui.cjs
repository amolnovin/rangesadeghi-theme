'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const {JSDOM} = require('jsdom');
(async () => {
  const dom = new JSDOM(`<!doctype html><body><div id="rs-admin-notice"></div>
  <textarea data-opt-json="home.hero_slides">[{"desktop":"old.jpg","custom":"preserve"}]</textarea>
  <textarea data-opt-json="home.hero_side_cards">[]</textarea><textarea data-opt-json="home.projects">[]</textarea>
  <button id="rs-save-options-btn">Save</button></body>`, {url:'https://example.test/wp-admin/',runScripts:'outside-only'});
  const w=dom.window; let payload;
  w.RS_ADMIN={ajaxUrl:'/admin-ajax.php',nonce:'nonce'};
  w.document.querySelector('#rs-admin-notice').scrollIntoView=()=>{};
  w.fetch=async (url,opts) => {payload=JSON.parse(opts.body.get('options_json')); return {json:async()=>({success:true,data:{message:'ok'}})};};
  w.eval(fs.readFileSync('resources/js/admin-panel.js','utf8'));
  const source=w.document.querySelector('[data-opt-json="home.hero_slides"]');
  assert(source.hidden); const editor=source.previousElementSibling;
  const image=editor.querySelector('input'); image.value='new.jpg'; image.dispatchEvent(new w.Event('input',{bubbles:true}));
  assert.equal(JSON.parse(source.value)[0].custom,'preserve');
  editor.querySelector(':scope > button').click(); assert.equal(editor.querySelectorAll('fieldset').length,2);
  w.document.querySelector('#rs-save-options-btn').click(); await new Promise(r=>setTimeout(r,0));
  assert.equal(payload.home.hero_slides[0].desktop,'new.jpg'); assert.equal(payload.home.hero_slides[0].custom,'preserve'); assert.equal(payload.home.hero_slides.length,2);
  dom.window.close(); console.log('Visual homepage editor saves cards without losing unknown fields.');
})().catch(error=>{console.error(error);process.exitCode=1;});
