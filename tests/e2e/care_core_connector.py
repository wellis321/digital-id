import os, sys, json, urllib.parse, subprocess, time, urllib.request, urllib.error, tempfile, base64
# End to end: Care Core (single source of truth) -> Digital ID. Joiner, mover, leaver, photo, webhook, reconcile.
#   CC_PW=<mysql root password> [CARE_CORE_DIR=../care-core] python3 tests/e2e/care_core_connector.py
# Needs MySQL on 127.0.0.1:8889, empty-able databases care_core_review_demo and digitalid_review_demo (Digital ID schema loaded). Ports 8990 (Care Core) and 8097 (Digital ID).
PW=os.environ['CC_PW']; DI=os.path.abspath(os.path.join(os.path.dirname(__file__),'..','..')); CC=os.path.abspath(os.environ.get('CARE_CORE_DIR') or os.path.join(DI,'..','care-core'))
KEY=base64.b64encode(b'k'*32).decode()
CCENV={**os.environ,'DB_HOST':'127.0.0.1','DB_PORT':'8889','DB_USER':'root','DB_PASS':PW,'DB_NAME':'care_core_review_demo','APP_KEY':KEY,'DEMO_MODE':'false','APP_URL':'http://localhost:8990'}
DIENV={**os.environ,'DB_HOST':'127.0.0.1:8889','DB_NAME':'digitalid_review_demo','DB_USER':'root','DB_PASS':PW,'DEMO_MODE':'production','APP_URL':'http://localhost:8097','APP_ENV':'development','ALLOW_PRIVATE_CARE_CORE':'1'}
ok=0; bad=[]; procs=[]
def check(n,c,d=''):
    global ok
    if c: ok+=1; print('  ok  ',n)
    else: bad.append(n); print('  FAIL',n,str(d)[:300])
def spawn(cmd,env,cwd): p=subprocess.Popen(cmd,cwd=cwd,env=env,stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL); procs.append(p)
def up(port):
    for _ in range(60):
        try: urllib.request.urlopen(f'http://localhost:{port}/',timeout=1); return True
        except urllib.error.HTTPError: return True
        except Exception: time.sleep(0.2)
    return False
def pdo(db,q,args=()):
    code="$p=new PDO('mysql:host=127.0.0.1;port=8889;dbname='.$argv[1],'root',getenv('CC_PW'));$s=$p->prepare($argv[2]);$s->execute(json_decode($argv[3],true));echo json_encode($s->columnCount()?$s->fetchAll(PDO::FETCH_NUM):[]);"
    return json.loads(subprocess.run(['php','-r',code,'--',db,q,json.dumps(list(args))],capture_output=True,text=True,env={**os.environ,'CC_PW':PW}).stdout or '[]')
def api(method,path,body=None,raw=False):
    r=urllib.request.Request('http://localhost:8990/api/v1'+path,method=method,data=json.dumps(body).encode() if body is not None else None,headers={'Authorization':'Bearer '+TOKEN,'Content-Type':'application/json'})
    try:
        x=urllib.request.urlopen(r,timeout=30); b=x.read(); return x.status,(b if raw else json.loads(b or b'null'))
    except urllib.error.HTTPError as e: return e.code,e.read().decode()[:300]
def di(code):
    r=subprocess.run(['php','-r','require "'+DI+'/config/config.php"; '+code],cwd=DI,env=DIENV,capture_output=True,text=True); return (r.stdout+r.stderr).strip()
