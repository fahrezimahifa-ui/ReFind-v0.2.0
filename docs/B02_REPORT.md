# B02 — Database + Auth

BATCH: B02 — Database + credential authentication.

STATUS: **Verified — scope B02 database/auth/permission foundation.**

## BASELINE

B01 blueprint/diagrams tersedia; database dan application routes belum ada. Approval pengguna: app/database baru, PHP + MySQL diperbolehkan, per batch. PHP 8.2.12, Apache, MariaDB 10.4.32 tersedia.

## CHANGELOG

- `database/schema.sql`, `views.sql`, `seed-reference.sql`, `manifest.json`, README: dependency-ordered/non-destructive schema dan reference seed. Handover enum tiga states sesuai D05.
- `app/config.php`, `db.php`, `http.php`, `auth.php`, `routes.php`, `policies.php`: configuration, prepared statements, safe errors/headers, credential authentication, ID/CSRF rotation, expiry, hashed credential fingerprint, locked rate limiter, local return allowlist, role/ownership helpers.
- `api/`: session/auth/account read/reference/items read/claims read/staff read/handovers read/photos/own-notification read-state. Future business writes explicitly 501 after applicable guards.
- `page.php`, `.htaccess`: authoritative route from REQUEST_URI, allowlisted templates, authorization before protected HTML. Case/query bypasses tested. Private app/database/storage/tools/dotfiles/bootstrap denied.
- `app/pages/login.html`, `home.html`, `bm.html`, `message.html`; `css/auth.css`, `js/api.js`, `auth-ui.js`: real login/logout and auth landing states, mobile-first basic UI, error/busy/retry/focus behavior, role-specific landing.
- `vendor/`, `assets/`: seven local library/logo/license files imported from the checksum-verified source ZIP; no framework migration.
- `tools/generate-database.py`, `setup-db.php`, `setup-demo.php`, `check-database.php`, `prepare-test.py`, `b02-fixture.php`, `test-auth.cjs`, `smoke-auth.cjs`: setup, isolation, real integration/browser tests, non-mutating main smoke.
- README/decision log/role matrix/acceptance status/data dictionary/schema-contract/docs overview diperbarui. Diagram regeneration sekarang mengikuti SQL B02 actual untuk fields/relations; future workflow tetap Target / To-be.

## DB CHANGE

Dua database baru **`refind_db_0_2_0`** dan **`refind_db_0_2_0_test`**: masing-masing 14 tabel, 2 views, 14 PK, 13 UNIQUE, 25 FK dan 9 CHECK. Reference data: 4 buildings, 8 categories, 7 item statuses, 40 campus locations, 91 floor records. Tiga akun demo per DB, password random dan hashing. Operational records main masih kosong.

Setup menggunakan receipt/fingerprint, menolak unknown nonempty DB, dan re-run tidak menimpa current records/password. SQL tanpa DROP/TRUNCATE. Secret, sessions dan credential receipts terpisah antara main/test. Backup/rollback prosedur ada di database/README.md; tidak ada destructive migration.

## TESTS EXECUTED

Working directory: `C:\xampp\htdocs\ReFind_0.2.0`.

| Perintah / pemeriksaan | Hasil nyata |
|---|---|
| `python tools/generate-database.py` | 14-table schema, 2 views, 5 reference seeds; 7 licensed local assets |
| XAMPP PHP `tools/setup-db.php` / `tools/setup-demo.php` | Main DB/schema/accounts initialized; idempotence verified on isolated copy |
| `python tools/prepare-test.py` | Separate `_Test` app/config/secret/session storage/database; no main runtime secret copy |
| `node tools/test-auth.cjs` | **28 scenario groups PASS**, 0 uncaught JS exceptions |
| XAMPP PHP `tools/check-database.php` pada main/test | **9 checks PASS masing-masing**: columns/types/nullability, FK columns, uniques/counts, hash accounts, schema fingerprint |
| `node tools/smoke-auth.cjs` | **3 main accounts login/logout PASS**, DB counts/users fingerprint unchanged |
| PHP `-l`, Node `--check`, Python AST parse | Syntax checks passed; 22 PHP runtime/tool files, app/test JavaScript and Python tools |
| Regenerate/render + `python tools/verify-blueprint.py` | **59 documentation checks PASS**, updated schema/ERD dictionary and 11-sheet/8-board exports |

