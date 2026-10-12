"""Extract the actual ZIP and verify setup/browser workflows on a new isolated MariaDB server."""
import hashlib
import json
import os
from pathlib import Path
import shutil
import socket
import subprocess
import tempfile
import time
import zipfile
import argparse

ROOT=Path(__file__).resolve().parents[1]
PHP=r'C:\xampp\php\php.exe'
BIN=Path(r'C:\xampp\mysql\bin')
TEMP=Path(r'C:\Users\LENOVO\AppData\Local\Temp\opencode')
PORT=3307
parser=argparse.ArgumentParser()
parser.add_argument('--batch',choices=['B06','UX01'],default='B06')
batch=parser.parse_args().batch
ARCHIVE=ROOT/'releases'/('ReFind_0.2.0_'+batch+'.zip')
OUTPUT=ROOT/'docs'/'verification'/batch.lower()
if not ROOT.parent.is_dir() or not TEMP.is_dir() or not OUTPUT.is_dir() or not ARCHIVE.is_file():
    raise RuntimeError('Expected XAMPP/archive/evidence/temp parents unavailable')
with socket.socket() as probe:
    if probe.connect_ex(('127.0.0.1',PORT))==0:
        raise RuntimeError('Fresh QA port3307 is already in use; no existing server was stopped')
run=batch.lower()+'-'+str(int(time.time()*1000))
private=Path(tempfile.mkdtemp(prefix='refind-fresh-'+run+'-',dir=TEMP))
target=ROOT.with_name('ReFind_0.2.0_Fresh_'+run.replace('-','_'))
if target.exists():raise RuntimeError('Fresh target unexpectedly exists')
with zipfile.ZipFile(ARCHIVE) as bundle:
    for name in bundle.namelist():
        parts=Path(name).parts
        if not parts or parts[0]!='ReFind_0.2.0' or '..' in parts or ':' in name or '\\' in name:
            raise RuntimeError('Unsafe archive path')
    bundle.extractall(private/'extracted')
shutil.copytree(private/'extracted'/'ReFind_0.2.0',target)
manifest=json.loads((target/'RELEASE_MANIFEST.json').read_text(encoding='utf-8'))
for name,digest in manifest['files'].items():
    if hashlib.sha256((target/name).read_bytes()).hexdigest()!=digest:raise RuntimeError('Extracted file hash differs: '+name)
base='/'+target.name+'/'
(target/'.refind-b06-fresh.json').write_text(json.dumps({'source':str(ROOT),'run':run,'port':PORT,'base':base})+'\n')
(target/'app'/'config.local.php').write_text("<?php\nreturn ['db_name'=>'refind_db_0_2_0_test','db_port'=>3307,'base_path'=>'"+base+"','session_name'=>'refind_020_fresh'];\n",encoding='utf-8')
data=private/'mariadb'
subprocess.run([str(BIN/'mysql_install_db.exe'),'--datadir='+str(data),'--port='+str(PORT)],check=True,capture_output=True)
log=open(private/'server.log','wb')
server=subprocess.Popen([str(BIN/'mysqld.exe'),'--no-defaults','--basedir='+str(BIN.parent),'--datadir='+str(data),'--port='+str(PORT),
                         '--bind-address=127.0.0.1','--skip-log-bin','--innodb-buffer-pool-size=64M','--console'],stdout=log,stderr=subprocess.STDOUT)
result={'scope':batch+' actual submission ZIP fresh setup, isolated new MariaDB datadir/server3307 + browser lifecycle',
        'run':run,'archive_sha256':hashlib.sha256(ARCHIVE.read_bytes()).hexdigest(),'runtime_sha256':manifest['runtime_sha256'],
        'fresh_folder':str(target),'isolated_datadir':str(data),'database_port':PORT,'checks':{},'passed':False}

def php(tool):
    return json.loads(subprocess.check_output([PHP,str(target/'tools'/tool)],cwd=target))

