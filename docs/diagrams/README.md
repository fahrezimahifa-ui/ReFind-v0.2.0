# Diagram sources and exports — As-built B06 / draw.io

Semua final diagrams memakai **draw.io / diagrams.net**, berlabel **As-built / B06**. Dictionary/ERD membaca actual SQL14 tabel/25 FK; sitemap/app flow reconcile12 actual routes; flow models mencantumkan implementation files/functions.81 final checks lulus. [Laporan B06](../B06_REPORT.md) menyimpan runtime/fresh-install evidence.

## Paket

- `sources/*.drawio`: editable diagrams.net XML, uncompressed, nodes/edges individual.
- `sources/*.dot`: editable Graphviz source untuk sitemap/ERD.
- `exports/*.svg`: vector rendering.
- `exports/*.png`: readable raster rendering.
- `exports/*.pdf`: vector/print export; FC04.pdf menggabungkan sheet A dan B.
- `exports/BLUEPRINT_DIAGRAMS.pdf`: seluruh sheets dalam satu paket.
- `exports/AS_BUILT_DIAGRAMS.pdf`: final combined13-sheet package; BLUEPRINT filename remains a compatibility alias.

Diagram: SITEMAP, ERD01-operational, ERD02-reference, FC01-login-session-logout, FC02-report, FC03-browse-claim, FC04-review-handover (dua sheets), FC05-bm-operations (dua sheets), FC06-profile-settings, **UF01-user-flow**, **AF01-app-flow**. Total11 sources/13 sheets. Editable conceptual wireframes: `../wireframes/sources/WIREFRAMES.drawio` plus8 individual boards.

### Notasi

- Sitemap: `[Page]` berwarna navy-soft; `(Feature)` berwarna cyan-soft. Root **Home / Landing Page**, bukan nama brand. Cross-navigation memakai garis putus-putus.
- ERD: tiap entity menunjukkan fields dan PK/FK/UNIQUE/nullable. `1:M` berarti parent dapat mempunyai `0..M` children; parent per child `1` atau `0..1` sesuai FK nullability. `claims → handovers = 1:0..1` berdasarkan UNIQUE claim_id. Table stubs bertanda REF menunjuk ERD sheet lain, bukan entity baru.
- Flowchart: ellipse Start/End, rectangle process, diamond decision, parallelogram input/output. Tepat dua actor lanes **Student** / **Building Management**.
- Shared blocks melintasi batas dua lane, bukan lane ketiga. `(System)` menandai otomatisasi, bukan pekerjaan BM.
- FC04 connector A keluar dari sheet 1 dan masuk sheet 2. Hanya sheet 1 mempunyai Start. Awaiting pickup adalah akhir skenario, bukan status terminal database.

## Regenerasi

Canonical definitions: `tools/blueprint-diagrams.py` dan `docs/schema-contract.json`. Editable .drawio adalah hasil generator yang dapat dibuka/import di diagrams.net; regenerasi mengikuti definitions tersebut.

```powershell
python "tools/build-blueprint.py"
node "tools/render-blueprint.cjs"
python "tools/verify-blueprint.py"
```

Default Graphviz: `C:\Program Files\Graphviz\bin\dot.exe`. Default Chrome: `C:\Program Files\Google\Chrome\Application\chrome.exe`. Default Playwright Core: `C:\Users\LENOVO\AppData\Local\Temp\opencode\refind-multipage-check\node_modules\playwright-core`. Override lewat `REFIND_DOT`, `REFIND_CHROME`, `REFIND_PLAYWRIGHT_MODULE` di mesin lain. Python memerlukan Pillow dan PyMuPDF. Lokasi ZIP sumber dapat diubah dengan `REFIND_BASELINE_ZIP`; fingerprint SQL baseline harus cocok.

Renderer/verifier run document QA, separate from runtime suites. Actual SQL is enough for B06 generation; original ZIP is only the fallback for legacy pre-schema generation. Draw.io source shapes/text/connectors are editable individually. Regeneration follows canonical definitions; keep edits there when regenerating.
