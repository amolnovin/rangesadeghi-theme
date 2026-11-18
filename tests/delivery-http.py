#!/usr/bin/env python3
"""Exercise the public feedback form via a real WordPress HTTP request, without sending SMS."""
import base64, datetime, html.parser, http.client, json, os, socket, subprocess, sys, time, urllib.parse
root, cli, fixture = sys.argv[1:]
data = json.loads(fixture)
server = subprocess.Popen(['php', '-S', '127.0.0.1:8765', '-t', root], stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
class Nonce(html.parser.HTMLParser):
    def __init__(self): super().__init__(); self.nonce = ''
    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        if tag == 'input' and a.get('name') == '_wpnonce': self.nonce = a.get('value', '')
def call(path, form=None, photo=None):
    conn = http.client.HTTPConnection('127.0.0.1', 8765, timeout=10)
    headers = {'Host': 'example.test'}
    if form is None: conn.request('GET', path, headers=headers)
    else:
        if photo:
            boundary = 'rsfeedbacktestboundary'
            headers['Content-Type'] = 'multipart/form-data; boundary=' + boundary
            chunks = []
            for name, value in form.items():
                chunks.append((f'--{boundary}\r\nContent-Disposition: form-data; name="{name}"\r\n\r\n{value}\r\n').encode())
            name, content = photo
            chunks.append((f'--{boundary}\r\nContent-Disposition: form-data; name="photos[]"; filename="{name}"\r\nContent-Type: image/png\r\n\r\n').encode() + content + b'\r\n')
            chunks.append(f'--{boundary}--\r\n'.encode())
            payload = b''.join(chunks)
        else:
            headers['Content-Type'] = 'application/x-www-form-urlencoded'
            payload = urllib.parse.urlencode(form)
        conn.request('POST', path, payload, headers)
    res = conn.getresponse(); text = res.read().decode('utf-8'); status = res.status; location = res.getheader('Location'); conn.close()
    if form is None and status in (301, 302, 303, 307, 308) and location:
        target = urllib.parse.urlsplit(urllib.parse.urljoin('http://example.test' + path, location))
        if target.hostname == 'example.test' and target.path != urllib.parse.urlsplit(path).path:
            return call(target.path + ('?' + target.query if target.query else ''))
        raise AssertionError(f'Unexpected redirect: {path} => {location}')
    return status, text
try:
    for _ in range(100):
        if server.poll() is not None: raise RuntimeError('PHP server exited')
        try:
            s = socket.create_connection(('127.0.0.1', 8765), timeout=.1); s.close(); break
        except OSError: time.sleep(.1)
    else: raise RuntimeError('PHP server did not start')
    for query, expected in [('/?post_type=product&s=Delivery+feedback', 'shop-grid'), ('/?s=Hello', '<main'), ('/?post_type=product&s=nothing-matches-xyz', 'shop-grid')]:
        status, html = call(query)
        assert status == 200 and expected in html, f'Search produced an error or blank page: {query}; HTTP {status}; response: {html[:500]!r}'
    # The block/Store API must not add a protected product with no consent.
    conn = http.client.HTTPConnection('127.0.0.1', 8765, timeout=10)
    conn.request('GET', '/?rest_route=/wc/store/v1/cart', headers={'Host':'example.test'})
    resp = conn.getresponse(); resp.read(); nonce = resp.getheader('Nonce'); cart_token = resp.getheader('Cart-Token'); status = resp.status; conn.close()
    assert status == 200 and (nonce or cart_token), f'Store API cart failed: {status}'
    headers = {'Host':'example.test', 'Content-Type':'application/json'}
    if nonce: headers['Nonce'] = nonce
    if cart_token: headers['Cart-Token'] = cart_token
    conn = http.client.HTTPConnection('127.0.0.1', 8765, timeout=10)
    conn.request('POST', '/?rest_route=/wc/store/v1/cart/add-item', json.dumps({'id':data['product'], 'quantity':1}), headers)
    resp = conn.getresponse(); store_body = resp.read().decode(); status = resp.status; conn.close()
    assert status == 400 and 'rs_product_consent_required' in store_body, f'Store API bypassed consent: HTTP {status}: {store_body[:400]}'
    path = '/?rsd=' + str(data['id']) + '.' + data['token']
    code, body = call('/?rsd=' + str(data['id']) + '.' + '0'*22)
    assert code == 404 and 'delivery-feedback-form' not in body, 'Invalid token exposed a form'
    code, body = call(path); parser = Nonce(); parser.feed(body)
    assert code == 200 and parser.nonce and 'delivery-feedback-form' in body and 'Delivery feedback HTTP fixture' in body, 'Valid guest form did not render'
    code, body = call(path, {'_wpnonce': 'bad', 'received': 'yes'})
    assert 'درخواست معتبر نیست' in body, 'Bad nonce accepted'
    code, body = call(path, {'_wpnonce': parser.nonce, 'received': 'no', 'reason': 'unknown', 'contacted': 'no', 'no_detail': 'HTTP test'})
    assert code == 200 and 'وضعیت سفارش تغییر نکرد' in body, 'No-response form failed'
    dt = (datetime.datetime.now(datetime.timezone.utc) - datetime.timedelta(hours=1)).strftime('%Y-%m-%dT%H:%M')
    yes = {'_wpnonce': parser.nonce, 'received': 'yes', 'delivery_datetime': dt, 'pack_rating': 5, 'product_rating': 4, 'recommend': 'yes', 'product_id': data['product'], 'review': 'HTTP review'}
    code, body = call(path, yes, ('fake.png', b'not an image'))
    assert 'فقط تصاویر' in body, 'Invalid image accepted'
    png = base64.b64decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/a7sAAAAASUVORK5CYII=')
    code, body = call(path, yes, ('photo.png', png))
    assert code == 200 and 'تأیید تحویل شما' in body, 'Delivered form failed'
    code, body = call(path)
    assert 'قبلاً ثبت شده' in body and 'delivery-feedback-form' not in body, 'Completed token still allows resubmission'
    print('Public feedback HTTP and nonce checks passed.')
finally:
    server.terminate()
    try: server.wait(timeout=5)
    except subprocess.TimeoutExpired: server.kill(); server.wait()
