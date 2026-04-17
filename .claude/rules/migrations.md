---
paths:
  - "database/migrations/*"
---

# Migrations

- Do not use Cascade Deletes on columns, instead, use Restrict Deletes.
- Do not use default values on columns
- Do not use enum() method on columns, instead, use string()