def snapshot(folder):
    code="require 'app/db.php'; $all=[]; foreach(['users','items','claims','handovers','notifications','item_photos','claim_photos','item_matches','item_status_history'] as $t) $all[$t]=rows('SELECT * FROM '.$t); echo hash('sha256',json_encode($all));"
    return subprocess.check_output([PHP,'-r',code],cwd=folder).decode().strip()

main_before=snapshot(ROOT)
try:
    ready=False
    for _ in range(100):
        if server.poll() is not None:raise RuntimeError('Fresh MariaDB exited; inspect '+str(private/'server.log'))
        ping=subprocess.run([str(BIN/'mysqladmin.exe'),'--host=127.0.0.1','--port='+str(PORT),'--user=root','ping'],capture_output=True)
        if ping.returncode==0:ready=True;break
        time.sleep(.2)
    if not ready:raise RuntimeError('Fresh MariaDB readiness timed out')
    absent=subprocess.check_output([str(BIN/'mysql.exe'),'--host=127.0.0.1','--port='+str(PORT),'--user=root','--batch','--skip-column-names','-e',"SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='refind_db_0_2_0_test'"]).decode().strip()
    assert absent=='0';result['checks']['new_empty_database']=True
    setup=php('setup-db.php');assert setup['tables']==14 and setup['views']==2
    accounts=php('setup-demo.php');assert accounts['created']==3
    php('seed-student-demo.php');seed=php('seed-expanded-demo.php')
    assert seed['summary']['counts']['items']==40 and seed['summary']['counts']['claims']==12 and seed['summary']['counts']['handovers']==6
    result['initial_dataset']=seed['summary']['counts'];result['checks']['documented_setup_and_full_mock_dataset']=True
    before=snapshot(target)
    php('setup-db.php');assert php('setup-demo.php')['created']==0
    php('seed-student-demo.php');assert php('seed-expanded-demo.php')['reports_created']==0
    assert snapshot(target)==before;result['checks']['setup_and_seed_idempotent']=True
    result['schema']=php('check-database.php');assert result['schema']['passed'];result['checks']['schema_nine_checks']=True
    subprocess.run(['node',str(ROOT/'tools'/'test-fresh-install.cjs'),str(target),batch.lower()],cwd=ROOT,check=True,timeout=240)
    result['browser']=json.loads((OUTPUT/'fresh-browser-results.json').read_text());assert result['browser']['complete']
    result['checks']['actual_browser_role_privacy_responsive_and_return']=True
    result['invariants']=php('check-workflow.php');assert result['invariants']['passed'];result['checks']['seventeen_workflow_media_invariants']=True
    assert snapshot(ROOT)==main_before;result['checks']['main_database_unchanged']=True
    result['checks']['archive_file_hashes']=True;result['passed']=True
finally:
    if server.poll() is None:
        subprocess.run([str(BIN/'mysqladmin.exe'),'--host=127.0.0.1','--port='+str(PORT),'--user=root','shutdown'],capture_output=True)
        try:server.wait(timeout=15)
        except subprocess.TimeoutExpired:server.terminate();server.wait(timeout=10)
    log.close();result['fresh_server_stopped']=server.poll() is not None
    (OUTPUT/'fresh-install-results.json').write_text(json.dumps(result,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
    if result['passed']:
        (ROOT/'releases'/('ReFind_0.2.0_'+batch+'.verification.json')).write_text(json.dumps({'scope':'External acceptance receipt for exact tested archive','archive':ARCHIVE.name,'archive_sha256':result['archive_sha256'],'runtime_sha256':result['runtime_sha256'],'run':run,'checks':result['checks'],'browser_groups':result['browser']['passed'],'fresh_server_stopped':result['fresh_server_stopped'],'passed':True},indent=2)+'\n',encoding='utf-8')
print(json.dumps({'passed':result['passed'],'checks':result['checks'],'browser_groups':result['browser']['passed'],'fresh_server_stopped':result['fresh_server_stopped'],'evidence':'docs/verification/'+batch.lower()+'/fresh-install-results.json'},indent=2))
