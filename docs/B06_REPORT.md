# B06 — Final draw.io artefacts, mock data, fresh install & release

BATCH: B06 — as-built academic artefacts, expanded synthetic dataset, final regression/fresh-install acceptance and clean submission ZIP.

STATUS: **Verified — approved local XAMPP MVP and six-batch implementation plan.**

## BASELINE

B01–B05 delivered working auth/account/Student/BM workflows, real Lost → Returned integration and protected private evidence. Main had3 synthetic reports,3 accounts and no claims/handovers. Academic exports were Target / To-be; user/app flow sources, larger mock dataset and final release/fresh-install acceptance were pending.

The user approved B06 and explicitly requested draw.io for diagram artefacts and many mock records. Decisions D12/D13 are recorded in SOURCES_AND_DECISIONS.md. Work retains the procedural PHP/mysqli + local HTML/CSS/JS/jQuery/Bootstrap stack.

## CHANGELOG

- `tools/seed-expanded-demo.php`: adds37 reports around the original3 examples; simulates12 claims and6 handovers through actual report/claim/review/status/pickup functions. Includes private/public illustrations, real matching/history/notifications, variant times/locations and all report states. Private bookmarks and step receipts preserve edited/deleted examples and make reseeding idempotent.
- `tools/demo-summary.php`: read-only inventory, state distributions and public synthetic dataset evidence. `tools/backup-project.py`: private SQL + storage snapshot before main expansion. `tools/test-demo-seed.cjs`: isolated seed/idempotency/edit/delete/cleanup acceptance.
- `tools/blueprint-diagrams.py`, `build-blueprint.py`: reconcile actual SQL/routes/states with final draw.io definitions; add UF01 user flow and AF01 app/route flow. Sources/exports now labelled **As-built / B06**. Actual SQL is sufficient for regeneration; baseline ZIP is no longer required.
- `tools/render-blueprint.cjs`: regenerate13 diagram sheets and editable individual wireframe components in draw.io, plus PNG/PDF exports and combined PDFs. `tools/verify-blueprint.py`:81 semantic/source/export/link checks, with actual route and implementation-function reconciliation.
- `tools/build-release.py`: ZIP, per-file hashes/runtime fingerprint, CRC and secret/storage/config/helper exclusions. `.htaccess` also denies the local release directory over HTTP.
- `tools/verify-fresh-install.py`, `test-fresh-install.cjs`: actual ZIP extraction, new empty MariaDB datadir/server3307, documented setup/seeding, idempotency, schema/invariants and real browser lifecycle/role/privacy/viewport acceptance. Screen-only Student/BM demo recordings included.
- `tools/prepare-test.py`, `smoke-auth.cjs`: current isolated setup/seed helpers and main40-report read-only smoke, including hydrated image screenshots.
- README, browser overview, decision log, role/workflow/acceptance contracts, database/diagram guides, demo inventory/SOP and five-minute presentation script reflect final scope.

## DB CHANGE

**No schema migration.** Main remains14 tables,2 views,14 PK,13 UNIQUE,25 FK and9 CHECK. SQL fingerprints are unchanged. Main demo expansion is an approved additive data change; no old database records/passwords were imported.

Private backup before expansion: `storage/setup/backups/b06-before-20261012-095422/` with database.sql, storage.zip and checksum receipt. SQL and media/credentials remain together for local recovery; the backup is excluded from the submission ZIP.

### Main initial B06 dataset

| Records | Count / distribution |
|---|---|
| Accounts |3 active:2 Student,1 BM; existing credentials preserved |
| Reports |40:20 Lost,20 Found |
| Report states |OPEN4, UNDER_REVIEW2, POTENTIAL_MATCH14, VERIFICATION4, HANDOVER8, RESOLVED5, UNCLAIMED_CLOSED3 |
| Claims |12:Pending2, Need More Info2, Approved6, Rejected2 |
| Handovers |6:Pending2, Scheduled2, Completed2 |
| Public item photos |40 |
| Private Lost evidence |20 |
| Private claim proof |12 |
| Matches |84 suggested +6 confirmed |
| Notifications |176 |
| Status-history events |133 |

Illustrations are credited licensed stock reused across clearly labelled **synthetic classroom scenarios**. Approved/Returned outcomes are simulated through the actual business workflow, not asserted as real campus returns. Timestamps reflect actual execution/installation; NULL event times remain NULL. Fresh installs can have different event/history counts due to the original seed date and legitimate matching transitions; the40/12/6 dataset and invariants are stable.

## TESTS EXECUTED

Working directory: `C:\xampp\htdocs\ReFind_0.2.0`. Mutation suites use owned `_test` records; fresh-install mutations use a new server/datadir and extracted folder.

