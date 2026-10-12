# B05 — BM Review & Transactional Handover

BATCH: B05 — BM queue/evidence, claim decisions, operational status/archive, pickup scheduling/audit and synchronized return.

STATUS: **Verified — BM scope and full Lost → Returned lifecycle on isolated `_test`.** Final as-built documentation/release verification follows B06.

## BASELINE

B04 Student discovery, Lost/Found report/media, matching, claim/resubmit, activity and notifications/history were active alongside B02 auth and B03 Profile/Settings. BM had server guards/read APIs but operational controls and mutations were pending. Main held three synthetic photo-backed reports, no claims/handovers. User approved B05 and confirmed cancellation means cancelling the pickup appointment while retaining approved ownership.

Stack remains HTML/CSS/JS, local jQuery 3.7.1/Bootstrap 5.3.8, procedural PHP/mysqli and MariaDB/XAMPP. Original v0.1.0 folder/database are separate from the new application.

## CHANGELOG

- `app/workflow.php`: claim/report/handover revision fingerprints, explicit confirmation, shared database-specific advisory lock, ascending-ID parent locks, fresh relation/state checks, atomic approval/rejection/request-more-info/status/pickup operations and conflict 409 mapping.
- Approval confirms an optional Lost/Found pair, sets related reports HANDOVER, creates exactly one pickup, rejects competing active claims, settles obsolete suggestions and commits history/notifications together. Rejection distinguishes other active claims from last active claim. Request-more-info/resubmit preserves the claim ID.
- Eligible manual report actions include Under review/Never retrieved and Lost recovery. Active claim/confirmed pair/handover/terminal conflicts are blocked; Found Returned must use approved physical handover.
- `app/staff-read.php`: authorized queue/detail projections, finder clue, private claim/Lost evidence, eligible actions, revision and exact reviewed outcomes.
- `api/staff.php`: real paginated Claims/Reports queue/detail and BM-only review/status POST. `api/handovers.php`: scoped Student pickup reads, BM queue/detail/audit and schedule/reschedule/cancel_schedule/complete POST. `api/claims.php`: consistent string record IDs.
- `app/pages/bm.html`, `css/bm.css`, `js/bm.js`: responsive Claims/Reports/Handover tabs, searchable/filterable queues, selected detail, private evidence enlarge/keyboard dialog, notes/confirmation, pickup forms/audit and terminal read-only states. Queue badges use pickup status for pickup records.
- Stale/error dialogs retain draft note and offer explicit refresh; no automatic mutation replay. Async detail ordering prevents late responses replacing a newer selection. Reviewed deep links remain on the requested record.
- `app/routes.php`, navbar partial, `js/shell.js`, `js/student.js`: live role navigation, safe BM deep links, Student notification handoff to real review UI. No primary sidebar.
- `tools/test-workflow.cjs`, `b02-fixture.php`, `check-workflow.php`, `smoke-auth.cjs`, `test-account.cjs`: actual lifecycle/concurrency/browser coverage, test-only rollback failpoints, 17 invariants and current BM navbar/main smoke expectations.
- `README.md`, docs overview, role/API matrix, workflow contract, acceptance/evidence mapping, decision log, demo SOP, blueprint/schema checkpoints and database guide now reflect B05. `tools/build-blueprint.py` retains the current workflow-contract reference when generating the dictionary; `tools/verify-blueprint.py` checks the current executed-batch/pending-release distinction.

## DB CHANGE

**None — no schema migration.** Schema remains **14 tables, 2 views, 14 PK, 13 UNIQUE, 25 FK, 9 CHECK**, with unchanged SQL fingerprint. Transactions reuse claims/items/item_matches/handovers/item_status_history/notifications.

Pickup cancellation updates the same handover SCHEDULED → PENDING, clears `scheduled_at` and retains pickup area, claim APPROVED and item/pair HANDOVER. Reschedule uses the same record; complete sets COMPLETED/completed_at and related item/pair RESOLVED/resolved_at atomically. Detailed actor/before/after/reason audit is private claimant/active-BM notification data; public item history contains generic events only.

