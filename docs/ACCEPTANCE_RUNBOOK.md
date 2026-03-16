# GB3 Acceptance Runbook

Date: 2026-03-16

## Goal
Validate rewrite behavior against copied legacy data and current feature suite.

## One-command Acceptance
- bash /var/www-rewrite/tools/gb3-acceptance-check.sh

## What It Runs
1. Export legacy data from /var/www.
2. Dry-run import into /var/www-rewrite/database/database.sqlite.
3. Real import with truncate.
4. Semantic parity comparison (legacy vs rewrite DB).
5. Rewrite feature tests.
6. Rewrite DB spot-check queries.

## Expected Result
- Script ends with: ACCEPTANCE PASS
- Feature tests are green.
- Parity checker reports OK.

## Notes
- This runbook assumes legacy tooling exists in /var/www/tools.
- If paths differ, set env vars:
  - LEGACY_ROOT
  - REWRITE_ROOT
  - EXPORT_JSON
