# GB3 Implementation Guide
## Phases 34–39

Purpose: this document is the implementation guide for Claude working inside the GB3 repo.  
Goal: evolve GB3 from a basic grounding/chore tracker into a **predictable, low-arousal, trauma-informed family regulation system** that supports structure, consistency, repair, and trust.

---

## Project-wide rules

Claude must follow these rules in every phase:

- The repo on disk is the source of truth.
- Do not assume schema, routes, field names, statuses, file paths, CSS tokens, or business logic.
- Inspect actual files before changing behavior.
- SQLite only.
- Use existing patterns already present in the repo.
- Keep edits minimal, explicit, and production-safe.
- Do not do regex/perl mass edits.
- Do not redesign unrelated areas.
- If a suspected issue is not present in the current repo, do not force a fix.
- Prefer preserving architecture unless a small refactor is required to remove a confirmed defect.
- Every visible UI promise must be backed by real persisted state.
- No consequence flow should exist without a clear repair/review/re-entry path unless the behavior is a true safety issue.
- Crisis/safety handling must be separate from ordinary discipline handling.
- Child-facing UX should reduce shame, reduce ambiguity, and reduce cognitive load.

For every phase, Claude should:

1. Inspect relevant files first.
2. Confirm schema/state/route reality before editing.
3. Trace affected workflows end to end.
4. Implement only the phase scope.
5. Run lightweight verification after edits.
6. Return:
   - findings
   - change plan
   - full updated file contents
   - verification steps/results
   - suggested commit message

---

# Phase 34 — Stability / Trust First

## Goal
Remove anything that makes GB3 feel random, unfair, broken, or unreliable.

## Why this phase matters
In this household context, bugs are not just technical defects.  
Broken state, misleading labels, missing saves, or inconsistent rules damage trust and make adult consistency harder.

## Scope
### 1. Normalize field-name mismatches
Inspect and fix confirmed mismatches across:
- schema
- queries
- controllers
- models/services
- views/templates
- validation
- sorting/order logic

Known likely candidates to verify:
- `display_name` vs `name`
- `sort_order` vs `display_order`
- `pending` vs `pending_review`

Do not assume these are real until confirmed in the repo.

### 2. Fix settings persistence
Inspect current admin settings implementation and ensure:
- real save route exists if settings UI posts data
- controller/service persists values correctly
- saved values reload correctly
- one source of truth exists for app/family settings

### 3. Fix family/app name consistency
Verify whether the displayed family/app name comes from:
- config
- database
- both

Standardize reads/writes so the displayed value always reflects saved state.

### 4. Validate core happy paths
Inspect and fix confirmed defects in:
- kid login
- kid today screen
- proof submission
- admin review approve/reject
- infraction apply/review
- bonus submit/review

### 5. Eliminate state-label mismatches
UI wording must match real backend state values and transitions.

## Deliverables
- Confirmed defects fixed in touched workflows
- Settings save works
- Family/app name uses one reliable source
- No obvious field/status mismatches in core paths
- Core flows no longer have obvious trust-breaking issues

## Verification
At minimum:
- syntax checks for touched PHP files
- schema inspection
- route inspection
- trace each touched workflow
- confirm rendered labels align with actual saved state

## Suggested commit
`phase-34-stability-trust`

---

# Phase 35 — Engine Completion

## Goal
Make every reward, lock, review, and recovery promise in the UI real and auditable.

## Why this phase matters
If the app says a child earned minutes, money, a privilege change, or a review path, the system must persist it clearly and show it consistently.  
Anything less teaches that the rules are flexible or arbitrary.

## Scope
### 1. Complete reward application logic
Inspect current bonus/reward flow and ensure approved bonuses actually apply:
- money / cents
- phone minutes
- game minutes
- any other reward balances present in the repo

Use whatever reward/balance model already exists in the codebase.

### 2. Add reward ledger/history
Create or complete a persistent ledger/history for:
- reward credits
- reward deductions
- manual adjustments if such actions already exist
- source/event type
- timestamp
- actor if appropriate

Do not invent a large accounting system. Keep it consistent with existing app patterns.

### 3. Complete infraction effects
Inspect infraction definitions and ensure the implemented engine fully respects configured fields such as:
- blocked categories
- duration / days
- review dates
- strike resets
- escalation ladders
- repair requirements
- unlock/re-entry conditions

Only expose/configure fields that actually fit the repo’s current design.

### 4. Expose real state in UI
Kid/admin UI must show exact current state, including where applicable:
- active lock
- blocked privileges/categories
- next review date/time
- repair required
- eligible re-entry/unlock path
- current balances/history

