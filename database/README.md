# Database 0.2.0 — schema B02 / final checkpoint B06

Schema baru memakai 14 tabel, 2 views, 25 FK, 14 PK, 13 UNIQUE dan 9 CHECK, sesuai data dictionary. `handovers.handover_status` adalah PENDING/SCHEDULED/COMPLETED; cancel appointment pada B05 kembali PENDING.

B03–B06 tidak mengubah schema. Approval/linked pair/handover/return/history/notifications menggunakan transaksi pada tabel existing. Cancel schedule mempertahankan ownership; private before/after pickup audit disimpan dalam authorized notifications, bukan public history. Detail final: [B06_REPORT.md](../docs/B06_REPORT.md).

## Setup

Dari `C:\xampp\htdocs\ReFind_0.2.0`:

```powershell
& "C:\xampp\php\php.exe" "tools/setup-db.php"
& "C:\xampp\php\php.exe" "tools/setup-demo.php"
& "C:\xampp\php\php.exe" "tools/seed-student-demo.php"
& "C:\xampp\php\php.exe" "tools/seed-expanded-demo.php"
& "C:\xampp\php\php.exe" "tools/check-database.php"
```

- `schema.sql`: CREATE TABLE IF NOT EXISTS dengan dependency order; tanpa DROP/TRUNCATE.
- `views.sql`: SQL SECURITY INVOKER; tanpa schema-bound DEFINER dari baseline.
- `seed-reference.sql`: hanya taxonomy/status/location. Tidak ada user/password/report/claim data dari 0.1.0.
- `manifest.json`: fingerprint SQL dan licensed assets.

Tool hanya menerima `refind_db_0_2_0` / `refind_db_0_2_0_test`. Existing nonempty DB tanpa receipt proyek ditolak. Re-run mempertahankan current records dan password, lalu memeriksa names, constraint counts, reference counts dan enum. Receipt disimpan di private storage.

## Demo credentials

CLI membuat Student A, Student B dan BM dengan password random dan `password_hash()`. Credential demonstrasi lokal disimpan di `storage/setup/demo-accounts.json`, **bukan dalam SQL/code/README publik**. HTTP ke storage/tools ditolak; runtime storage di-Git-ignore dan harus dikeluarkan dari ZIP submission.

Jika credentials sudah ada, re-run tidak mengubah password. Password pribadi tidak digunakan. Email/NIM demo synthetic.

## Test isolation

`python tools/prepare-test.py` membuat app `ReFind_0.2.0_Test` dengan config, secret, session directory dan database terpisah. Fixture CLI menolak DB main. Auth/account/Student/BM mutation suites memakai fresh QA records hanya pada `_test`; fixtures dibersihkan berdasarkan ID. Main smoke hanya login/logout/read dan memeriksa business dataset unchanged.

```powershell
& "C:\xampp\php\php.exe" "tools/check-database.php"
& "C:\xampp\php\php.exe" "tools/check-workflow.php"
```

Checkpoint B06:9 live schema checks dan17 workflow/media invariants PASS pada main/test/fresh. Main40 synthetic reports/12 claims/6 handovers are simulated via actual workflow functions; photo/claim/history/notification records are consistent. No real campus return is asserted. Reseed respects edited/deleted bookmarked examples and preserves credentials. Simpan SQL dan media storage bersama ketika membuat backup.

Before approved expansion a private SQL/storage snapshot was created under `storage/setup/backups/b06-before-20261012-095422/`. Fresh acceptance uses a completely new3307 datadir/server with the allowlisted `_test` DB name; original3306 schemas are retained. [Fresh install instructions](../docs/INSTALLATION_AND_RELEASE.md) and final evidence explain the runnable ZIP.

## Rollback / backup

B02 adalah instalasi baru, bukan destructive migration. Sebelum perubahan schema/data lanjutan, export SQL dan backup private storage melalui prosedur lokal, simpan di lokasi privat. Untuk rollback kode gunakan snapshot file batch sebelumnya dan konfigurasinya; jangan mengimpor dump berisi DROP atau mereset database existing. Jika setup berhenti, receipt `initializing` memungkinkan retry non-destructive dengan SQL yang sama; data yang tidak cocok harus diaudit terlebih dahulu.
