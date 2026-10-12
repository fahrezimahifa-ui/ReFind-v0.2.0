# UX01 — Selection, navigation, status clarity and accessible visual polish

BATCH: UX01 — fixes approved after the B06 UI/UX/colour/stack audit.

STATUS: **Verified — approved UI/UX fixes, regression, artefacts and extracted-ZIP fresh install.**

## BASELINE

B06 supplied the complete local XAMPP MVP,40 reports/12 claims/6 handovers and draw.io artefacts. Audit identified persistent BM selection after filters, competing primary buttons, missing target identity in confirmation, unscrolled deep links, Completed showing “Not scheduled yet”, indirect handover notification routes, raw audit jargon, weak focus/secondary contrast and inconsistent native upload styling.

## CHANGELOG

- `js/bm.js`, `app/pages/bm.html`: immediate action invalidation on search/filter/page changes; request sequencing prevents late results replacing new selection. Explicit deep links/saved outcomes remain pinned with context. Mobile queue/detail views, focused entry and Back to queue; confirm item/claimant/record ID; single primary action, secondary request-info and danger rejection. Conflict refresh preserves an in-memory draft, resets confirmation and never replays POST.
- `js/student-common.js`, `student.js`: shared semantic state colours; Returned/completion time with exact claim focus, complete pickup pagination, direct BM handover notification links and human-friendly WIB/area presentation. Synthetic titles have concise headings with explicit classroom badges; stored titles/matching stay intact.
- `app/pickup-read.php`, `api/notifications.php`, `handovers.php`: authorized read-only event presentation of existing private audit messages. Original message is preserved; BM can expand it. Handover metadata is scoped to BM or the related claimant. No schema/data rewrite.
- `css/auth.css`, `student.css`, `bm.css`: stronger focus/control/muted tokens, consistent status colours, readable badges, secondary/danger/warning button hierarchy and visual-viewport-aware dialogs.
- `app/pages/report.html`, `claim.html`, `js/uploads.js`, `shell.js`: optional photo disclosures/custom picker presentation retaining native file inputs and validation. Upload/errors reveal collapsed media; saved proof is kept explicitly. Visual viewport height/top and keyboard state improve dialog/sticky-action behavior.
- Draw.io sitemap/user/flowchart definitions and conceptual wireframes reconcile focus/back navigation, target confirmations, direct pickup handoff and Returned time. New UX evidence and edition-specific packaging/fresh-install helpers.

## DB CHANGE

**None —14 tables/2 views/25 FK retained.** Main synthetic dataset and existing credentials are preserved; main smoke checks user fingerprint/business counts before/after. Existing raw private audit/history records are not rewritten. New API fields are authorized presentation metadata.

## TESTS EXECUTED

| Command/check | Actual result |
|---|---|
| `python tools/prepare-test.py` | Owned isolated test copy/config/DB ready |
| `node tools/test-ux.cjs` | **12 groups PASS**,0 browser errors; fixtures/media cleanup exact-owned IDs |
| `node tools/test-auth.cjs` | **28 regression groups PASS** |
| `node tools/test-account.cjs` | **24 regression groups PASS** |
| `node tools/test-student.cjs` | **24 regression groups PASS** |
| `node tools/test-workflow.cjs` | **27 regression groups PASS**, including transactional/race/rollback paths |
| `node tools/smoke-auth.cjs --ux01` |3 main accounts/shared pages/BM tabs PASS; business dataset unchanged;9 schema checks each main/test |
| UX workflow checker |17 invariants PASS; no missing/orphan/invalid media |
| Draw.io/export/link verification |81 checks PASS;11 diagrams/13 sheets +8 editable conceptual wireframe boards regenerated |
| `python tools/build-release.py --batch UX01` / `verify-fresh-install.py --batch UX01` |8 setup/archive checks +13 actual browser groups PASS,0 browser errors; fresh schema/media/workflow verified; main unchanged |
| PHP / Node / Python syntax |34 PHP /18 JS-CJS /9 Python files PASS |

### Computed browser contrast

- Primary text13.69:1; secondary page text5.28:1; pending badge12.12:1 (text target4.5).
- Keyboard focus4.69:1 and search control boundary3.27:1 (non-text target3).
-12 UX groups exercise stale selection, pagination, late response, primary/danger hierarchy, target identity, pinned/outcome focus, draft reconfirmation, Returned timestamp, private/human-readable handover notifications, keyboard upload/disclosure, XSS-safe synthetic labels and five viewports.
-360×800,390×844,768×1024,1280×800,1440×900: no global overflow; actions≥44px high; dialogs fit. Reduced390×420 viewport confirms scrollable confirmation; this is not a physical-device keyboard test.

## MANUAL TESTS

1. BM select Pending, then filter Rejected/search a different item/page2. Detail/actions must follow current results; empty results have no decisions.
2. Open reviewed-claim/pickup notification: exact target gets focus. On mobile use Back to queue. Review confirmation names the item/claimant/ID.
3. Two BM sessions: stale submit409 → refresh → reopen. Note retained, confirmation unchecked, no automatic save.
4. Student C05/C06: Returned + completion time; no unscheduled message. BM pickup notification goes directly to that handover; human-readable area/WIB summary with raw audit expandable only in authorized context.
5. Optional photos via summary/keyboard, bad SVG, valid JPEG preview, retain/remove existing proof; required-field errors focus the appropriate field.
6. Android Chrome/iOS Safari physical device: open keyboard in report/claim/BM dialog and check visible input, scrolling, action access and safe-area spacing.

## GAPS / NEXT

No failure remains in executed UX/regression checks. Physical-device keyboard behavior is **manual verification pending**; visual-viewport handling and reduced-viewport simulations are implemented/tested. Latest assessment rubric/visual references can refine later design review; current scope follows the supplied guide and approved stack.

## FILE PATH / URL

- Project: `C:\xampp\htdocs\ReFind_0.2.0`; login `http://localhost/ReFind_0.2.0/login.html`.
- Report: `docs/UX01_REPORT.md`; evidence `docs/verification/ux01/ux-results.json`, `main-smoke.json`, screenshots.
- Latest release edition: `releases/ReFind_0.2.0_UX01.zip`, adjacent `.manifest.json`/`.verification.json` tie checks to the exact delivered archive. Historical B06 release is retained separately.
- Fresh evidence/clips: `docs/verification/ux01/fresh-install-results.json`, `fresh-browser-results.json`, `DEMO_STUDENT_WORKFLOW.webm`, `DEMO_BM_WORKFLOW.webm`.