### 5. Remove fake or ambiguous “earned” messaging
No UI should say or imply:
- earned
- unlocked
- credited
- restored

unless it is backed by persisted state.

## Deliverables
- Bonus approval changes real balances
- Reward ledger/history exists and is visible where appropriate
- Infractions apply real configured effects
- Review/re-entry state is visible
- No misleading reward or unlock messaging

## Verification
- trace reward submission → approval → balance update → visible history
- trace infraction application → active effect → review/re-entry visibility
- syntax/schema checks for touched files
- confirm labels and balances are derived from real persisted state

## Suggested commit
`phase-35-engine-completion`

---

# Phase 36 — Trauma-Informed Adult Workflow

## Goal
Help adults use GB3 in a calm, predictable, low-escalation way.

## Why this phase matters
The app should reduce power struggles, not accelerate them.  
The adult workflow should support neutral, factual, consistent responses and always provide a path to repair and re-entry.

## Scope
### 1. Replace raw quick-apply consequence flow
Inspect current infraction/consequence UI and convert it into a structured workflow that captures:
- child
- behavior/incident type
- whether this is ordinary discipline or a safety/crisis issue
- short factual note
- consequence/effect
- repair action
- review date/time if appropriate

Keep it efficient, but not impulsive.

### 2. Require repair/review structure where appropriate
For ordinary non-safety behavior flows, require or strongly support:
- repair step
- review checkpoint
- re-entry path

Examples may include:
- redo task
- apology / repair action
- clean-up / corrective step
- calm recheck later
- restoration after review

Claude should adapt to actual repo terminology and avoid introducing jargon that conflicts with current UI.

### 3. Add neutral review/reject templates
Where admin can reject submissions or apply consequences, add calm standardized options such as:
- photo unclear
- needs one more step
- please redo and resubmit
- good effort, not complete yet

Keep freeform notes available if the repo already supports them, but add structured fast options.

### 4. Add adult script prompts
Add small UI guidance that supports calm use, for example:
- state the rule
- state the consequence
- avoid arguing
- offer repair path
- end interaction and review later if needed

This guidance should be subtle and practical, not preachy.

### 5. Separate discipline lane from crisis/safety lane
Create or improve a distinct safety workflow for events involving:
- serious aggression
- severe dysregulation
- threat to self/others
- psychosis-related concern
- other crisis behaviors reflected in app terminology

This lane should not behave like ordinary discipline.  
It should focus on safety, documentation, and follow-up, not routine punishment logic.

## Deliverables
- Consequence application is more structured and less impulsive
- Ordinary discipline includes repair/review/re-entry
- Neutral reject/review templates exist
- Adult script prompts exist
- Safety/crisis workflow is distinct from ordinary discipline

## Verification
- trace ordinary behavior consequence flow end to end
- trace reject/review flow with templates
- confirm safety/crisis events do not use ordinary discipline logic
- syntax checks for all touched files

## Suggested commit
`phase-36-trauma-informed-flow`

---

# Phase 37 — Kid UX Simplification

## Goal
Make the child-facing experience simple, calm, recovery-oriented, and easy to understand at a glance.

## Why this phase matters
Kids in this context may struggle with working memory, emotional regulation, transitions, shame sensitivity, and fairness conflict.  
The UI should answer: what do I do now, what happens next, and how do I get back on track?

## Scope
### 1. Re-center child navigation around self, not siblings
Default child-facing navigation/views toward:
- My Day
- My Next Step
- My Week
- How to Get Back on Track

If family-wide views exist, do not make them the default unless the repo clearly depends on that.

### 2. Make each screen action-focused
Each primary child-facing screen should have one clear main action.  
Reduce clutter, secondary actions, and competing emphasis.

### 3. Add recovery-oriented state communication
Use clear, calm wording for statuses such as:
- waiting for review
- try again
- back on track
- review tomorrow
- needs redo
- approved

Claude should adapt wording to the existing app voice and avoid jarring terminology changes.

### 4. Add countdowns / transition visibility
Where supported by current logic, make time expectations visible:
- next review
- next unlock
- tonight’s check-in
- when to resubmit
- when something will be looked at again

### 5. Improve rejection/redo clarity
After a rejection or incomplete review, clearly show:
- what needs fixed
- what step comes next
- when to resubmit or review again
- how the child can recover

### 6. Reduce sibling comparison pressure
Avoid default experiences that make kids constantly compare:
- points
- consequences
- chores
- privileges
- status

Adult/admin views can remain broader; child default views should be more personal and task-focused.