### Verified auth/security paths

- Distinct credential login Student A/B/BM; session ID + CSRF rotation; refresh persists.
- Wrong/unknown/inactive account uniform generic error; existing inactive session revoked.
- Current DB role is authority; client role/account/user_id and old switch endpoint cannot grant privilege.
- Page/API unauthorized 401/403; uppercase and route query bypass attempts rejected.
- Missing/stale CSRF and foreign Origin rejected; no automatic POST replay.
- Logout invalidates old cookie/token; idle and absolute expiry enforced server-side.
- Password hash change invalidates existing sessions; test via fixture, not a claim that B03 password UI exists.
- Five-failure pair limit independent sessions; parallel failures serialized; aggregate IP limit across emails; Retry-After.
- 72-byte bcrypt boundary prevents accepted suffix truncation.
- Cross-user edit/delete/Lost-link/claims/handovers/notification/profile access rejected without unauthorized changes.
- Public projection excludes clue/evidence/email/NIM; private media BM-only; collection mismatch cannot expose evidence through ITEM endpoint.
- Internal/config/storage/tools/dotfiles/CLI fixture inaccessible over HTTP.
- DB names displayed with DOM escaping; no injected elements.

### Browser/layout scope

Real keyboard login/logout and overflow checks at **360×800, 390×844, 768×1024, 1280×800, 1440×900**. These checks cover auth/landing UI, not every future workflow page.

Bukti: [auth-results.json](verification/b02/auth-results.json), [main-smoke.json](verification/b02/main-smoke.json), screenshots di `docs/verification/b02/`. Fixtures cleaned by exact IDs; demo account state restored; throttle reset in test app.

Koreksi sebelum final pass: normalize CRLF SQL extraction, binary-vs-collation table ordering, stronger media collection guard, and documented pending workflow scope. Tidak ada failed auth scenario tersisa dalam suite yang dijalankan.

## MANUAL TESTS

1. Buka login aplikasi; gunakan email/password dari [DEMO_ACCOUNTS.md](DEMO_ACCOUNTS.md).
2. Login Student A/B → Home dengan nama akun sendiri → refresh → Logout.
3. Login BM → staff foundation page → Student-only URL ditolak → Logout.
4. Student membuka admin.html → 403; authorized future workflow → 501 dengan batch yang jelas.
5. Review auth appearance/error states pada smartphone. Profile/password settings mulai B03.

## GAPS

Profile/settings writes dan UI B03; Student business mutations/UI B04; BM decisions/handover B05. No registration/reset-email/SSO integration. Upload/write workflow belum diuji. Authentication **sudah diverifikasi end-to-end**; full Lost → Returned workflow **belum diverifikasi secara end-to-end**. Read/scoping foundation tidak berarti seluruh business feature selesai.

## NEXT

**B03 — Responsive shell + Profile/Account Settings**, setelah approval: persistent name edit, current/new/confirmation password change, full role-aware top navbar/account menu, loading/error/focus states dan corresponding tests.

## FILE PATH / URL

- Project: `C:\xampp\htdocs\ReFind_0.2.0`.
- Login: `http://localhost/ReFind_0.2.0/login.html`.
- Templates: `app/pages/`; guarded virtual `.html` URLs bukan public template files.
- Password receipt: `C:\xampp\htdocs\ReFind_0.2.0\storage\setup\demo-accounts.json` (local/private).
- Schema: `database/schema.sql`, `views.sql`, `seed-reference.sql`.
- Tests: `tools/test-auth.cjs`, `docs/verification/b02/`.
- Isolated copy: `C:\xampp\htdocs\ReFind_0.2.0_Test`.
