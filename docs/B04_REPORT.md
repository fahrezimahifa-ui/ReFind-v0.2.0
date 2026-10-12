# B04 — Student Discovery & Workflows

BATCH: B04 — Explore, Lost/Found reporting, media, matching, claim/resubmit, activity, notifications/history.

STATUS: **Verified — Student scope B04.** BM review/status/handover actions remain B05.

## BASELINE

B03 auth/Profile/Settings/shared shell was active. Student pages and mutations were pending, with protected read/scoping APIs. Main/test report datasets were empty at audit. XAMPP Apache and MariaDB were running. User approved B04 and existing stack/workflow contracts were followed.

## CHANGELOG

- `app/uploads.php`: independent multipart MIME/dimension/size/count validation, safe generated paths, retained-photo selection, rollback/after-commit/reference-aware cleanup.
- `app/repository.php`: UTC mapping, notifications/history/status transitions, numeric parent locking, rule-based scoring/persisted suggestions, safe public/read models and pagination.
- `app/reports.php`: actual create/update/delete, ownership/status revalidation, field/date/reference checks, separate public/private collections, DB-specific matching advisory lock, transactional file metadata/history/notifications and suggestion maintenance.
- `app/claims.php`: actual submit/resubmit, own Found denial/own Lost link, unique same-user/item record, eligible state/duplicate checks, retained/replaced proof, numeric locks, rollback, PENDING/VERIFICATION and BM notifications.
- `api/items.php`, `claims.php`: live writes, SQL-backed filters/page counts, private edit metadata only own eligible report, history/suggestions and own claim/read detail.
- `api/references.php`, `notifications.php`, `handovers.php`: legacy filter labels, campus date/time context, paginated own notifications + exact unread count, own read state, authorized pickup data with explicit UTC/NULL handling.
- Seven templates: `browse.html`, `item.html`, `report.html`, `claim.html`, `my-reports.html`, `notifications.html`, `history.html`. `home.html` now has actual recent discovery and Student report/search actions.
- `css/student.css`, `js/student-common.js`, `uploads.js`, `student.js`: photo-led cards, discoverable filters, explicit CTA, gallery/native dialogs/focus, public-vs-private forms, browser image preparation, state/error/retry/draft/busy controls, own activity, read-before-navigation and read-only history. All rendering escapes user data through DOM text/attributes.
- Routes and role navbar activated for Student/shared pages. Collapse breakpoint extended for the larger nav; no fixed body min-width or primary sidebar. Blob image sources allowed by scoped page CSP for upload preparation/preview.
- `tools/import-demo-photos.py`, `seed-student-demo.php`, `PHOTO_CREDITS.md`, protected demo photo inputs: credited synthetic classroom examples and non-destructive/idempotent bookmarks.
- `tools/test-student.cjs`, `check-workflow.php`, isolated CLI rollback failpoints, current regression/smoke helpers. Documentation/overview updated with actual screenshots separate from target wireframes.

## DB CHANGE

**No schema migration.** 14 tables / 2 views / 25 FK remain. Business records/photos/matches/claims/history/notifications now persist through actual APIs.

Main initially had zero reports. CLI seed added **3 labelled synthetic reports** (Lost headphones A, Found headphones B, Found umbrella B), **3 public photos + 1 private Lost evidence**, rule-based suggestions/history/notifications. No claims or BM decisions were fabricated in main. Source/crops/licenses are credited. Seed bookmarks preserve edited/deleted records, and initial seeding refuses an unknown nonempty dataset.

All mutation assertions used fresh QA accounts/records on `_test`, not the main demonstration dataset. Main smoke was read-only and its before/after DB counts/user fingerprint matched. Rollback/file cleanup tests used controlled CLI-only triggers scoped to the test database. Backup/non-destructive setup remains in database/README.md; preserve current storage and SQL together for future backups.

## TESTS EXECUTED

Working directory: `C:\xampp\htdocs\ReFind_0.2.0`.

| Command/check | Result |
|---|---|
| `python tools/import-demo-photos.py` | 14 credited stock inputs + PHOTO_CREDITS; no DB change |
| `python tools/prepare-test.py` | Owned app/config/storage/database isolation ready |
| `node tools/test-student.cjs --api-only` | Initial 16 API/DB/storage groups passed before UI checkpoint |
| `node tools/test-student.cjs` | **24 actual Student scenario groups PASS**, 0 uncaught browser errors |
| `node tools/test-auth.cjs` | **28 B02 auth/role/ownership regression groups PASS** |
| `node tools/test-account.cjs` | **24 B03 account/navbar regression groups PASS** |
| XAMPP PHP `tools/seed-student-demo.php` | 3 synthetic main reports, generated public/private media, history/notifs/matching |
| XAMPP PHP `tools/check-workflow.php` on main/test | **14 invariant checks PASS each**, no missing/orphan/invalid media paths |
| `node tools/smoke-auth.cjs --b04` | Three main accounts login/logout + live Student/shared read pages PASS; DB data unchanged |
| Live schema checker via smoke | 9 checks PASS each main/test, unchanged schema fingerprint |
| PHP lint / Node syntax / Python AST | Passed on current runtime/tool sources |
| `python tools/verify-blueprint.py` | Current documentation/diagram/link checks rerun |

