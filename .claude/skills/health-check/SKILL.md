---
name: health-check
description: Run this skill after completing any implementation work. Execute all three checks in order. If any check fails, fix the issues before reporting completion..
---

# Health Check

## Step 1: Code Style (Pint)
Run Pint in test mode to detect style violations without auto-fixing:
```bash
./vendor/bin/pint --test
```

If running in Docker:
```bash
docker exec app ./vendor/bin/pint --test
```

If violations are found:
1. Review the diff output
2. Fix the code manually so you understand what was wrong
3. Do NOT run pint without — test to silently fix issues
4. Run pint — test again to confirm fixes

## Step 2: Test Suite (Pest)
Run the full Pest test suite:
```bash
php artisan test
```

If running in Docker:
```bash
docker exec app php artisan test
```

If tests fail:
1. Read the failure output carefully
2. Determine if the failure is in your new code or existing tests
3. Fix your code if the failure is yours
4. If an existing test broke because of your changes, check whether the test expectation is outdated or your code introduced a regression
5. Re-run only the failing test file first, then the full suite

## Step 3: Static Analysis (Larastan)
```bash
./vendor/bin/phpstan analyse --memory-limit=512M
```

If errors are found, fix type issues and missing return types. Do not suppress errors with @phpstan-ignore unless discussed first.

## Reporting
After all three checks pass, summarize:

- Pint: [pass/fail, number of files checked]
- Pest: [pass/fail, number of tests, assertions]
- Larastan: [pass/fail, errors found]