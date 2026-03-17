# GB3 Feature Implementation Guide
## Phase 40 — Payout Requests + Consequence Visibility

Purpose: implement two requested features in the current GB3 repo:
1. kids can request payout of earned bonus balances
2. kids can see clear consequence details instead of only a generic paused-privilege message

This guide is written for Claude Opus 4.6 working directly inside the repo.

---

## What I verified from the current repo

The current code already has the right foundation:

- reward balances already exist on `privileges`:
  - `bank_cents`
  - `bank_phone_min`
  - `bank_games_min`
  - `bank_other_min`
- reward ledger/history already exists via `ledger_entries`
- bonus approvals already credit the kid bank through the ledger service
- kid history already shows recent consequences, but the kid-facing “today” experience still appears too generic when privileges are paused
- current consequence state is stored in `infraction_events`, including:
  - definition label
  - blocks_json
  - days_applied
  - review_on
  - note
  - review_note
  - review_action
  - reviewed_at

This means the new features should extend existing reward/ledger/infraction systems instead of inventing parallel logic.

---

## Non-negotiable rules

- The repo on disk is the source of truth.
- Do not assume schema, routes, field names, statuses, or view names.
- Inspect actual files before editing.
- SQLite only.
- Use existing Laravel and repo patterns.
- Keep edits minimal and production-safe.
- No regex/perl mass edits.
- Prefer extending existing reward/ledger/infraction models over building duplicate systems.
- No UI promise without real persisted state.
- Payout approval must be idempotent.
- Kid-facing consequence copy must stay calm, specific, and non-shaming.
- Do not expose sensitive admin-only notes if the existing repo uses them for internal context; inspect actual note usage first.

---

# Feature A — Bonus Payout Requests

## Goal
Allow a kid to request payout of their earned reward bank.  
When an admin approves the payout, the bank resets to zero for the requested categories and a ledger record is written.

## Product behavior
### Kid side
On the kid bonuses screen:
- show current earned balances clearly
- add a “Request Payout” action when any payout-eligible balance is above zero
- kid can request payout for the current available bank
- after requesting, show a calm “Payout requested / waiting for review” state
- prevent duplicate pending payout requests

### Admin side
Add an admin payout review workflow:
- list pending payout requests
- approve request
- deny request
- optionally add short review note

### Approval behavior
On approval:
- debit the requested bank amounts from the kid’s privilege bank
- write ledger entry or entries that clearly record the payout
- mark payout request approved
- ensure approval cannot be applied twice

### Denial behavior
On denial:
- keep bank unchanged
- mark payout request denied
- optionally store review note
- allow kid to request again later

## Data model direction
Inspect repo first, then implement the lightest clean option.

Preferred approach:
- add a `payout_requests` table rather than overloading another table

Suggested fields to verify/implement:
- id
- kid_id
- status (`pending`, `approved`, `denied`, maybe `cancelled` only if needed)
- requested_cents
- requested_phone_min
- requested_games_min
- requested_other_min
- requested_at
- reviewed_at nullable
- reviewed_by_actor_type
- reviewed_by_actor_id
- review_note nullable

Indexes:
- kid_id + status
- status + requested_at

Important:
- snapshot the requested values at request time
- do not auto-read current bank during approval and wipe more than requested
- on approval, debit the snapshot amounts, clamped safely if needed
- keep approval transaction-safe and idempotent

## Implementation tasks
1. Inspect current bonus, privilege, and ledger flows.
2. Add migration/model for payout requests.
3. Add service logic for:
   - create request from current available balances
   - prevent duplicate pending request
   - approve request with debit + ledger + state update in one transaction
   - deny request cleanly
4. Add kid route/controller action for request submission.
5. Add admin route/controller/view for payout review.
6. Update kid bonus page UI to show:
   - current balance
   - request payout button
   - pending payout state/message
7. Update admin dashboard or reviews area to surface pending payout requests if that fits current structure.

## Ledger behavior
Use the existing ledger system.  
Do not create a second history mechanism.

Suggested source values:
- `payout_request_approved`
- `payout_request_adjustment` only if absolutely needed

Suggested debit note example:
- `Payout approved`
or
- `Cash payout approved`
Keep wording simple and neutral.

