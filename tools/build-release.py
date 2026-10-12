"""Build a clean, inspectable submission archive; never package runtime credentials."""
import hashlib
import json
from pathlib import Path
import zipfile
import argparse

ROOT=Path(__file__).resolve().parents[1]
parser=argparse.ArgumentParser()
parser.add_argument('--batch',choices=['B06','UX01'],default='B06')
batch=parser.parse_args().batch
OUTPUT=ROOT/'releases'
if not OUTPUT.is_dir():
    raise RuntimeError('Expected owned releases directory')
FOLDERS={'app','api','assets','css','js','vendor','database','docs'}
ROOT_FILES={'.htaccess','page.php','README.md','PHOTO_CREDITS.md'}
TOOLS={'.htaccess','setup-db.php','setup-demo.php','seed-student-demo.php','seed-expanded-demo.php','demo-summary.php',
       'check-database.php','check-workflow.php','build-blueprint.py','blueprint-diagrams.py','render-blueprint.cjs','verify-blueprint.py','build-release.py'}
STORAGE={'.htaccess','README.md'}
files=[]
for file in ROOT.rglob('*'):
    if not file.is_file():continue
    relative=file.relative_to(ROOT)
    if relative.as_posix()=='releases/README.md':files.append(file);continue
    if any(part in {'node_modules','__pycache__','.git','releases'} for part in relative.parts):continue
    if file.name in {'config.local.php','.env'} or file.suffix in {'.log','.pyc'}:continue
    if len(relative.parts)==1 and file.name in ROOT_FILES:files.append(file)
    elif relative.parts[0] in FOLDERS:files.append(file)
    elif relative.parts[0]=='tools' and (len(relative.parts)==2 and file.name in TOOLS or len(relative.parts)==3 and relative.parts[1]=='demo-photos'):files.append(file)
    elif relative.parts[0]=='storage' and len(relative.parts)==2 and file.name in STORAGE:files.append(file)
files.sort()
hashes={file.relative_to(ROOT).as_posix():hashlib.sha256(file.read_bytes()).hexdigest() for file in files}
runtime={name:value for name,value in hashes.items() if not name.startswith('docs/') and name not in {'README.md','PHOTO_CREDITS.md'}}
runtime_digest=hashlib.sha256(json.dumps(runtime,sort_keys=True).encode()).hexdigest()
manifest={'version':'0.2.0','batch':batch,'files':hashes,'runtime_sha256':runtime_digest,'credentials':'Generated locally by tools/setup-demo.php; never copied from main',
          'mock_data':'Generate 40 reports / 12 claims / 6 handovers with both demo seed tools'}
archive=OUTPUT/('ReFind_0.2.0_'+batch+'.zip')
with zipfile.ZipFile(archive,'w',zipfile.ZIP_DEFLATED,compresslevel=6) as bundle:
    for file in files:bundle.write(file,'ReFind_0.2.0/'+file.relative_to(ROOT).as_posix())
    bundle.writestr('ReFind_0.2.0/RELEASE_MANIFEST.json',json.dumps(manifest,ensure_ascii=False,indent=2)+'\n')
with zipfile.ZipFile(archive) as bundle:
    assert bundle.testzip() is None
    names=bundle.namelist()
    assert not any('/storage/' in name and not name.endswith(('/storage/.htaccess','/storage/README.md')) for name in names)
    assert not any(name.endswith(('config.local.php','.env','b02-fixture.php','recover-interrupted-auth.cjs')) for name in names)
    credentials=ROOT/'storage'/'setup'/'demo-accounts.json'
    passwords=[a['password'].encode() for a in json.loads(credentials.read_text())['accounts'].values()] if credentials.is_file() else []
    for name in names:
        if Path(name).suffix in {'.php','.js','.cjs','.py','.md','.html','.json','.sql'}:
            content=bundle.read(name)
            assert not any(password in content for password in passwords),'Main password found in release: '+name
receipt=manifest|{'archive':archive.name,'archive_sha256':hashlib.sha256(archive.read_bytes()).hexdigest(),'bytes':archive.stat().st_size,
                  'checks':{'zip_crc':True,'no_runtime_storage':True,'no_local_config':True,'no_main_passwords':True,'no_mutation_helpers':True},'passed':True}
(OUTPUT/('ReFind_0.2.0_'+batch+'.manifest.json')).write_text(json.dumps(receipt,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
print(json.dumps({'archive':str(archive),'files':len(hashes)+1,'bytes':receipt['bytes'],'sha256':receipt['archive_sha256'],'runtime_sha256':runtime_digest,'checks':receipt['checks']},indent=2))
