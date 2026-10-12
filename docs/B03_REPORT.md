# B03 — Responsive Shell + Profile / Account Settings

BATCH: B03 — Shared top navigation, persistent own profile, verified password change.

STATUS: **Verified — scope B03.** Business workflows B04/B05 remain pending.

## BASELINE

B02 had working credential login/logout, session/CSRF/expiry/throttling, page/API guards and readonly account GET. Profile/Settings routes and writes returned 501. Navbar markup/logout handlers were repeated per auth page. User approved B03.

At startup both Apache/MySQL were stopped. Existing XAMPP processes were started with existing configuration; HTTP 80 and MariaDB 3306 became available. No global configuration repair or data reseed was performed.

## CHANGELOG

- `app/accounts.php`: own profile serializer, UTF-8/name/password validation, locked own-account revalidation and current-password verification limiter.
- `api/account.php`: real `update_profile` / `change_password`, explicit payload allowlists, CSRF, transaction + SELECT FOR UPDATE, fresh credential fingerprint, SID/CSRF rotation after successful password commit.
- `app/http.php`: structured field errors; messages remain safe, no credential/trace exposure.
- `app/routes.php`: Profile/Settings live pages for both roles.
- `app/pages/profile.html`, `settings.html`: own identifiers readonly, Student identifier private/Student-only, persistent display-name form, current/new/confirmation password form and working logout.
- `app/pages/partials/navbar.html`, `page.php`: trusted shared header fragment; all live pages/messages share one top navbar.
- `css/styles.css`, `js/shell.js`: mobile collapse below top header, desktop account disclosure, active page, own unread count, role-specific Home/Staff home, keyboard/ArrowDown/Escape/outside close/focus return.
- `js/account-ui.js`, `api.js`: field feedback, loading/retry/reset, busy controls, DOM-safe rendering, password byte counter, updated name in summary/header, new CSRF from response, session-expired safe login return without POST replay.
- Auth/landing/message templates and `auth-ui.js` integrated with shared shell; duplicated logout listener removed.
- `tools/test-account.cjs`: fresh QA accounts/records in `_test`, actual backend + browser tests, cleanup by IDs. B02 regression/smoke helpers follow the new account disclosure interaction. Fixture CLI can clear only the test password-attempt limiter.
- README, role/route matrix, acceptance progress, demo guide, decision log and docs overview updated; actual screenshots linked separately from target wireframes.

## DB CHANGE

**No schema migration; 14 tables / 2 views / 25 FK unchanged.** Profile fields remain in `users`; no artificial `user_profiles` entity.

User-initiated operations update only authenticated `users.full_name` or `users.password_hash`. Email/NIM/role/target user_id are not editable request fields. B03 integration mutations use fresh `_test` QA accounts/records; exact IDs are cleaned. Main app smoke is read-only with respect to database rows; counts/user fingerprint matched before/after.

Password changes preserve original absolute-session deadline, rotate current SID/CSRF and refresh credential fingerprint after commit. Other sessions become invalid at their next protected request. Concurrent password requests are serialized by user-row lock; stale fingerprint fails 401 instead of overwriting the winning password. Failed current-password attempts are limited independently of login attempts (5 / 15 min per user+IP).

## TESTS EXECUTED

Working directory: `C:\xampp\htdocs\ReFind_0.2.0`.

| Command / check | Real result |
|---|---|
| `python "tools/prepare-test.py"` | Owned isolated app/database prepared; current seed credentials preserved |
| `node "tools/test-account.cjs" --api-only` | 13 API/database scenario groups passed during backend checkpoint |
| `node "tools/test-account.cjs"` | **24 integration/browser scenario groups PASS**, no uncaught JS errors |
| `node "tools/test-auth.cjs"` | **28 B02 auth/role/ownership regression groups PASS** with current navbar |
| `node "tools/smoke-auth.cjs" --b03` | Three main accounts login → Profile/Settings read → logout PASS; main DB data unchanged |
| Main/test schema checker via smoke | 9 checks PASS on each database; schema fingerprint unchanged |
| PHP lint, Node syntax and Python AST checks | Runtime/tool syntax passed; output captured in the working session |
| `python "tools/verify-blueprint.py"` | Documentation/diagram/link checks rerun against current docs |

Backend assertions cover name trim/UTF-8/120-character boundary/control characters, readonly injection, foreign UID, guest/CSRF/origin denial, old password, mismatch/same/short/overlong/NUL password, 72-byte bcrypt boundary, new/old login, replayed cookie, other session revocation, concurrent updates, current password rate limit, expired-session mutation denial, public reporter-name persistence without NIM/email leakage.

Browser assertions cover actual profile save/reload/relogin, summary/navbar update, reset/inline error/focus, simulated 500 retry preserving draft, deterministic in-flight busy/duplicate prevention, actual password change and logout, empty fields after success, no secret in localStorage, expired-form safe return, HTML escaping, own unread-count isolation, keyboard/disclosures and both-role responsive layouts.

One initial UI assertion used Playwright `isDisabled()` directly on a fieldset and a timing delay. It was corrected to native fieldset disabled state with a held request gate; all final scenarios passed. No application failure remains in the executed suite.

### Responsive coverage

Both Student and BM Profile/Settings were exercised at **360×800, 390×844, 768×1024, 1280×800, 1440×900**. Navigation opens below the mobile header; no global horizontal overflow; menu/logout touch targets, active page, Escape/focus and outside close work. Twenty role/page/viewport screenshots are provided.

Bukti output:

- [account-results.json](verification/b03/account-results.json)
- [main-smoke.json](verification/b03/main-smoke.json)
- `docs/verification/b03/`: current actual screenshots, including main app profiles and isolated test layout cases.

## MANUAL TESTS

1. Login Student A/B or BM; Account → My profile → edit display name → save → reload/relogin.
2. Check readonly email/role and private Student identifier. BM has no Student identifier field.
3. Account settings → current password + different new password + confirmation → update. Current session remains; old password fails at next login; new password succeeds.
4. Open the same account in another browser session before change; its next protected request asks for login.
5. On mobile open Menu → Account; Escape/outside click closes correctly. Logout works from account menu and Settings.

Initial credential receipt is not rewritten after a user changes their password. Use the new password you selected; setup does not reset an existing account.

## GAPS

Student discovery/report/claim/upload/My Activity/notifications UI B04 and BM operational review/handover B05 still pending. Navbar currently links only live pages; future module navigation is added when the module works. Unread badge uses real existing API, not a fake notification page.

Auth + profile + account settings **have been verified end-to-end**. Full Lost → Found → Claim → BM Review → Handover → Returned has **belum diverifikasi secara end-to-end**. Target wireframes/flowcharts remain clearly marked until final system reconciliation B06.

## NEXT

**B04 — Student discovery + workflows**, after next-batch approval: Explore cards/search/filter, Lost/Found forms/uploads, matching, ownership claim/resubmit, My Activity and notifications/history.

## FILE PATH / URL

- Project: `C:\xampp\htdocs\ReFind_0.2.0`.
- Profile: `http://localhost/ReFind_0.2.0/profile.html`.
- Settings: `http://localhost/ReFind_0.2.0/settings.html`.
- Actual templates: `app/pages/profile.html`, `settings.html`, `partials/navbar.html` (direct template HTTP access is blocked).
- API/helper: `api/account.php`, `app/accounts.php`.
- UI: `css/styles.css`, `js/shell.js`, `js/account-ui.js`.
- Report/evidence: `docs/B03_REPORT.md`, `docs/verification/b03/`.