| Command/check | Executed result |
|---|---|
| `python tools/backup-project.py` | Private main SQL + storage snapshot before expansion |
| `python tools/prepare-test.py` | Owned test/config/session/database isolation ready |
| `node tools/test-demo-seed.cjs` | **7 groups PASS**; exact-ID cleanup restores pre-seed `_test` snapshot |
| PHP `tools/seed-expanded-demo.php` | Main37 additional reports,12 claims,6 pickups; all states/history/notifs through valid functions |
| `python tools/build-blueprint.py` |11 editable diagrams /13 sheets, actual14-table/25-FK contract |
| `node tools/render-blueprint.cjs` |13 diagram renders +8 wireframe boards, PDFs and editable draw.io components |
| `python tools/verify-blueprint.py` | **81 checks PASS**,0 failures |
| `node tools/test-auth.cjs` | **28 groups PASS** |
| `node tools/test-account.cjs` | **24 groups PASS** |
| `node tools/test-student.cjs` | **24 groups PASS** |
| `node tools/test-workflow.cjs` | **27 groups PASS**, including stale/race/rollback |
| `node tools/smoke-auth.cjs --b06` | Main3 accounts, expanded dataset/shared pages/BM tabs PASS; business dataset unchanged |
| PHP schema checker main/test/fresh | **9 checks PASS each** |
| PHP workflow checker main/test/fresh | **17 invariants PASS each**; no missing/orphan/invalid media |
| `python tools/build-release.py` | Clean archive/file manifest, CRC/config/storage/password/helper exclusions PASS |
| `python tools/verify-fresh-install.py` |8 fresh-install checks + **13 real browser groups PASS**; new empty DB, repeat setup/seed, full Returned, privacy/roles, five viewports; main unchanged |
| PHP lint / Node syntax / Python AST | **33 PHP /17 JS-CJS /9 Python sources PASS** |

Initial recording-enabled QA lacked Playwright FFmpeg. The QA dependency was installed in the approved temp directory; the complete13-group fresh suite then passed and exported actual screen recordings. This was a tooling dependency issue, not an application failure. The temporary3307 server is stopped after each run; existing3306 services/data remain available.

All required360×800,390×844,768×1024,1280×800,1440×900 viewports are covered by executed suites. Final Student discovery and all BM tabs are additionally exercised with the larger seeded dataset, with no global horizontal overflow or uncaught browser errors. Screenshots show actual main/fresh UI; conceptual wireframes remain identified as design references.

## MANUAL TESTS

Use [DEMO_ACCOUNTS.md](DEMO_ACCOUNTS.md) for all case IDs and actual UI labels. Login credentials are local in `storage/setup/demo-accounts.json`.

1. Student A → original Found headphones item2 → new claim + original own Lost item1 → BM request-more-info → same-ID Student resubmit → approve.
2. Or start from seeded C11 Pending / C07 Need More Info and follow the existing claim. Pending/Scheduled pickup examples C01–C04 support schedule/reschedule/cancel/complete demonstration.
3. Verify Cancel appointment retains ownership/Ready for pickup and the same handover; then Confirm item returned synchronizes both reports and gives read-only outcomes.
4. Explore filters/pagination, own Activity/notifications, private evidence BM-only, archived/recovered examples, Profile/name/password persistence and role-aware top navigation.
5. Follow [INSTALLATION_AND_RELEASE.md](INSTALLATION_AND_RELEASE.md) on another XAMPP installation; generated passwords are unique to that installation.

## GAPS

No blocking failure remains in executed approved MVP/release checks. Final artefacts, expanded synthetic dataset, repeatable setup, submission ZIP, actual screen-only Student/BM demo clips and a five-minute presentation script are supplied. The clips are workflow recordings; [DEMO_SCRIPT_5_MIN.md](DEMO_SCRIPT_5_MIN.md) provides the presentation narration/sequence.

## NEXT

**All six approved batches are complete.** User acceptance review/demonstration uses the delivered application, SOP and release; subsequent changes follow user feedback.

## FILE PATH / URL

- Main: `C:\xampp\htdocs\ReFind_0.2.0`, `http://localhost/ReFind_0.2.0/login.html`.
- Overview: `http://localhost/ReFind_0.2.0/docs/`.
- Release: `C:\xampp\htdocs\ReFind_0.2.0\releases\ReFind_0.2.0_B06.zip`; adjacent `.manifest.json` contains final SHA-256/file hashes.
- Final diagrams: `docs/diagrams/sources/*.drawio`, `docs/diagrams/exports/AS_BUILT_DIAGRAMS.pdf` (13 sheets), individual PDF/PNG/SVG exports.
- User/app flow: `UF01-user-flow.drawio`, `AF01-app-flow.drawio`; exports UF01/AF01.
- Editable wireframes: `docs/wireframes/sources/WIREFRAMES.drawio` (8 boards), individual WF01–WF08 sources and PDF/PNG exports.
- Evidence: `docs/verification/b06/`: seed-results, dataset, artifact-results, main-smoke, fresh-install-results, fresh-browser-results and actual screenshots.
- Clips: `docs/verification/b06/DEMO_STUDENT_WORKFLOW.webm`, `DEMO_BM_WORKFLOW.webm`.
- Report: `C:\xampp\htdocs\ReFind_0.2.0\docs\B06_REPORT.md`.
