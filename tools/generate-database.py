"""Generate new schema/reference seeds and import licensed assets from a fixed ZIP snapshot."""
import hashlib
import json
import os
from pathlib import Path
import re
import zipfile

ROOT = Path(__file__).resolve().parents[1]
SOURCE = Path(os.environ.get('REFIND_BASELINE_ZIP', r'C:\Users\LENOVO\Downloads\ReFind_Version 0.1.0.zip'))
EXPECTED = '8b2fe2c560b524c4e8710280c1a7222551f0e52ee047f0201e7993b09f0ae289'
ORDER = ['users', 'buildings', 'categories', 'item_statuses', 'campus_locations', 'location_floors', 'items', 'item_photos', 'item_matches', 'claims', 'claim_photos', 'handovers', 'notifications', 'item_status_history']
REFERENCE = ['buildings', 'categories', 'item_statuses', 'campus_locations', 'location_floors']
ASSETS = ['vendor/jquery.min.js', 'vendor/bootstrap.min.css', 'vendor/bootstrap.bundle.min.js', 'vendor/LICENSES.txt', 'vendor/README.md', 'assets/refind-logo-horizontal.png', 'assets/refind-logo-mark.png']


def generate():
    with zipfile.ZipFile(SOURCE) as archive:
        raw = archive.read('ReFind_Version 0.1.0/Database (mysql xampp)/refind_db_0_1_0.sql')
        assert hashlib.sha256(raw).hexdigest() == EXPECTED, 'Unexpected source SQL fingerprint'
        sql = raw.decode('utf-8').replace('\r\n', '\n')
        definitions = dict(re.findall(r'CREATE TABLE `([^`]+)` \((.*?)\n\) ENGINE', sql, re.S))
        out = ['-- ReFind 0.2.0: new database schema, B02.', '-- No DROP/TRUNCATE; run with tools/setup-db.php on the approved new databases.', 'SET NAMES utf8mb4;', "SET time_zone = '+00:00';"]
        for name in ORDER:
            block = definitions[name]
            if name == 'handovers':
                block = block.replace("enum('PENDING','SCHEDULED','COMPLETED','CANCELLED')", "enum('PENDING','SCHEDULED','COMPLETED')")
            out.append(f'CREATE TABLE IF NOT EXISTS `{name}` ({block}\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;')
        # No schema-bound DEFINER: the authenticated DB connection is the invoker.
        views = []
        for name in ['v_items_overview', 'v_pending_claims']:
            query = re.search(r'/\*!50001 VIEW `' + name + r'` AS (.*?) \*/;', sql, re.S).group(1)
            views.append(f'CREATE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW `{name}` AS {query};')
        seed = ['-- Reference data only: no users, report/claim records, passwords or uploaded media.', 'SET NAMES utf8mb4;']
        for name in REFERENCE:
            statement = re.search(r'^INSERT INTO `' + name + r'` VALUES .*?;$', sql, re.M).group(0)
            if name == 'item_statuses':
                statement = statement.replace("'Match Found'", "'Possible match'")
            seed.append(statement)
        directory = ROOT / 'database'
        directory.mkdir(exist_ok=True)
        for file, content in [('schema.sql', '\n\n'.join(out) + '\n'), ('views.sql', '\n\n'.join(views) + '\n'), ('seed-reference.sql', '\n\n'.join(seed) + '\n')]:
            destination = directory / file
            if destination.exists() and destination.read_text(encoding='utf-8') != content:
                raise RuntimeError(f'Existing generated SQL differs; inspect before regenerating {file}')
            destination.write_text(content, encoding='utf-8')
        for relative in ASSETS:
            content = archive.read('ReFind_Version 0.1.0/ReFind_New/' + relative)
            destination = ROOT / relative
            destination.parent.mkdir(parents=True, exist_ok=True)
            if destination.exists() and destination.read_bytes() != content:
                raise RuntimeError(f'Existing asset differs: {relative}')
            destination.write_bytes(content)
        receipt = {'source_sql_sha256': EXPECTED, 'tables': ORDER, 'views': ['v_items_overview', 'v_pending_claims'], 'reference_counts': {'buildings': 4, 'categories': 8, 'item_statuses': 7, 'campus_locations': 40, 'location_floors': 91},
                   'files': {f: hashlib.sha256((directory / f).read_bytes()).hexdigest() for f in ['schema.sql', 'views.sql', 'seed-reference.sql']},
                   'asset_hashes': {f: hashlib.sha256((ROOT / f).read_bytes()).hexdigest() for f in ASSETS}}
        (directory / 'manifest.json').write_text(json.dumps(receipt, indent=2) + '\n', encoding='utf-8')
    print('Generated 14-table schema + 2 views + 5 reference seeds; imported 7 licensed local assets. No DB connection.')


if __name__ == '__main__':
    generate()
