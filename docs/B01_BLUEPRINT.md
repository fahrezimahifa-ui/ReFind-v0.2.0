# B01 — Blueprint ReFind 0.2.0

**Status: blueprint awal B01 dengan final as-built checkpoint B06.**

Checkpoint final setelah B06: seluruh planned routes dan MVP auth/account/Student/BM workflows diimplementasikan/direverifikasi, termasuk fresh-install actual ZIP. Final draw.io sitemap/ERD/FC01–FC06/UF01/AF01 direkonsiliasi dengan SQL/routes/functions; data dictionary membaca SQL actual tanpa migration B03–B06. Dokumen ini menyimpan traceability/architecture blueprint; evidence current: [B06_REPORT.md](B06_REPORT.md).

## 1. Tujuan dan batas produk

Lost & Found kampus UMN: Lost → rule-based suggestion → Found → Claim → BM verification → Handover → Returned, dengan private evidence, notifikasi in-app, persistence, role separation, dan audit trail.

Primary target **mobile-first web app**. Desktop Student/BM tetap usable. Stack: HTML/CSS/JS + jQuery 3.7.1 + Bootstrap 5.3.8 lokal; PHP prosedural + mysqli + MariaDB. Matching tidak menentukan kepemilikan.

Pengguna mengubah scope menjadi app baru/database baru. Source v0.1.0 adalah referensi transaksi, validations, ownership, matching dan storage; folder baru menampung implementasi v0.2.0 dan artefak akademiknya.

## 2. Information architecture

### Student top navigation

`Home | Explore | Report | My Activity | Notifications` + account menu `Profile | Settings | Logout`.

- Home: search, recent discovery, dan next action kontekstual, bukan KPI dashboard.
- Explore: foto prominent, title, type, category, location, event date, status, dan View details.
- Report: pilih Lost/Found; satu form dengan bagian yang bergantung pada jenis laporan.
- My Activity: Reports / Claims / Closed. Review note dan next action ditampilkan pada claim card.
- Campus History tetap dapat dibuka dari Explore/My Activity.

### BM top navigation

`Review Queue | Handover | History | Explore | Notifications` + account menu yang sama.

- `admin.html?tab=reports`: review laporan dan eligible status actions.
- `admin.html?tab=claims`: queue dan detail ownership; deep link `&claim=ID` menargetkan record itu, termasuk outcome read-only.
- `admin.html?tab=handover`: pickup pending/scheduled dan completion.
- Queue adalah konten halaman; pada desktop dapat berdampingan dengan detail. Bukan sidebar navigasi.

### Page mapping target — 12 HTML routes

| Route | Peran | Komposisi |
|---|---|---|
| index.html | Guest / Student; BM diarahkan ke queue | Landing atau Student Home |
| login.html | Guest | Credential form, validation, generic error |
| browse.html | Student / BM | Search, filter disclosure, discovery cards |
| item.html?id=ID | Student / BM | Public photos/details, status, suggestions, eligible actions |
| report.html?type=lost/found atau ?edit=ID | Student | Sections: item, location/date, public media, private clue/evidence |
| claim.html?id=ID | Student | Ownership answer, optional own Lost link, private supporting photos |
| my-reports.html | Student | My Reports / Claims / Closed |
| admin.html?tab=reports/claims/handover | BM | Operational queue, review details, pickup steps |
| history.html | Student / BM | Read-only campus archive; mobile card/list |
| notifications.html | Student / BM | Own notifications and persisted read state |
| profile.html | Student / BM | Own name, email, role; Student identifier private |
| settings.html | Student / BM | Change password, logout |

Seluruh12 route aktif pada final checkpoint B06. Tabel izin/API current yang mengikat berada di ROLE_ROUTE_MATRIX.md. Final sitemap/app flow mengikuti mapping actual ini.

## 3. Mobile UI principles

- Navbar atas, satu navigasi utama. Mobile menu berupa collapse di bawah header.
- Satu kolom pada smartphone; 2–3 kolom pada layar lebih besar.
- Input/body text nyaman dibaca; target sentuh utama minimum 44px.
- Filter advanced dibuka dari tombol Filter; search, type tabs, count dan applied chips tetap discoverable.
- Item photo public tidak dicampur dengan private ownership evidence.
- Claim, approve, reject, complete pickup, dan delete memakai aksi eksplisit; keputusan penting memiliki confirmation.
- Label UI POTENTIAL_MATCH = **Possible match**, berbeda dengan claim APPROVED.
- Error tampil dekat input; loading/busy/retry/empty/success/disabled state kontekstual. Focus tidak hilang saat rerender.
- Semua board wireframe adalah dokumen statis. Mereka tidak merupakan tombol/form aplikasi yang sudah berfungsi.

## 4. Arsitektur as-built

```text
HTML pages + shared CSS/JS
        │ fetch / same-origin session cookie
        ▼
api/ endpoint adapters ───── page.php protected route gateway
        │                         │
        └────────── app/ ──────────┘
              auth / DB / repository / workflow / upload
                        │
                   mysqli / InnoDB
                        │
                   MariaDB 0.2.0
```