### Verified behaviour

- SQL search, category/location/type/status/date filters, literal `%/_/'`, paging, no results and clear/reset states.
- Lost/Found create with public/private image limits, known midnight and unknown NULL time, rule-based suggestions persisted on both sides.
- Own eligible edit with retained/replaced images; combined limits/foreign image IDs rejected; eligible delete cascades metadata/files while notification refs become NULL.
- Server independently rejects malformed MIME, SVG, oversized prepared files, >1200px, excess count and Found ownership collection.
- Private clue/evidence/NIM/email excluded from public projections; actual evidence BM-only; direct storage inaccessible.
- Forced report failure after file save rolls back report/history/notifs/media metadata and cleans new files. Forced claim notification failure after proof save likewise rolls back claim/item/history/notifs/files.
- Own Found, foreign/closed Lost link, terminal/ready-pickup targets and active duplicate rejected. Concurrent same-user claim requests produce one record (201 + 409).
- NEED_MORE_INFO fixture → browser/API resubmit reuses claim ID, preserves answer/Lost link/selected existing proof, replaces removed proof only after commit and returns PENDING.
- Own claim/pickup/notification data remains isolated. Read state persists before claim navigation; claimed report editing locks; legacy floor/location filter/display survives.
- Actual browser forms, compression/preview/gallery, failed-save draft/retry, delete confirmation cancellation/commit, activity/inbox/history states exercised.

NEED_MORE_INFO/approved/terminal states were inserted only as isolated test fixtures to test Student exceptions. Actual BM request-more-info/approval/completion actions are **not implemented/tested yet**.

### Responsive / evidence

All **7 Student pages** exercised at **360×800, 390×844, 768×1024, 1280×800, 1440×900**; no global horizontal overflow; filters, forms, gallery/dialog and next actions usable. Browser uses America/Los_Angeles while UI formats Asia/Jakarta.

- [student-results.json](verification/b04/student-results.json)
- [main-smoke.json](verification/b04/main-smoke.json)
- `docs/verification/b04/`: actual current screenshot exports (main and isolated test).

A combined long QA command was interrupted after partial B02 regression, leaving its known 72-byte password probe and three identified fixtures in `_test`. The interrupted records/hash probe were inspected, recovered only with identity checks, and regression rerun as standalone commands. Final 28-group B02 suite and invariant checker pass; no application failure remains in executed suites.

## MANUAL TESTS

1. Login Student A → Explore → Found headphone demo → detail → claim + optional own Lost link → My Activity → notifications.
2. Login Student B: own Found claim CTA unavailable; submit a new Lost/Found and upload public/private images.
3. Edit/delete an eligible own report before a claim links it; cancel the confirmation first, then verify deletion.
4. Search/filter/reset at mobile and desktop; open public gallery with keyboard/Escape.
5. Supporting photos/private clue must not appear on another Student's public detail. BM evidence/review UI follows B05.

## GAPS

BM operational queue/evidence review/approve/reject/request-more-info/status/archive/handover schedule/cancel/complete remain B05. Staff notifications preserve exact claim deep links but honestly open the staff foundation page until that batch.

Student submit → matching → claim pending and fixture-backed resubmit **are verified end-to-end**. Full Lost → Found → Claim → actual BM Verification → Handover → Returned **belum diverifikasi secara end-to-end**. Final diagrams/exports will be reconciled as-built B06.

## NEXT

**B05 — BM workflow + transactional handover**, after next-batch approval.

## FILE PATH / URL

- Project: `C:\xampp\htdocs\ReFind_0.2.0`.
- Explore: `http://localhost/ReFind_0.2.0/browse.html`.
- Report: `http://localhost/ReFind_0.2.0/report.html`.
- My Activity: `http://localhost/ReFind_0.2.0/my-reports.html`.
- Notifications/history: `notifications.html`, `history.html`.
- Helpers: `app/uploads.php`, `repository.php`, `reports.php`, `claims.php`.
- Actual HTML: seven templates under protected `app/pages/`.
- UI: `css/student.css`, `js/student-common.js`, `uploads.js`, `student.js`.
- Report/evidence: `docs/B04_REPORT.md`, `docs/verification/b04/`.
