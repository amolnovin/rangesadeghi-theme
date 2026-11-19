'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const { JSDOM } = require('jsdom');
const css = fs.readFileSync('styles.css', 'utf8');
for (const bodyClass of ['search search-results', 'search search-no-results']) {
  const dom = new JSDOM(`<!doctype html><style>${css}</style><body class="${bodyClass}"><main id="main-content">Results</main><div class="search-box"><div class="search-results">Suggestions</div></div></body>`);
  const {window} = dom;
  const body = window.document.body;
  const box = body.querySelector('.search-box');
  const dropdown = box.querySelector('.search-results');
  assert.notEqual(window.getComputedStyle(body).display, 'none', `${bodyClass}: the page body must be visible`);
  assert.notEqual(window.getComputedStyle(body).visibility, 'hidden', `${bodyClass}: the page body must not be hidden`);
  assert.equal(window.getComputedStyle(dropdown).display, 'none', 'Suggestions should be hidden when search is closed');
  box.classList.add('open');
  assert.equal(window.getComputedStyle(dropdown).display, 'block', 'Suggestions should appear when search is open');
  dom.window.close();
}
console.log('WordPress search pages remain visible; suggestions open and close.');
