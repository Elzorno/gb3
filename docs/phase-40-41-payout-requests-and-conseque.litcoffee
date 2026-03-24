phase-40-41-payout-requests-and-consequence-visibility

Feature A: Payout Requests
- Kids can request payout of earned bonus bank
- Snapshot bank balances at request time
- Max 1 pending payout request per kid
- Admin can approve (debits snapshot amounts via ledger) or deny
- Approval is idempotent, denial leaves bank intact
- Dashboard shows pending payouts inline

Feature B: Kid Consequence Details
- Replace generic "privileges paused" with concrete consequence info
- Show: consequence name, paused privileges, review date, next-step text
- Use calm, low-shame language throughout
- Pull from InfractionEvent + InfractionDef (label, blocks_json, repairs_json)
- No admin-only notes exposed to kids

Files added:
- database/migrations/2026_03_17_100000_create_payout_requests_table.php
- app/Models/PayoutRequest.php
- app/Domain/Payout/PayoutService.php
- app/Http/Controllers/PayoutController.php
- resources/views/admin/payouts/index.blade.php

Files modified:
- routes/web.php
- app/Http/Controllers/BonusController.php
- app/Http/Controllers/AdminDashboardController.php
- app/Http/Controllers/RotationAssignmentsController.php
- resources/views/app/bonuses.blade.php
- resources/views/app/today.blade.php
- resources/views/admin/dashboard.blade.php