- `app/` menampung kode internal, bukan endpoint HTTP.
- `page.php` memakai allowlist dan role guard sebelum membaca HTML protected; bukan arbitrary file reader.
- `api/session.php` menghasilkan guest/auth state dan CSRF bootstrap; tanpa demo account switching.
- Fetch mempertahankan status HTTP agar 401/403/409/422/429 memiliki feedback berbeda.
- Session server-side menyimpan authenticated user ID. Role/is_active/credential fingerprint diperiksa dengan DB pada request protected.
- Data store adalah MariaDB. Snapshot JS hanya cache halaman yang dapat dibuang; UI tidak menjadi security authority.
- Folder app/database/storage/tools serta local secrets diblokir direct HTTP.
- Public ITEM photos dapat dibaca lewat photo endpoint. OWNERSHIP/claim photos hanya BM authorized pada MVP.

## 5. Database as-built

14 tabel dan2 views telah dibuat/diverifikasi. Referensi lengkap: `schema-contract.json` dan DATA_DICTIONARY.md. Final ERD berasal actual `database/schema.sql`, dengan25 FK,13 UNIQUE dan9 CHECK.

Perbedaan enum terhadap baseline: actual handover memakai **PENDING / SCHEDULED / COMPLETED**. CANCELLED baseline tidak dipakai untuk batal jadwal: SCHEDULED → PENDING sesuai D05. Klaim tetap approved dan barang tetap HANDOVER. Schema baru dibuat B02 dan direverifikasi B06; database lama terpisah.

`users` menyimpan profil MVP. Seed reference dan seed demo dipisahkan. Demo password ditetapkan melalui CLI dan hashing. Data/photo relation harus diverifikasi terhadap seed baru.

Audit event handover: append history generik untuk item terkait; notifikasi milik claimant/BM menyimpan perubahan jadwal, reason dan before/after yang private. Latest handover state berada di row handovers; history tidak mempublikasikan lokasi/jadwal pickup privat.

## 6. Traceability R01–R12

| ID | Deliverable target | Acceptance utama |
|---|---|---|
| R01 | Mobile-first wireframes, responsive CSS, README | UI01–UI05 |
| R02 | Discovery cards dan item-first hierarchy | ST03, UI01, UI06 |
| R03 | Shared top navbar dan role-aware account menu | AU07, UI02 |
| R04 | ERD operational/reference + dictionary + SQL reconciliation | DC01–DC03 |
| R05 | Sitemap page → feature; root Home/Landing Page | DC01 |
| R06 | FC01–FC06, one Start, labelled branches, valid merges/connectors | DC02 |
| R07 | Dua lane Student / Building Management, handoffs | BM01–BM05, DC02 |
| R08 | Login/logout/session/CSRF/rate-limit | AU01–AU08 |
| R09 | Profile edit dan change password persisten | AC01–AC04 |
| R10 | Server-side page/API/ownership guards | AU05–AU08, SE01–SE04 |
| R11 | Loading/error/empty/success, responsive forms/queue | ST01–ST09, UI01–UI06 |
| R12 | Workflow, concurrency, audit, demo dan documentation | BM01–BM08, DB01–DB04, RL01 |

## 7. Batch execution plan

| Batch | Hasil | Gate / dependency |
|---|---|---|
| B01 | Dokumen target, wireframes, sitemap/ERD/flowcharts editable + export | Konsistensi artefak diverifikasi; user review arah desain |
| B02 | Schema/seed baru, CLI demo accounts, login/logout, CSRF, throttling, protected page/API guards | Auth/ownership tests pada DB _test; tidak memakai dropdown privilege |
| B03 | Responsive shell, Profile/Settings persisten | AU + AC + navbar/keyboard/mobile checks |
| B04 | Student report/discovery/claim/activity/notifications/history | ST + upload/privacy + persistence |
| B05 | BM review, request-more-info, handover, status/archive | BM + transaction/concurrency + return synchronization |
| B06 | Final ERD/sitemap/FC, regression, fresh install, SOP dan ZIP | Artefak sesuai real app; evidence output tes; clean install |

Setiap batch melaporkan BASELINE, CHANGELOG, DB CHANGE, TESTS EXECUTED, MANUAL TESTS, GAPS, dan NEXT. Status Implemented berbeda dari Verified.

## 8. Risiko dan mitigasi

- SQL export baseline mengandung DROP TABLE. B02 membuat schema khusus database baru dengan checks terhadap nama/schema existing; seed tidak menimpa dataset secara diam-diam.
- Auth dapat memengaruhi semua caller. Kontrak session/CSRF/status HTTP diselesaikan B02 sebelum mutasi Student/BM.
- Approval, report pasangan, match, handover, history dan notifications harus atomic dan locked dengan urutan ID konsisten.
- Private clue/evidence/review note/pickup detail tidak masuk public cards/API/history.
- Layout refactor tidak boleh mengganti workflow dengan gesture ambigu. Screenshot desain bukan bukti interaction test.
- Sumber diagram target harus direkonsiliasi dengan schema/API final. B01 tidak menyatakan route/auth/database sudah berfungsi.

## 9. Approval berikutnya

Seluruh gate B01–B06 telah disetujui/dijalankan. Scope tambahan draw.io dan expanded synthetic dataset tercatat sebagai D12/D13. Final acceptance/demo mengikuti B06 report/SOP; perubahan berikutnya mengikuti user feedback. Mutation QA memakai `_test`; fresh release memakai new isolated3307 datadir dengan nama DB `_test` yang sama.
