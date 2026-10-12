"""Import credited classroom stock-photo inputs, never personal uploads or old DB records."""
from pathlib import Path
import os
import zipfile

ROOT=Path(__file__).resolve().parents[1]
SOURCE=Path(os.environ.get('REFIND_BASELINE_ZIP',r'C:\Users\LENOVO\Downloads\ReFind_Version 0.1.0.zip'))
NAMES=['headphones-overview.jpg','headphones-headband.jpg','headphones-earcup.jpg','headphones-cable.jpg','wallet-overview.jpg','wallet-stitching.jpg','satchel-overview.jpg','satchel-straps.jpg','satchel-buckle.jpg','satchel-flap.jpg','satchel-strap-clip.jpg','satchel-adjuster.jpg','calculator-overview.jpg','umbrella-overview.jpg']
with zipfile.ZipFile(SOURCE) as archive:
    target=ROOT/'tools'/'demo-photos';target.mkdir(exist_ok=True)
    for name in NAMES:
        content=archive.read('ReFind_Version 0.1.0/ReFind_New/tools/demo-photos/'+name)
        destination=target/name
        if destination.exists() and destination.read_bytes()!=content:raise RuntimeError('Existing media differs: '+name)
        destination.write_bytes(content)
    credits=archive.read('ReFind_Version 0.1.0/ReFind_New/PHOTO_CREDITS.md').decode('utf-8').replace('\r\n','\n')
    credits=credits.split('## Current demonstration mapping')[0]
    credits+='\n## ReFind 0.2.0\n\nThe prepared sources above are reused as synthetic classroom examples. Runtime copies have generated filenames in separate public/private collections. New demo IDs are recorded privately by tools/seed-student-demo.php; the old database/photo mapping is not imported.\n'
    credits+='\n## B06 expanded classroom dataset\n\nThe same credited subjects illustrate the40 synthetic Lost/Found reports and12 mock claims. Reuse across scenario codes C01–C19 is deliberate; these are classroom illustrations, not photographs of40 independently recovered campus objects. Public ITEM, private Lost OWNERSHIP and claim-proof copies have separate generated paths/authorization. `tools/seed-expanded-demo.php` simulates review/pickup outcomes through real business functions and stores bookmarks privately. Calculator derivatives retain the CC BY-SA4.0 attribution/license above.\n'
    destination=ROOT/'PHOTO_CREDITS.md'
    if destination.exists() and destination.read_text(encoding='utf-8')!=credits:raise RuntimeError('Existing credits differ; inspect before import.')
    destination.write_text(credits,encoding='utf-8')
print('Imported 14 licensed stock-photo inputs and attribution. No database changes.')
