# Five-minute presentation / demo script — B06

Use the actual app and clearly identify the dataset as synthetic classroom examples. Screen-only workflow recordings are available: [Student](verification/b06/DEMO_STUDENT_WORKFLOW.webm) and [BM](verification/b06/DEMO_BM_WORKFLOW.webm). The script below adds the presentation narration.

| Time | Talking point / actual action |
|---|---|
|0:00–0:35|Introduce ReFind: campus Lost & Found; scattered reports, ownership verification and traceable return. State mobile-first/card-based discovery and Student/BM actors.|
|0:35–1:05|Show draw.io sitemap and ERD briefly: page→feature, explicit cardinality,14 tables; claims→handovers1:0..1 enforced UNIQUE. Explain suggested match is rule-based, not ownership approval.|
|1:05–1:50|Login Student A; Explore40 synthetic Lost/Found examples, search/filter/pagination, public photos and clear status. Open existing C11 claim via My Activity or original Found item2 for a new scenario.|
|1:50–2:35|Login BM; Claims queue, private finder clue/Lost/claim proof. Request more info with note/confirmation; Student resubmits same-ID claim. Switch back BM and approve. Show one verified owner/pickup.|
|2:35–3:40|Handover: choose active campus area/future time (Asia/Jakarta), schedule, reschedule and cancel appointment. Explain cancelled schedule leaves ownership approved. Schedule again and confirm simulated physical return; both reports become Returned.|
|3:40–4:20|Show private notification/audit vs generic public History; exact reviewed/terminal record is read-only. Briefly show Profile/name persistence and Settings/password workflow without displaying a password.|
|4:20–5:00|Show mobile/desktop screenshots and evidence summary: auth28/account24/Student24/BM27, seed7, fresh13 browser groups,81 artefact checks. Explain clean ZIP and new empty-DB fresh install verified. Close with the complete Lost→Returned value.|

## Fast prepared cases

- Pending review: C11 (Student A), C12 (Student B).
- Request-more-info/resubmit: C07 (A), C08 (B).
- Rejection/resubmit: C09 (A), C10 (B).
- Pending pickup: C01/C02; Scheduled: C03/C04; Returned: C05/C06.
- Operational status/archive: C13–C16; editable own reports: C17/C18/C19 where eligible.
- Brand-new full scenario: original item2 Found, Student A Lost item1; both remain available at the initial B06 checkpoint.

Each demo changes real persisted records. For another run after a terminal outcome, use another eligible case or create new Lost/Found reports; the non-destructive setup does not reset existing outcomes.
