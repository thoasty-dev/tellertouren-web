---
paths:
  - 'database/migrations/**'
---

# Migrations

## Preserve legacy migration compatibility
Historical migration filenames intentionally match the deployed legacy database so they are skipped there and run on fresh installs. Never execute the irreversible legacy-blog removal migration against a developer's current database during implementation; validate it with isolated databases and migrate --pretend.
