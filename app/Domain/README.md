# Domain Modules

This rewrite keeps business rules under app/Domain by module:
- Auth
- Rotation
- Bonus
- Submission
- Infraction
- Privilege
- Ledger

Keep controllers thin. Put state transitions and validation in domain services.
