#!/usr/bin/env python3
"""Exercise front-end and AJAX search through WordPress, including authenticated requests."""
import http.client
import json
import re
import socket
import subprocess
import sys
import tempfile
import time
import urllib.parse

root, fixture = sys.argv[1:]
data = json.loads(fixture)
with tempfile.TemporaryFile(mode='w+t') as log:
    server = subprocess.Popen(['php', '-S', '127.0.0.1:8767', '-t', root], stdout=log, stderr=log)
    def request(path, cookie=None):
        headers = {'Host': 'example.test', 'User-Agent': 'Mozilla/5.0'}
        if cookie:
            headers['Cookie'] = data['cookie_name'] + '=' + data['cookie']
        conn = http.client.HTTPConnection('127.0.0.1', 8767, timeout=30)
        conn.request('GET', path, headers=headers)
        response = conn.getresponse()
        body = response.read().decode('utf-8', errors='replace')
        status, location = response.status, response.getheader('Location')
        conn.close()
        assert status == 200, f'{path} status {status} location={location}, body={body[:500]}'
        return body
    def search(q, page=None, cookie=False):
        params = {'post_type': 'product', 's': q}
        if page:
            params['paged'] = page
        return request('/?' + urllib.parse.urlencode(params), cookie)
    try:
        for _ in range(100):
            if server.poll() is not None:
                raise RuntimeError('PHP server exited')
            try:
                sock = socket.create_connection(('127.0.0.1', 8767), timeout=.1)
                sock.close()
                break
            except OSError:
                time.sleep(.1)
        else:
            raise RuntimeError('PHP server did not start')
        ajax = json.loads(request('/wp-admin/admin-ajax.php?' + urllib.parse.urlencode({'action': 'rs_live_search', 'q': 'پاستیل'})))
        assert ajax['success'] and len(ajax['data']['items']) == 4, 'Live search fixture not found'
        def live(q):
            return json.loads(request('/wp-admin/admin-ajax.php?' + urllib.parse.urlencode({'action': 'rs_live_search', 'q': q})))['data']
        arabic = live('اکریلیک 120')
        assert arabic['total'] == 1 and int(arabic['items'][0]['id']) == data['variant_id'], f'Persian and Arabic search variants failed: {arabic}'
        assert arabic['items'][0]['stock_label'] == 'ناموجود' and not arabic['items'][0]['in_stock'], 'Stock status missing from live search'
        for term in ('اكريليك ۱۲۰', 'اکریلیک ١٢٠', 'اکریلیک 120'):
            page = search(term)
            assert 'id="shop-grid"' in page and 'اكريليك' in page, f'Full search did not normalize {term}'
        bulk = live('انبوه')
        assert bulk['total'] == 9 and len(bulk['items']) == 7, f'Total results or live result limit incorrect: {bulk}'
        assert not {data['hidden_id'], data['catalog_id'], data['draft_id']} & {int(item['id']) for item in bulk['items']}, 'Private or search-hidden item leaked'
        bulk_page = search('انبوه')
        ids_on_page = re.findall(r'<article class="product-card"\s+data-product-id="(\d+)"', bulk_page)
        assert ids_on_page == [item['id'] for item in bulk['items'][:3]], f'Live search and full-page ordering disagree: {ids_on_page}'
        assert all(str(data[name]) not in ids_on_page for name in ('hidden_id', 'catalog_id', 'draft_id')), 'Search-hidden item on page'
        assert live("' OR 1=1 %_")['total'] == 0, 'Search wildcard or SQL injection changed query'
        for logged_in in (False, True):
            html = search('پاستیل', cookie=logged_in)
            assert '<html' in html.lower() and 'id="shop-grid"' in html and 'رنگ پاستیلی' in html, f'Product search page missing: length={len(html)}'
            assert html.count('class="product-card"') == 3, 'First page contains wrong number of results'
            if logged_in:
                assert 'wp-admin-bar-my-account' in html, 'Authenticated request was not logged in'
            assert 'pagination' in html, 'Pagination missing'
            second = search('پاستیل', page=2, cookie=logged_in)
            assert 'id="shop-grid"' in second and 'رنگ پاستیلی' in second, 'Second search page missing'
            one = search('یکتای جستجو', cookie=logged_in)
            assert 'id="shop-grid"' in one and 'کالای یکتای جستجو' in one, 'Single result did not render'
            none = search('no-such-search-fixture-zzzz', cookie=logged_in)
            assert 'id="shop-grid"' in none and 'محصولی با این فیلترها پیدا نشد' in none, 'Empty result did not render'
        blog = request('/?' + urllib.parse.urlencode({'s': 'مقاله جستجوی آزمایشی'}))
        assert '<main' in blog and 'مقاله جستجوی آزمایشی' in blog, 'Post search did not render'
        print('HTTP product search (guest/admin, AJAX, pagination, single, zero) and post search passed.')
    except Exception:
        log.seek(0)
        print(log.read()[-12000:], file=sys.stderr)
        raise
    finally:
        server.terminate()
        try:
            server.wait(timeout=5)
        except subprocess.TimeoutExpired:
            server.kill()
            server.wait()