## Acceptance criteria
- Kid can request payout when balance > 0
- Kid cannot create duplicate pending requests
- Admin can approve or deny
- Approval debits the requested balance exactly once
- Denial does not change balances
- History/ledger reflects payout approval
- Kid UI shows pending payout status clearly

## Suggested commit
`phase-40-payout-requests`

---

# Feature B — Kid-Facing Consequence Details

## Goal
When a consequence is active, the kid should be able to understand:
- what consequence was applied
- what privileges are affected
- how long or until when
- when it will be reviewed
- what the next step is

The UI should be calm and informative, not vague and not shaming.

## Product behavior
On the kid-facing side, show active consequence details in a predictable place, ideally on:
- Today page
- optionally Rules / History if that fits current structure

At minimum, when there is an active unreviewed consequence affecting current privileges, show:
- consequence label
- blocked categories (phone, games, other)
- review date if present
- short child-safe explanation / next step
- repair or recheck note if the system already stores one appropriately

## Important content rules
- prefer definition label and structured fields over dumping raw note text
- if raw notes may contain admin-only wording, sanitize or avoid showing raw notes directly
- show calm copy such as:
  - “Current consequence”
  - “What is paused”
  - “Next review”
  - “How to get back on track”
- avoid shame-based wording

## Implementation direction
Inspect how current Today page grounding banner is built and extend that, rather than creating a disconnected component.

Recommended data source:
- latest active/relevant `infraction_events` for the current kid
- filter for events not fully resolved/reviewed, or events whose applied locks still matter
- map `blocks_json` into friendly labels
- use `review_on`, `days_applied`, and current lock-until timestamps where appropriate

## Implementation tasks
1. Inspect current kid Today controller/view and current consequence/history rendering.
2. Add a clear query/helper for the kid’s current active consequence context.
3. Pass active consequence data into kid-facing views.
4. Update the grounding/paused banner to show:
   - consequence label
   - paused privileges
   - review date
   - concise next-step text
5. If safe and useful, show a small “recent consequence details” card in kid history too.
6. Keep copy neutral, brief, and consistent.

## Acceptance criteria
- A kid with an active consequence can see what it is
- affected privileges are named explicitly
- review timing is visible when present
- child-facing wording is calm and specific
- no obvious admin-only text leaks into kid views

## Suggested commit
`phase-41-kid-consequence-visibility`

---

# Additional improvements worth making while in these files

These are not mandatory, but Claude should inspect and implement them if they are small, clean, and clearly beneficial.

## 1. Add payout history visibility
On the kid reward/history screen, make payout approvals clearly visible as debits or “paid out” events so the bank reset is understandable.

## 2. Show pending payout status on bonus page
If a payout request is pending, show the snapshot requested amounts so the child knows what is being reviewed.

## 3. Improve current consequence timing language
If lock-until timestamps exist, show friendly text like:
- “Review today”
- “Review tomorrow”
- “Paused until ...”
Use existing app timezone/formatting patterns.

## 4. Avoid duplicative status language
Use one consistent terminology set across kid views:
- Waiting for review
- Try again
- Requested payout
- Payout approved
- Current consequence
- Back on track

## 5. Consider admin quick actions later
After this phase, a follow-up phase could add:
- approve payout from dashboard
- payout filters/history
- printable or exportable allowance log

Do not overbuild those in this phase unless they fit naturally.

---

# Output format

Return results in this exact structure:

1. FINDINGS
- confirmed repo findings only
- exact files involved
- root cause / current extension points

2. CHANGE PLAN
- ordered list of implementation steps

3. FILE CHANGES
For each changed file:
- path
- why it changed
- full updated file content

4. MIGRATIONS
- any new migration files
- what schema they add/change

5. VERIFICATION
Show concrete checks and outcomes:
- syntax checks
- route checks
- schema checks
- happy-path traces:
  - kid requests payout
  - admin approves payout
  - admin denies payout
  - kid sees active consequence details

6. SUGGESTED COMMITS
- `phase-40-payout-requests`
- `phase-41-kid-consequence-visibility`

---

# One-line build brief

Extend the existing bank, ledger, and infraction systems so kids can request payout of earned rewards and can clearly understand active consequences, using calm, specific, persisted state throughout.