try:
    print('-- starting Care Core and Digital ID')
    subprocess.run(['php','scripts/demo-reset.php'],cwd=CC,env={**CCENV,'DEMO_MODE':'true'},capture_output=True)
    org=pdo('care_core_review_demo','SELECT id FROM organisations LIMIT 1')[0][0]
    out=subprocess.run(['php','-r','require "app/bootstrap.php"; $k=App\\Api\\ApiKeys::create((int)$argv[1],"Digital ID",["people.read","staff.read","staff.write","events.read","webhooks.manage"],null,null,600,null,App\\Core\\Actor::system("e2e")); echo $k["token"];','--',str(org)],cwd=CC,env=CCENV,capture_output=True,text=True)
    TOKEN=out.stdout.strip()
    if len(TOKEN)<20: print('could not make an API key:',out.stdout[-200:],out.stderr[-300:]); sys.exit(2)
    spawn(['php','-S','localhost:8990','-t','public','public/index.php'],CCENV,CC)
    check('Care Core is up', up(8990))
    # Digital ID: an organisation, then connect
    pdo('digitalid_review_demo',"DELETE FROM organisations WHERE domain='cc-e2e.test'")
    pdo('digitalid_review_demo',"INSERT INTO organisations (name, domain) VALUES ('Glenross e2e','cc-e2e.test')")
    org_di=int(pdo('digitalid_review_demo',"SELECT id FROM organisations WHERE domain='cc-e2e.test'")[0][0])
    spawn(['php','-S','localhost:8097','-t','public'],DIENV,DI)
    check('Digital ID is up', up(8097))
    print(di(f'CareCore::ensureSchema(); echo "schema ok";'))
    s,r=api('POST','/employments',{'first_name':'Nina','last_name':'Barr','email':'nina.barr@cc-e2e.test','job_title':'Care assistant','start_date':'2026-01-05'})
    check('a joiner is created in Care Core', s==201, (s,r))
    emp=r['data']; person=emp['person_id']; empid=emp['id']
    set_=f'CareCore::set({org_di},"care_core_url","http://localhost:8990"); CareCore::set({org_di},"care_core_api_key",{json.dumps(TOKEN)});'
    res=di(set_+f' echo json_encode(CareCoreSync::syncOrganisation({org_di}));')
    print('   ',res[:400])
    j=json.loads(res[res.index('{'):]) if res.startswith('{') else {'errors':[res]}
    check('first sync creates them, no errors', j.get('errors')==[] and j.get('created',0)>=1, res[-300:])
    row=pdo('digitalid_review_demo',"SELECT e.is_active,u.is_active,e.care_core_person_id,e.display_reference FROM employees e JOIN users u ON u.id=e.user_id WHERE u.email='nina.barr@cc-e2e.test'")
    check('Nina is an active employee linked to her Care Core id', row and int(row[0][0])==1 and row[0][2]==person, row)
    res2=di(f'echo json_encode(CareCoreSync::syncOrganisation({org_di}));'); j2=json.loads(res2[res2.index('{'):])
    check('a second sync changes nothing (idempotent)', j2['created']==0 and j2['errors']==[], res2[-200:])
    n=pdo('digitalid_review_demo',"SELECT COUNT(*) FROM employees e JOIN users u ON u.id=e.user_id WHERE u.email='nina.barr@cc-e2e.test'")[0][0]
    check('still exactly one card holder', int(n)==1, n)
    # leaver
    s,r=api('POST',f'/employments/{empid}/end',{'last_day':'2026-01-06'})
    check('Care Core records the leaver', s==200, (s,r))
    res3=di(f'echo json_encode(CareCoreSync::syncOrganisation({org_di}));'); j3=json.loads(res3[res3.index('{'):])
    row=pdo('digitalid_review_demo',"SELECT e.is_active,u.is_active FROM employees e JOIN users u ON u.id=e.user_id WHERE u.email='nina.barr@cc-e2e.test'")
    check('the nightly check switches the leaver off (employee and login)', j3['deactivated']>=1 and [int(x) for x in row[0]]==[0,0], (res3[-200:],row))
    # webhook
    hook=di(f'$t=bin2hex(random_bytes(20)); echo $t;')
    print('-- webhook receiver')
    token=hook[-40:]
    di(f'CareCore::set({org_di},"care_core_webhook_token","{token}"); CareCore::set({org_di},"care_core_webhook_secret","whsec_test");')
    import hmac, hashlib
    def post(body,sig_secret='whsec_test',t=token):
        data=json.dumps(body).encode(); sig='sha256='+hmac.new(sig_secret.encode(),data,hashlib.sha256).hexdigest()
        r=urllib.request.Request(f'http://localhost:8097/api/care-core-webhook.php?t={t}',data=data,headers={'X-CareCore-Signature':sig,'Content-Type':'application/json'})
        try: x=urllib.request.urlopen(r,timeout=30); return x.status,x.read().decode()
        except urllib.error.HTTPError as e: return e.code,e.read().decode()
    s,r=api('POST','/employments',{'first_name':'Omar','last_name':'Ali','email':'omar.ali@cc-e2e.test','job_title':'Senior carer','start_date':'2026-01-05'}); e2=r['data']
    ev={'id':str(__import__('uuid').uuid4()),'type':'employment.started','subject':e2['person_id'],'data':{'employment':e2['id']}}
    check('a bad signature is refused', post(ev,'wrong')[0]==401)
    check('an unknown token is refused', post(ev,t='0'*40)[0]==404)
    s,b=post(ev); check('a signed event is accepted and applied', s==200 and '"outcome":"ok"' in b, (s,b))
    row=pdo('digitalid_review_demo',"SELECT e.is_active FROM employees e JOIN users u ON u.id=e.user_id WHERE u.email='omar.ali@cc-e2e.test'")
    check('Omar exists straight away, without waiting for the nightly sync', row and int(row[0][0])==1, row)
    s,b=post(ev); check('the same event again is a harmless duplicate', s==200 and 'duplicate' in b, (s,b))
    s,r=api('POST',f'/employments/{e2["id"]}/end',{'last_day':'2026-01-06'})
    s,b=post({'id':str(__import__('uuid').uuid4()),'type':'employment.ended','subject':e2['person_id'],'data':{'employment':e2['id']}}); check('an ended event is accepted', s==200, (s,b))
    row=pdo('digitalid_review_demo',"SELECT e.is_active,u.is_active FROM employees e JOIN users u ON u.id=e.user_id WHERE u.email='omar.ali@cc-e2e.test'")
    check('and the leaver is switched off at once', row and [int(x) for x in row[0]]==[0,0], row)

    print('-- magic-link sign-in and the managed-organisation guard')
    import re, http.cookiejar
    s_,r=api('POST','/employments',{'first_name':'Priya','last_name':'Nair','email':'priya.nair@cc-e2e.test','job_title':'Nurse','start_date':'2026-01-05'}); e3=r['data']
    di(f'echo json_encode(CareCoreSync::syncOrganisation({org_di}));')
    di(f'putenv("MAIL_TRANSPORT=log");')
    open(DI+'/storage/mail.log','w').close() if os.path.isdir(DI+'/storage') else None
    cj=http.cookiejar.CookieJar()
    class NR(urllib.request.HTTPRedirectHandler):
        def redirect_request(self,*a,**k): return None
    op=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj),NR)
    def fetch(url,data=None):
        try:
            x=op.open(urllib.request.Request(url,data=urllib.parse.urlencode(data).encode() if data else None),timeout=30); return x.status,x.headers,x.read().decode('utf8','replace')
        except urllib.error.HTTPError as e: return e.code,e.headers,e.read().decode('utf8','replace')
    def csrf(h): m=re.search(r'name="csrf_token"\s+value="([^"]+)"',h) or re.search(r'value="([^"]+)"\s+name="csrf_token"',h); return m.group(1) if m else ''
    st,h,b=fetch('http://localhost:8097/login-link.php'); check('the sign-in-by-email page loads', st==200 and 'Email me a link' in b, st)
    st,h,b=fetch('http://localhost:8097/login-link.php',{'csrf_token':csrf(b),'email':'nobody@cc-e2e.test'}); check('an unknown address gets the same quiet answer', 'on its way' in b, b[:200])
    check('no link was created for the unknown address', int(pdo('digitalid_review_demo','SELECT COUNT(*) FROM login_links')[0][0])==0)
    st,h,b=fetch('http://localhost:8097/login-link.php'); st,h,b=fetch('http://localhost:8097/login-link.php',{'csrf_token':csrf(b),'email':'priya.nair@cc-e2e.test'}); check('a current member of staff is told the same thing', 'on its way' in b, b[:200])
    n=int(pdo('digitalid_review_demo','SELECT COUNT(*) FROM login_links')[0][0]); check('and a link was created for them', n==1, n)
    # we cannot read the emailed token (only its hash is stored), so make a known one the same way MagicLink does
    tok=di(f'$t=bin2hex(random_bytes(32)); getDbConnection()->prepare("INSERT INTO login_links (user_id, token_hash, expires_at) SELECT id, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE) FROM users WHERE email=?")->execute([hash("sha256",$t),"priya.nair@cc-e2e.test"]); echo $t;')[-64:]
    st,h,b=fetch('http://localhost:8097/login-link.php?t='+tok); check('opening the link shows a button and does not sign in', st==200 and 'name="t"' in b and int(pdo('digitalid_review_demo','SELECT COUNT(*) FROM login_links WHERE used_at IS NOT NULL')[0][0])==0, st)
    st,h,b2=fetch('http://localhost:8097/login-link.php',{'csrf_token':csrf(b),'t':tok}); check('pressing it signs them in', st==302 and 'id-card' in (h.get('Location') or ''), (st,h.get('Location')))
    st,h,b3=fetch('http://localhost:8097/id-card.php'); check('and the card page opens', st==200, st)
    st,h,b=fetch('http://localhost:8097/login-link.php?t='+tok); check('the same link cannot be used twice', 'expired or has already been used' in b, b[:200])
    # a leaver cannot use a link that was already emailed
    tok2=di(f'$t=bin2hex(random_bytes(32)); getDbConnection()->prepare("INSERT INTO login_links (user_id, token_hash, expires_at) SELECT id, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE) FROM users WHERE email=?")->execute([hash("sha256",$t),"omar.ali@cc-e2e.test"]); echo $t;')[-64:]
    cj.clear(); st,h,b=fetch('http://localhost:8097/login-link.php?t='+tok2); st,h,b2=fetch('http://localhost:8097/login-link.php',{'csrf_token':csrf(b),'t':tok2})
    check('a leaver cannot sign in even with a link they were already sent', st!=302, (st,h.get('Location')))
    # managed organisation guard
    r=di(f'echo json_encode(Employee::create(1,{org_di},"X1")); echo json_encode(UserImport::importFromCsv({org_di},"/nonexistent"));')
    check('staff cannot be added by hand while Care Core manages the organisation', 'come from Care Core' in r, r[:200])
finally:
    for p in procs: p.terminate()
    pdo('digitalid_review_demo',"DELETE FROM organisations WHERE domain='cc-e2e.test'")
print(f'\n{ok} passed, {len(bad)} failed'); sys.exit(1 if bad else 0)
