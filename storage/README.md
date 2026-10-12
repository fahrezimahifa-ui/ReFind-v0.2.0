# Private runtime storage

Direct HTTP access is denied by Apache. Images are served only through the photo endpoint and its authorization checks.

- `auth/`: per-install secret and atomically locked login-attempt records.
- `sessions/`: PHP server-side sessions, isolated per app/test copy.
- `setup/`: installation receipts and **generated demonstration credentials**. `demo-accounts.json` is local, private, Git-ignored, and excluded from submission ZIP.
- `items/`, `evidence/`, `claims/`: future public/private image collections.

These runtime files are not application source. Do not publish the secret, sessions, or generated credential receipts.
