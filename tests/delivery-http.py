#!/usr/bin/env python3
"""Exercise the public feedback form via a real WordPress HTTP request, without sending SMS."""
import datetime, html.parser, http.client, json, os, socket, subprocess, sys, time, urllib.parse
root, cli, fixture = sys.argv[1:]
data = json.loads(fixture)
server = subprocess.Popen(['php', '-S', '127.0.0.1:8765', '-t', root], stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
class Nonce(html.parser.HTMLParser):
    def __init__(self): super().__init__(); self.nonce = ''
    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        if tag == 'input' and a.get('name') == '_wpnonce': self.nonce = a.get('value', '')
def call(path, form=None):
    conn = http.client.HTTPConnection('127.0.0.1', 8765, timeout=10)
    headers = {'Host': 'example.test'}
    if form is None: conn.request('GET', path, headers=headers)
    else:
        headers['Content-Type'] = 'application/x-www-form-urlencoded'
        conn.request('POST', path, urllib.parse.urlencode(form), headers)
    res = conn.getresponse(); text = res.read().decode('utf-8'); status = res.status; conn.close(); return status, text
try:
    for _ in range(100):
        if server.poll() is not None: raise RuntimeError('PHP server exited')
        try:
            s = socket.create_connection(('127.0.0.1', 8765), timeout=.1); s.close(); break
        except OSError: time.sleep(.1)
    else: raise RuntimeError('PHP server did not start')
    path = '/?rs_delivery=' + str(data['id']) + '&t=' + data['token']
    code, body = call('/?rs_delivery=' + str(data['id']) + '&t=' + '0'*32)
    assert code == 404 and 'delivery-feedback-form' not in body, 'Invalid token exposed a form'
    code, body = call(path); parser = Nonce(); parser.feed(body)
    assert code == 200 and parser.nonce and 'delivery-feedback-form' in body and 'Delivery feedback HTTP fixture' in body, 'Valid guest form did not render'
    code, body = call(path, {'_wpnonce': 'bad', 'received': 'yes'})
    assert 'درخواست معتبر نیست' in body, 'Bad nonce accepted'
    code, body = call(path, {'_wpnonce': parser.nonce, 'received': 'no', 'reason': 'unknown', 'contacted': 'no', 'no_detail': 'HTTP test'})
    assert code == 200 and 'وضعیت سفارش تغییر نکرد' in body, 'No-response form failed'
    dt = (datetime.datetime.now(datetime.timezone.utc) - datetime.timedelta(hours=1)).strftime('%Y-%m-%dT%H:%M')
    code, body = call(path, {'_wpnonce': parser.nonce, 'received': 'yes', 'delivery_datetime': dt, 'pack_rating': 5, 'product_rating': 4, 'recommend': 'yes', 'product_id': data['product'], 'review': 'HTTP review'})
    assert code == 200 and 'تأیید تحویل شما' in body, 'Delivered form failed'
    code, body = call(path)
    assert 'قبلاً ثبت شده' in body and 'delivery-feedback-form' not in body, 'Completed token still allows resubmission'
    print('Public feedback HTTP and nonce checks passed.')
finally:
    server.terminate()
    try: server.wait(timeout=5)
    except subprocess.TimeoutExpired: server.kill(); server.wait()
