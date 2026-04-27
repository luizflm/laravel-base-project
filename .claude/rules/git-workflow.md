# Git Workflow Rules

## Commit Messages

Commit messages must follow the **Conventional Commits** specification.

### Format

```
<type>: <imperative verb> <short description>
```

### Types

| Type | Description |
|---|---|
| `feat` | A new feature |
| `fix` | A bug fix |
| `refactor` | Code change that neither fixes a bug nor adds a feature |
| `docs` | Documentation changes only |
| `style` | Formatting, missing semicolons, etc. — no logic change |
| `test` | Adding or updating tests |
| `chore` | Build process, dependency updates, tooling |
| `perf` | Performance improvements |
| `ci` | CI/CD configuration changes |

### Imperative Verb Rule

The description **must start with an imperative verb**, written as if completing the sentence:
> *"If applied, this commit will: __________"*

**Good examples**
```
feat: create posts CRUD
fix: resolve sidebar toggle behavior
refactor: extract email sender into dedicated service
docs: add API authentication guide
chore: update dependencies to latest versions
```

**Bad examples**
```
feat: created posts CRUD        # past tense
fix: sidebar is now fixed       # not imperative
refactor: refactoring the auth  # gerund form
feat: posts                     # no verb, too vague
```

---

## 2. Branch Names

Branch names must mirror the commit message convention — using the same type prefix and an imperative description written in `kebab-case`.

### Format

```
<type>/<imperative-verb>-<short-description>
```

### Examples

**Good examples**
```
feature/create-posts-crud
fix/change-sidebar-behavior
refactor/extract-email-sender-service
docs/add-api-authentication-guide
chore/update-project-dependencies
```

**Bad examples**
```
posts-crud                        # missing type prefix
feature/created-posts-crud        # past tense
feature/PostsCrud                 # not kebab-case
fix/sidebar                       # too vague, no verb
my-branch                         # no convention at all
```

> **Note:** Use `feature/` as the branch prefix for `feat` commits (avoid `feat/` in branch names for readability).

---

## 3. Pull Request Title

The PR title **must match the branch name**, replacing slashes with a readable separator and preserving the same wording.

### Format

```
<type>: <imperative-verb> <short-description>
```

### Examples

| Branch Name | PR Title |
|---|---|
| `feature/create-posts-crud` | `feature: create posts crud` |
| `fix/change-sidebar-behavior` | `fix: change sidebar behavior` |
| `refactor/extract-email-sender-service` | `refactor: extract email sender service` |

> The PR title acts as a unique identifier that ties the branch, commits, and review together under a single, traceable name.

---

## 4. Pull Request Description

The PR description must serve as **documentation of the work done**. It should give reviewers and future readers enough context to understand *what* changed and *why*, without having to dig through the diff.

### Required Structure

```markdown
## Summary
A brief explanation of the goal of this PR and the problem it solves.

## Changes
A clear list of the actions taken, written in past tense:
- Created the posts controller with index, show, create, update, and delete endpoints
- Added request validation using the PostRequest form class
- Defined the Post model with fillable fields and relationships
- Written unit tests covering all CRUD operations

## Notes (optional)
Any relevant context, trade-offs, follow-up tasks, or decisions made during implementation.
```

### Example

```markdown
## Summary
Implements full CRUD functionality for the posts resource, enabling users to create, read, update, and delete posts through the API.

## Changes
- Created `PostsController` with resourceful methods (index, show, store, update, destroy)
- Added `PostRequest` for input validation (title required, body required, max lengths enforced)
- Defined `Post` model's `belongsTo` relationship to `User`
- Registered resource routes under `/api/posts`
- Written feature tests for all five endpoints

## Notes
Soft delete was intentionally left out of this scope and will be handled in a follow-up task.
```