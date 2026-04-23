# Project: [App Name] - System Prompt

You are working on a Laravel 13 application. This is a production
backend/frontend service handling [domain description]. The application runs
inside Docker containers.

## Critical Rules
All comments, commit messages, and documentation must be in English only.
Do not generate code with comments in any other language.
Always run the health-check skill after completing any implementation.
Never commit code that fails Pint, Pest, or Larastan checks.
If you are unsure about an architectural decision, stop and ask.
Do not guess. Do not invent new patterns that are not already in
the codebase.

## Imports
@rules/coding-style.md
@rules/architecture.md
@rules/testing.md
@rules/security.md
@rules/git-workflow.md

## Environment
The application runs in Docker. All artisan, composer, and pest
commands must be executed inside the app container:
```bash
docker exec app php artisan [command]
docker exec app ./vendor/bin/pint [args]
docker exec app php artisan test [args]
docker exec app ./vendor/bin/phpstan analyse
```

## Domain Overview
[Brief description of what the application does, who the users are, what the core entities are and how they relate to each other. This section is 10–20 lines of plain language explaining the business domain so Claude understands the WHY behind the code.]

## API Structure (if it has)
All endpoints are versioned under /api/v1/. Authentication uses Laravel Sanctum with token-based auth. Responses always use API Resources, never raw model output. Pagination follows Laravel default with per_page parameter.

## Key Conventions
- Single-action controllers for non-CRUD endpoints
- Resource controllers for standard CRUD
- FormRequests for all validation, no inline rules in controllers
- Actions for business logic, injected via parameter
- Enums for all status and type fields
- Events + Listeners for side effects (notifications, logging)
- Jobs for anything that takes more than 200ms
- Policies for all authorization checks

## Database
Boost MCP has access to the schema. Use it to inspect tables. Migrations are the source of truth for schema changes. Always create a migration for any database change, never edit existing migrations that have been run. Use Eloquent factories with explicit states for test data.