## Deliverables
- Child UI defaults to self-focused views
- Primary actions are clearer
- Status messaging is calmer and clearer
- Review/unlock timing is easier to understand
- Redo/recovery path is obvious
- Less default sibling comparison

## Verification
- inspect child-facing nav and top screens
- trace child task completion and review feedback flow
- confirm rejection flow answers “what now?”
- syntax checks for touched files/assets

## Suggested commit
`phase-37-kid-ux`

---

# Phase 38 — Admin UX / Operations

## Goal
Make GB3 easier for adults to operate consistently under real household stress.

## Why this phase matters
The system should help the adult quickly see what matters now, avoid mistakes, and correct errors without drama.

## Scope
### 1. Improve admin dashboard prioritization
Add or improve dashboard visibility for operationally useful items such as:
- pending reviews
- active locks
- upcoming review dates
- children needing follow-up today
- recent important actions

Use existing data models instead of inventing a parallel reporting layer.

### 2. Add bulk/fast review tools where safe
If the repo supports multiple submissions/reviews, improve review throughput without sacrificing clarity:
- fast approve/reject
- structured reasons
- grouped queues where appropriate

### 3. Add undo/correction window
Where feasible, allow recent admin actions to be corrected safely:
- mistaken approval
- mistaken rejection
- wrong child/consequence
- accidental action

Keep auditability.

### 4. Improve audit trail clarity
Show who changed what, when, and why where the repo supports it.  
Do not overbuild, but make important administrative state changes easier to understand later.

### 5. Standardize status presentation
Across admin screens:
- consistent color semantics
- consistent wording
- consistent badge/state treatment

Do not allow the same state to look different in different places without reason.

### 6. Improve freeze/lock messaging if present
If the app supports lock, freeze, or write-block states, make the UI explain:
- what is blocked
- why
- until when / next review
- who can change it

## Deliverables
- Admin dashboard is more actionable
- Safe fast-review tools exist where useful
- Undo/correction exists where feasible
- Audit trail is clearer
- Status colors/labels are consistent
- Lock/freeze messaging is clearer

## Verification
- inspect top admin screens and queues
- trace recent-action correction if added
- confirm consistent status labels/colors in touched views
- syntax checks for touched files/assets

## Suggested commit
`phase-38-admin-ux`

---

# Phase 39 — Hardening / Accessibility / Polish

## Goal
Make GB3 safe, mobile-friendly, accessible, and ready for dependable daily use.

## Why this phase matters
Once the family logic is trustworthy, the app needs to be resilient and easy to use on actual devices under real-life conditions.

## Scope
### 1. File upload hardening
Inspect upload flows and tighten:
- file type validation
- file size validation
- storage paths
- filename handling
- access control for uploaded assets

### 2. Auth/session hardening
Inspect auth/session flows and improve where needed:
- admin-only action protection
- session handling
- logout flow
- rate limiting if applicable
- CSRF/form protections using existing stack patterns

### 3. Accessibility pass
Improve where needed:
- contrast
- tap target sizes
- focus states
- semantic labels
- keyboard support where relevant
- readable error/help text

### 4. Mobile polish
Refine phone-first usability:
- spacing
- scroll behavior
- sticky actions only if they help
- form usability
- image/proof upload clarity

### 5. Empty/error states
Improve UX for:
- no assignments
- no pending reviews
- no active consequences
- failed uploads
- validation errors
- unknown/missing records

### 6. Final consistency cleanup
Do a final pass on:
- status badges
- button labels
- confirmation language
- repeated helper text
- visual noise

## Deliverables
- Uploads are safer
- Auth/session handling is tighter
- Accessibility is improved
- Mobile UX is stronger
- Empty/error states are clearer
- Final polish increases daily usability

## Verification
- inspect and test upload/auth related code paths
- syntax checks
- mobile-oriented template review
- confirm key screens have sane empty/error states
- note anything not fully testable without live runtime

## Suggested commit
`phase-39-hardening-a11y`

---

# Recommended implementation order

1. Phase 34 — Stability / Trust First
2. Phase 35 — Engine Completion
3. Phase 36 — Trauma-Informed Adult Workflow
4. Phase 37 — Kid UX Simplification
5. Phase 38 — Admin UX / Operations
6. Phase 39 — Hardening / Accessibility / Polish

---

# Definition of success for GB3

GB3 should become:

- predictable
- calm
- consistent
- review-driven
- repair-based
- trustworthy
- crisis-aware
- mobile-friendly
- clear for adults
- low-shame for kids

The app should not function primarily as a punishment tracker.  
It should function as a **family regulation and structure system** that helps adults stay consistent and helps children understand expectations, recovery, and re-entry.
