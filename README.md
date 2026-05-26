# Grounding Buddy Rewrite

Grounding Buddy is a Laravel-based rewrite of a family workflow app for chores, bonus rewards, consequence tracking, and caregiver review.

## Current Stack

- PHP 8.2+
- Laravel 12
- SQLite by default
- Vite for frontend assets

## Core Product Areas

- Kid login with 6-digit PIN
- Daily chore assignments and proof submission
- Bonus claim, proof, review, and payout request flow
- Caregiver review queue for chores, bonuses, and payouts
- Privilege pause / consequence tracking
- Legacy data import and parity validation

## Security Notes

- New proof uploads are stored on the private `local` disk.
- Caregiver proof review uses an authenticated admin route instead of public `/storage` URLs.
- Kid sessions regenerate on login and invalidate on logout.
- Review feedback is split into:
  - `kid_note`: visible to the child
  - `admin_note`: internal caregiver context

## Local Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

Optional frontend setup:

```bash
npm install
npm run build
```

## Running

```bash
php artisan serve
```

Or use the bundled development script:

```bash
composer run dev
```

## Testing

```bash
php artisan test
```

## Acceptance / Operations

Useful project scripts live in `tools/`:

- `gb3-acceptance-check.sh`
- `gb3-go-live-preflight.sh`
- `gb3-freeze-writes.sh`
- `gb3-restore-drill.sh`
- `gb3-legacy-parity-probe.sh`

The main acceptance runbook is in [docs/ACCEPTANCE_RUNBOOK.md](/var/www-rewrite/docs/ACCEPTANCE_RUNBOOK.md:1).
