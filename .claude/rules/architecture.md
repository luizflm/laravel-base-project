# Architecture

## Request Lifecycle
Route -> Middleware -> FormRequest (validation + authorization) -> Controller -> Action -> Repository (if used) -> Model
Controllers are thin wrappers. Business logic lives exclusively in Actions.
Actions are injected through parameter (method injection), never instantiated with new.
Actions must not depend on Request, Session, or any HTTP-layer class.

## Directory Structure
app/
  Http/
    Controllers/       
    Requests/          # FormRequest classes, one per action
    Resources/         # API Resources for response transformation
    Middleware/
  Actions/             # Business logic
  Models/              # Eloquent models, relationships, scopes, casts
  Enums/               # PHP 8.1+ backed enums for statuses, types
  Events/              # Domain events
  Listeners/           # Event handlers, keep them small
  Jobs/                # Queued work, dispatched from Actions
  Policies/            # Authorization logic, always use policies

## Dependency Rules
- Controllers depend on Actions and FormRequests only
- Actions depend on Models and other Actions
- Models never depend on Actions or Controllers
- Jobs receive only primitive data or model IDs, not model instances
- Events carry minimal payload, listeners do the work

## What NOT To Do
- Do not put business logic in controllers, models, or middleware
- Do not use DB:: facade for queries that Eloquent can handle
- Do not use env() outside of config files
- Do not create god-actions that handle multiple domains
- Do not skip form request validation for 'simple' endpoints