#!/usr/bin/env python3
"""The one-click demo entry and its guard against robots. Uses the local demo database (never the live one):
   DB_NAME=digitalid_review_demo DEMO_MODE=true php scripts/demo-reset.php   then   python3 tests/demo_entry.py"""
import http.cookiejar, os, re, subprocess, sys, time, urllib.parse, urllib.request, urllib.error

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
PORT = 8993; BASE = f'http://localhost:{PORT}'
ENV = dict(os.environ, DB_NAME='digitalid_review_demo', DEMO_MODE='true', APP_URL=BASE)
import glob, tempfile
for f in glob.glob(os.path.join(subprocess.run(['php', '-r', 'echo sys_get_temp_dir();'], capture_output=True, text=True).stdout, 'rate_limit_*')): os.remove(f)   # the demo's limits are kept in files; start clean
srv = subprocess.Popen(['php', '-S', f'localhost:{PORT}', '-t', 'public', 'public/router.php'], cwd=ROOT, env=ENV, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
BROWSER = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15'
passed = failed = 0
def check(c, what):
    global passed, failed
    if c: passed += 1; print('ok   ' + what)
    else: failed += 1; print('FAIL ' + what)

class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *a, **k): return None
class Client:
    def __init__(self, ua=BROWSER):
        self.ua = ua; self.sid = None; self.op = urllib.request.build_opener(NoRedirect)   # the session cookie is marked Secure, which a plain-http cookie jar would not send back: keep it by hand
    def req(self, method, path, data=None):
        body = urllib.parse.urlencode(data).encode() if data is not None else None
        hd = {'User-Agent': self.ua, **({'Content-Type': 'application/x-www-form-urlencoded'} if body else {}), **({'Cookie': 'PHPSESSID=' + self.sid} if self.sid else {})}
        r = urllib.request.Request(BASE + path, data=body, method=method, headers=hd)
        try: x = self.op.open(r); code, text, hs = x.status, x.read().decode('utf-8', 'replace'), x.headers
        except urllib.error.HTTPError as e: code, text, hs = e.code, e.read().decode('utf-8', 'replace'), e.headers
        for c in hs.get_all('Set-Cookie') or []:
            m = re.match(r'PHPSESSID=([^;]+)', c)
            if m: self.sid = m.group(1)
        return code, text, hs
    def form(self, path='/login.php'):
        s, b, _ = self.req('GET', path)
        csrf = re.search(r'name="csrf_token" value="([^"]+)"|name="_csrf" value="([^"]+)"', b)
        return b, (csrf.group(1) or csrf.group(2)), re.search(r'name="ft" value="([^"]+)"', b).group(1)
    def go(self, ft_csrf, role, ft, website=''):
        return self.req('POST', '/demo-enter.php', {ft_csrf[0]: ft_csrf[1], 'ft': ft, 'role': role, 'website': website})

try:
    for _ in range(40):
        try: urllib.request.urlopen(BASE + '/login.php', timeout=2); break
        except Exception: time.sleep(0.25)
    probe = Client().req('GET', '/login.php')[1]
    name = re.search(r'name="([a-z_]*csrf[a-z_]*)" value="', probe).group(1)
    CS = lambda v: (name, v)
    c = Client(); page, csrf, ft = c.form()
    check('Explore as an administrator' in page and 'Explore as a member of staff' in page, 'the sign-in page offers one-click entry in demo mode')
    s, _, h = c.go(CS(csrf), 'admin', ft); s2, b2, _ = c.req('GET', '/login.php'); check(s == 302 and 'did not work' in b2, 'pressing at once (before two seconds) is refused')
    time.sleep(2.5); page, csrf, ft = c.form(); time.sleep(2.5)
    s, _, _ = c.go(CS(csrf), 'admin', ft, 'http://spam.example'); s2, b2, _ = c.req('GET', '/login.php'); check('did not work' in b2, 'a filled-in hidden field is refused')
    s, _, _ = c.go(CS(csrf), 'admin', 'forged.' + 'a' * 64); s2, b2, _ = c.req('GET', '/login.php'); check('did not work' in b2, 'a forged token is refused')
    bot = Client('python-urllib/3.9'); bp, bc, bf = bot.form(); time.sleep(2.5)
    s, _, _ = bot.go(CS(bc), 'admin', bf); s2, b2, _ = bot.req('GET', '/login.php'); check('did not work' in b2, 'a robot user agent is refused even with a good token')
    page, csrf, ft = c.form(); time.sleep(2.5)
    s, _, h = c.go(CS(csrf), 'admin', ft); check(s == 302 and 'id-card.php' in (h.get('Location') or ''), 'a person waiting a moment gets in as an administrator')
    s, home, _ = c.req('GET', '/id-card.php'); check('Three things to try' in home and 'Check-in sessions' in home and 'See it as a member of staff' in home, 'the administrator sees the guide and a way to switch view')
    csrfs = re.findall(r'name="' + name + r'" value="([^"]+)"', home); fts = re.findall(r'name="ft" value="([^"]+)"', home)
    s, _, _ = c.go(CS(csrfs[0]), 'staff', fts[0]); s, home, _ = c.req('GET', '/id-card.php')
    check('Look at your card' in home and 'See it as an administrator' in home, 'switching view takes a signed-in person to the staff view at once')
    c2 = Client(); p2, cs2, ft2 = c2.form(); time.sleep(2.5)
    c2.go(CS(cs2), 'staff', ft2); s, home, _ = c2.req('GET', '/id-card.php'); check('Look at your card' in home, 'a visitor can enter as a member of staff')
    c2.go(CS(cs2), 'root', ft2); s, home, _ = c2.req('GET', '/id-card.php'); check('Look at your card' in home, 'an unknown role cannot be chosen (the session is unchanged)')
    flood = Client(); fp, fc, ff = flood.form()
    for _ in range(25): flood.go(CS(fc), 'admin', ff)
    s, _, _ = flood.go(CS(fc), 'admin', ff); s2, b2, _ = flood.req('GET', '/login.php'); check('a lot of attempts' in b2, 'a flood of presses is slowed down')
finally:
    srv.terminate()
print(f'\n{passed} passed, {failed} failed'); sys.exit(1 if failed else 0)
