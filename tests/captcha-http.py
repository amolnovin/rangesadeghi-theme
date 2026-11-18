#!/usr/bin/env python3
"""Exercise AJAX forms against the real Drive plugin in a disposable WP install."""
import http.client, json, socket, subprocess, sys, time, urllib.parse
root, cli, fixture = sys.argv[1:]
data = json.loads(fixture)
server = subprocess.Popen(['php', '-S', '127.0.0.1:8766', '-t', root], stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
def request(action, params):
    payload = urllib.parse.urlencode({'action': action, 'nonce': data['nonce'], **params})
    conn = http.client.HTTPConnection('127.0.0.1', 8766, timeout=10)
    conn.request('POST', '/wp-admin/admin-ajax.php', payload, {'Host':'example.test','Content-Type':'application/x-www-form-urlencoded','User-Agent':'Mozilla/5.0'})
    res = conn.getresponse(); body = res.read().decode(); status = res.status; conn.close()
    assert status == 200, f'{action}: HTTP {status} {body[:300]}'
    return json.loads(body)
def captcha(challenge): return {'wpmc_token':challenge['token'], 'wpmc_answer':str(challenge['ans']), 'wpmc_website':''}
try:
    for _ in range(100):
        if server.poll() is not None: raise RuntimeError('PHP server exited')
        try:
            sock = socket.create_connection(('127.0.0.1', 8766), timeout=.1); sock.close(); break
        except OSError: time.sleep(.1)
    else: raise RuntimeError('PHP server did not start')
    contact = {'fullname':'Captcha Test', 'mobile':'09123456789', 'subject':'other', 'message':'Hello from integration test'}
    assert not request('rs_contact_submit', contact)['success'], 'Contact accepted missing captcha'
    wrong = captcha(data['contact']); wrong['wpmc_answer'] = str(data['contact']['ans'] + 100)
    assert not request('rs_contact_submit', {**contact, **wrong})['success'], 'Contact accepted wrong answer'
    assert request('rs_contact_submit', {**contact, **captcha(data['contact'])})['success'], 'Contact rejected valid answer'
    assert not request('rs_contact_submit', {**contact, **captcha(data['contact'])})['success'], 'Contact accepted reused challenge'
    newsletter = {'contact':'captcha@example.test'}
    assert not request('rs_newsletter_subscribe', newsletter)['success'], 'Newsletter accepted missing captcha'
    assert request('rs_newsletter_subscribe', {**newsletter, **captcha(data['newsletter'])})['success'], 'Newsletter rejected valid answer'
    login = {'identity':'admin', 'password':'test-password'}
    assert not request('rs_modal_auth', login)['success'], 'Modal login accepted missing captcha'
    assert request('rs_modal_auth', {**login, **captcha(data['login'])})['success'], 'Modal login rejected valid answer'
    result = subprocess.check_output(['php', cli, 'eval', "echo json_encode([count(get_option('rangesadeghi_contact_messages', [])), isset(get_option('rangesadeghi_newsletter_subscribers', [])['captcha@example.test'])]);", '--path='+root], text=True)
    counts = json.loads(result[result.find('['):]); assert counts[0] == 1 and counts[1], f'Form submissions not saved: {counts}'
    print('Real WP Math Captcha contact, newsletter, replay and login HTTP checks passed.')
finally:
    server.terminate()
    try: server.wait(timeout=5)
    except subprocess.TimeoutExpired: server.kill(); server.wait()