Automated mutations used fresh QA accounts/records/media in `refind_db_0_2_0_test`, with exact-ID cleanup and CLI failpoints restricted to that DB. Main smoke did not alter business data: **3 users, 3 reports, 0 claims, 0 handovers, 6 notifications, 4 item photos, 0 claim photos** before/after. Main evidence includes the unchanged user fingerprint. Existing non-destructive setup/backup rules remain in [database guide](../database/README.md).

## TESTS EXECUTED

Working directory: `C:\xampp\htdocs\ReFind_0.2.0`. Each suite ran as a separate command against actual Apache/MariaDB/Chrome, rather than mock/localStorage records.

| Command/check | Actual result |
|---|---|
| `python "tools/prepare-test.py"` | Owned `_Test` app/config/secret/session/DB ready; existing credentials preserved |
| `node "tools/test-workflow.cjs"` | **27 scenario groups PASS**, complete=true, 0 uncaught browser errors |
| `node "tools/test-student.cjs"` | **24 Student regression groups PASS** |
| `node "tools/test-auth.cjs"` | **28 auth/role/ownership regression groups PASS** |
| `node "tools/test-account.cjs"` | **24 profile/password/navbar regression groups PASS** |
| `node "tools/smoke-auth.cjs" --b05` | Main three-account login/logout/read smoke + BM three tabs PASS; business dataset unchanged |
| Live `tools/check-database.php` via smoke, main/test | **9 checks PASS each**, unchanged schema fingerprint |
| XAMPP PHP `tools/check-workflow.php`, main/test | **17 invariants PASS each**, no missing/orphan/invalid media |
| XAMPP PHP `-l` on every app/API/CLI PHP file | **31 files PASS** |
| Node `--check` on application JS and CJS tools | **8 JS + 7 CJS files PASS** |
| Python `ast.parse` / `tokenize.open` on tooling | **6 files PASS**, no bytecode generated |
| `python "tools/verify-blueprint.py"` | **59 checks PASS, 0 failures**; blueprint artefacts/current links, not final as-built or application E2E |

### Verified workflow / integrity

- Real Student report/claim APIs populate BM queues and private detail. Student/guest/invalid-CSRF/unconfirmed operations are rejected without business changes.
- BM request-more-info → Student same-ID resubmit → BM approve works through real HTTP/browser interactions. Notes remain private; repeated/stale decisions are blocked.
- Approval with linked Lost creates exactly one approved owner, confirmed pair and handover. Unlinked approval is supported. Competing active claims close; both reports lock for handover.
- Rejecting one of multiple active claims keeps VERIFICATION; last rejection returns OPEN and allows eligible same-ID resubmit.
- Future campus pickup schedule is independently validated and converted Asia/Jakarta → UTC/ISO `Z`. Reschedule changes time/location, stores private before/after/reason and rejects unchanged/stale requests.
- Cancel schedule retains approved ownership/same pickup ID and permits another schedule. Complete works from PENDING or SCHEDULED, synchronizes both reports to Returned and preserves exact terminal/read-only outcomes.
- Manual status/archive and unlinked Lost recovery are guarded/audited; active claim/approved handover/terminal status prevents conflicting operations.
- Concurrent competing approvals, shared-Lost approvals, completion and same-state reschedules produce one winner and conflict 409 for stale writers, without duplicate owner/pair/pickup/audit.
- Controlled test-only approval-notification and completion-audit failures prove rollback of claim/pair/items/handover/timestamps/history/notifs. DB state snapshots/invariants are checked after failures.
- Public detail/history excludes private review/pickup information and proof. Evidence endpoints retain BM-only authorization; Student pickup/notifications remain own-claim scoped.

### Responsive / evidence

All **three BM tabs and operation/evidence dialogs** were exercised at **360×800, 390×844, 768×1024, 1280×800, 1440×900**, with no global horizontal overflow. Mobile uses stacked list/detail cards; desktop uses queue/detail content beneath top navigation. Browser timezone differs from campus timezone; UI consistently displays Asia/Jakarta. Actual browser scenarios also cover keyboard close/focus, validation/confirmation, stale note retention, status actions and exact reviewed deep links.

