# B01 — Blueprint report

BATCH: B01 — Blueprint ReFind 0.2.0

STATUS: **Verified — artefak dokumentasi/desain saja.** Tanggal: 10 Oktober 2026.

## BASELINE

Source 0.1.0 diaudit read-only; PHP 8.2.12 dan MariaDB 10.4.32 tersedia. Pengguna menyetujui app/database baru, cancellation hanya jadwal pickup, dan kerja per batch. Status baseline terhadap R01–R12 ada di SOURCES_AND_DECISIONS.md.

## CHANGELOG

- Root: `README.md`, `.gitignore`, `.htaccess` (directory listing disabled).
- `docs/index.html`: overview artefak yang bisa dibuka melalui browser.
- `docs/B01_BLUEPRINT.md`, `ROLE_ROUTE_MATRIX.md`, `WORKFLOW_CONTRACT.md`, `ACCEPTANCE_TESTS.md`, `SOURCES_AND_DECISIONS.md`: scope/traceability, role/routes/API, auth/workflow, test plan, decisions.
- `docs/schema-contract.json`, `DATA_DICTIONARY.md`: proposal lengkap berbasis 14-table/25-FK SQL baseline, termasuk target delta handover.
- `docs/diagrams/sources/`: **9 .drawio editable + 3 .dot** untuk sitemap/ERD. FC01–FC06 mempunyai satu Start tiap flowchart; FC04/FC05 masing-masing dua sheets dengan pasangan connector A/B.
- `docs/diagrams/exports/`: **11 sheets SVG/PNG/PDF**; FC04/FC05 grouped PDFs dan `BLUEPRINT_DIAGRAMS.pdf` (11 pages).
- `docs/wireframes/index.html`, README, exports: **8 board PNG + WIREFRAMES.pdf (8 pages)**. Static illustrations dengan scrollable form/list dan visible report action.
- `tools/build-blueprint.py`, `blueprint-diagrams.py`, `render-blueprint.cjs`, `verify-blueprint.py`: documentation generators/renderers/semantic QA; `tools/.htaccess` menolak akses HTTP.
- `docs/verification/`: machine-readable QA results, rendering results, contact sheet.

## DB CHANGE

**None.** Generator/verifier dokumen tidak mengakses database. Metadata query read-only mengonfirmasi `refind_db_0_2_0` dan `_test` belum dibuat (count 0). ERD/schema-contract masih proposal, bukan schema live.

## TESTS EXECUTED

Working directory: `C:\xampp\htdocs\ReFind_0.2.0`.

| Perintah / pemeriksaan | Hasil nyata |
|---|---|
| `python "tools/build-blueprint.py"` | 9 editable diagrams / 11 sheets; SQL fingerprint cocok; 14 tables / 25 FKs |
| `node "tools/render-blueprint.cjs"` | 11 diagram sheets, 8 wireframe boards, exports PDF; no browser exceptions/external requests; all diagram text within canvas |
| `python "tools/verify-blueprint.py"` | **59 checks, 0 failures**; Start/decisions/connectors/reachability, XML styles, FK/cardinality, links, nonblank PNG/PDF, PDF page counts, wireframe geometry |
| `node --check "tools/render-blueprint.cjs"` + Python AST parse tiga `.py` | Syntax checks passed |
| Local HTTP GET `/ReFind_0.2.0/docs/` | **200**, Target / To-be banner found |
| Local HTTP GET `/ReFind_0.2.0/tools/build-blueprint.py` | **403** |
| MySQL SELECT information_schema untuk dua DB 0.2.0 | **0**, tidak ada database baru pada B01 |

Bukti output: [verification/b01-results.json](verification/b01-results.json), [verification/render-results.json](verification/render-results.json), [verification/contact-sheet.png](verification/contact-sheet.png).

Koreksi yang ditemukan sebelum final pass: metadata/style nodes .drawio diperbaiki; measurement scroll wireframes diperbaiki; branch scheduling failure ditambahkan; edge labels ditata agar tidak clipped; report action dibuat terlihat; cross-navigation sitemap dibuat dashed dengan tooltip/catatan untuk mengurangi label overlap.

## MANUAL TESTS / VISUAL REVIEW

Visual review dilakukan terhadap exports sitemap, ERD operational, FC01, FC04 review, FC05 pickup, FC06 account, dan wireframes Student/BM. Pemeriksaan export tambahan dilakukan melalui browser geometry dan PDF/text/PNG checks.

User review berikutnya:

1. Buka overview dan delapan boards: cek arah card discovery, top menu, report/claim, review BM dan handover.
2. Buka PDF diagram: cek actor handoff, request-more-info loop, cancellation PENDING, terminal Returned, ERD cardinality.
3. Bila mengedit manual, .drawio dapat diimport di diagrams.net; canonical definitions untuk regenerasi ada pada tools/schema-contract.

## GAPS

Database/login/API/application pages belum diimplementasikan. Semua acceptance tests aplikasi berstatus **NOT RUN**; **belum diverifikasi secara end-to-end**. Pemeriksaan wireframe bukan pengujian responsive/keyboard/upload pada aplikasi actual. Final sitemap/ERD/FC harus direkonsiliasi lagi dengan implementasi B02–B06.

## NEXT

**B02 — Database + auth**, setelah approval batch berikutnya: schema + reference seed baru, demo credential CLI, login/logout, CSRF/throttling/session expiry, protected page/API guard, dan real auth tests pada database `_test`.

## FILE PATH

- Folder: `C:\xampp\htdocs\ReFind_0.2.0`.
- Overview: `C:\xampp\htdocs\ReFind_0.2.0\docs\index.html` / `http://localhost/ReFind_0.2.0/docs/`.
- Wireframe: `docs/wireframes/index.html` dan `docs/wireframes/exports/WIREFRAMES.pdf`.
- Diagram PDF: `docs/diagrams/exports/BLUEPRINT_DIAGRAMS.pdf`.
- Editable diagrams: `docs/diagrams/sources/`.