- [workflow-results.json](verification/b05/workflow-results.json): 27 named actual scenarios, five viewport checks, complete=true, browser errors and 17 invariants.
- [main-smoke.json](verification/b05/main-smoke.json): main/test schema checks, main three accounts, unchanged business counts/fingerprint.
- [final-checks.json](verification/b05/final-checks.json): final syntax/documentation and read-only main/test invariants; references the executed application-suite evidence above.
- [auth regression](verification/b02/auth-results.json), [account regression](verification/b03/account-results.json), [Student regression](verification/b04/student-results.json): latest respective scope outputs rerun during B05.
- [Claims mobile](verification/b05/claims-390.png) / [desktop](verification/b05/claims-1440.png), [Reports mobile](verification/b05/reports-390.png) / [desktop](verification/b05/reports-1440.png), [Handover mobile](verification/b05/handover-390.png) / [desktop](verification/b05/handover-1440.png): actual isolated-test UI, not target wireframes.
- [Main Claims](verification/b05/main-bm-admin-claims.png), [Main Reports](verification/b05/main-bm-admin-reports.png), [Main Handover](verification/b05/main-bm-admin-handover.png): actual main read-only checkpoint; no seeded approval/return outcomes.

## MANUAL TESTS

Full demo instructions and local credential location: [DEMO_ACCOUNTS.md](DEMO_ACCOUNTS.md).

1. Student A → Found headphones by B → claim with own Lost link/proof → My Activity Pending. BM → same claim → review private clue/evidence → request more info with reason/confirmation.
2. Student A → notification/same-ID claim → revise/resubmit. BM → approve with note/confirmation → verify one pickup and both reports Ready for pickup.
3. BM → Handover → future schedule in Asia/Jakarta → reschedule with different time/location/reason → cancel schedule. Verify Pending pickup, ownership Approved and reports still Ready for pickup, then schedule again.
4. BM confirms simulated physical return → complete pickup → Completed + both reports Returned. Student checks own notification/activity; open reviewed claim link and History read-only.
5. On new eligible reports, demonstrate rejection/same-ID resubmit, operational Under review/Never retrieved and recovered Lost. Existing terminal reports cannot be reset through those actions.
6. At mobile/desktop, open menu, filters, evidence dialogs and confirmation forms; cancel first, then submit. Student must not receive BM controls/private proof bytes. Two BM sessions with old detail should produce 409/explicit refresh without replay.

## GAPS

No blocking failure remains in executed B05 suites. **Final as-built sitemap/ERD/swimlane source/export reconciliation, clean fresh-install acceptance, final demo SOP/package and release ZIP are pending B06.** Current diagram/wireframe exports still carry honest Target / To-be blueprint labels. Main Lost → Returned was not mutated by automated QA; full lifecycle evidence is from the isolated test application.

## NEXT

**B06 — Final verification, as-built academic artefacts, fresh install and clean release package**, after next-batch approval.

## FILE PATH / URL

- Project: `C:\xampp\htdocs\ReFind_0.2.0`.
- Login: `http://localhost/ReFind_0.2.0/login.html`.
- Claims: `http://localhost/ReFind_0.2.0/admin.html?tab=claims`.
- Reports: `http://localhost/ReFind_0.2.0/admin.html?tab=reports`.
- Handover: `http://localhost/ReFind_0.2.0/admin.html?tab=handover`.
- Exact record links: add `&claim=ID`, `&report=ID` or `&handover=ID` to the matching tab.
- Docs overview: `http://localhost/ReFind_0.2.0/docs/`.
- Backend: `app/workflow.php`, `app/staff-read.php`, `api/staff.php`, `api/handovers.php`.
- UI: protected `app/pages/bm.html`, `css/bm.css`, `js/bm.js`.
- QA: `tools/test-workflow.cjs`, `tools/check-workflow.php`, `docs/verification/b05/`.
- Report: `C:\xampp\htdocs\ReFind_0.2.0\docs\B05_REPORT.md`.
- Test: `C:\xampp\htdocs\ReFind_0.2.0_Test`, `http://localhost/ReFind_0.2.0_Test/`, `refind_db_0_2_0_test`.
