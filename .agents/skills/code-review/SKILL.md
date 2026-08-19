---
name: code-review
description: >
  Performs a structured Laravel code review by running through a checklist of common issues
  including N+1 queries, missing input validation, authorization gaps, overly complex controller
  logic, and missing return types. Use this skill whenever a user asks to review Laravel or PHP
  code, check a file for issues, audit a controller, model, or service class, or asks questions
  like "what's wrong with this code", "can you review my PR", "does this look good", or "check
  my code for issues". Also trigger when a user pastes Laravel/PHP code and asks for feedback,
  even if they don't use the word "review". Always prefer this skill over ad-hoc inline
  commentary when reviewing any code snippet longer than ~10 lines.
---

# Code Review Skill (Laravel)

Perform a systematic Laravel code review against a focused checklist of high-impact issues.
Be specific: cite exact line numbers or method names, explain *why* each finding matters, and
suggest a concrete fix using idiomatic Laravel conventions. Do not just list problems — show
the improved version when possible.

---

## Review Checklist

Work through **all five categories** for every review. If a category has no findings, write
"✅ No issues found." so the user knows it was checked.

---

### 1. N+1 Queries

**What to look for:**
- Eloquent relationship accessed inside a `foreach` loop without eager loading
  (e.g. `$post->comments` or `$order->user` inside a loop)
- Missing `with()`, `load()`, or `withCount()` on collections that iterate relationships
- Repeated `DB::select` / `Model::find` calls that could be replaced with a single
  `whereIn` or a joined query
- `$collection->each(fn($m) => $m->relation)` patterns that fire one query per model

**How to flag it:**
```php
// ❌ N+1 — fires one query per post
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name; // SELECT * FROM users WHERE id = ?
}

// ✅ Fix — eager-load the relationship upfront
$posts = Post::with('author')->get();
foreach ($posts as $post) {
    echo $post->author->name; // no extra query
}
```
> ⚠️ **N+1 Query** — `$post->author` inside a loop fires one `SELECT` per post.
> Replace `Post::all()` with `Post::with('author')->get()`.

---

### 2. Missing Validation

**What to look for:**
- `$request->input()`, `$request->get()`, or `$request->all()` used directly in
  `create()` / `update()` without a Form Request or inline `$request->validate()`
- Missing `required`, `nullable`, `integer`, `email`, `max`, `in`, or `exists` rules
  for fields that are persisted or used in queries
- No CSRF protection on state-changing routes (missing `web` middleware or `VerifyCsrfToken`)
- Raw `$_POST` / `$_GET` access instead of the Request object
- `Model::create($request->all())` without `$fillable` / `$guarded` set on the model
  (mass-assignment vulnerability)

**How to flag it:**
```php
// ❌ No validation — mass-assignment risk
public function store(Request $request)
{
    User::create($request->all());
}

// ✅ Fix — use a typed Form Request with explicit rules
// app/Http/Requests/StoreUserRequest.php
public function rules(): array
{
    return [
        'name'  => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'unique:users'],
        'age'   => ['required', 'integer', 'min:0', 'max:120'],
    ];
}

// Controller
public function store(StoreUserRequest $request): RedirectResponse
{
    User::create($request->validated());
    // ...
}
```
> ⚠️ **Missing Validation** — `$request->all()` is passed directly to `User::create()`
> without validation or whitelisting. Use a Form Request and `$request->validated()`.

---

### 3. Authorization Gaps

**What to look for:**
- Controller actions that don't call `$this->authorize()`, a Gate check, or a Policy method
- `Model::find($id)` without scoping to the authenticated user (IDOR vulnerability)
- Missing `auth` / `auth:sanctum` / `auth:api` middleware on routes that require login
- Role or permission checks hard-coded as raw string comparisons instead of Gates/Policies
- Sensitive actions (delete, publish, impersonate) lacking a dedicated Policy method

**How to flag it:**
```php
// ❌ IDOR — any authenticated user can delete any document
public function destroy(int $id): RedirectResponse
{
    $document = Document::findOrFail($id);
    $document->delete();
    return redirect()->route('documents.index');
}

// ✅ Fix — scope to owner or use a Policy
public function destroy(Document $document): RedirectResponse
{
    $this->authorize('delete', $document); // DocumentPolicy@delete checks ownership
    $document->delete();
    return redirect()->route('documents.index');
}
```
> ⚠️ **Authorization Gap** — `Document::findOrFail($id)` fetches any document by ID without
> verifying ownership. Use route–model binding with a Policy and `$this->authorize('delete', $document)`.

---

### 4. Overly Complex Controller Logic

**What to look for:**
- Business logic, multi-step workflows, or external API calls living inside a controller action
- Controller actions longer than ~20 lines or with more than one level of nested conditionals
- Direct `Mail::send`, `Http::post`, or queue dispatch calls that belong in a dedicated
  Action class, Service class, or Laravel Job
- Multiple model mutations in a single action without a database transaction (`DB::transaction`)
- Logic that is duplicated across two or more controller methods (extract to a shared method
  or Action class)

**How to flag it:**
```php
// ❌ Controller doing too much
public function store(StoreOrderRequest $request): JsonResponse
{
    $order = Order::create($request->validated());

    // pricing calculation
    $subtotal = 0;
    foreach ($request->items as $item) {
        $product = Product::find($item['id']);
        $subtotal += $product->price * $item['qty'];
        $order->items()->create([...]);
    }
    $order->update(['total' => $subtotal * 1.1]);

    // inventory
    foreach ($request->items as $item) {
        Product::where('id', $item['id'])->decrement('stock', $item['qty']);
    }

    Mail::to($request->user())->send(new OrderConfirmation($order));

    return response()->json($order, 201);
}

// ✅ Fix — delegate to an Action class and a Job
public function store(StoreOrderRequest $request): JsonResponse
{
    $order = DB::transaction(fn () => CreateOrder::run($request->validated()));
    SendOrderConfirmationJob::dispatch($order);
    return response()->json($order, 201);
}
```
> ⚠️ **Controller Complexity** — `OrderController@store` handles pricing, inventory, and email.
> Extract to a `CreateOrder` Action and a `SendOrderConfirmationJob`.

---

### 5. Missing Return Types

**What to look for:**
- Public controller methods missing a return type (`JsonResponse`, `RedirectResponse`,
  `Response`, `View`, `string`, `void`, etc.)
- Service / Action class methods with no return type declaration
- Methods that return `Model|null` in some paths but only declare `Model`
- Closures passed to `Route::get()` or `array_map()` without return types when the
  surrounding code is fully typed
- `collect()` chains returned as plain `mixed` when `Collection<int, Model>` is deterministic

**How to flag it:**
```php
// ❌ No return types — callers can't rely on the contract
public function index()
{
    return view('orders.index', ['orders' => Order::paginate()]);
}

public function findByEmail(string $email)
{
    return User::where('email', $email)->first();
}

// ✅ Fix — declare explicit return types
public function index(): View
{
    return view('orders.index', ['orders' => Order::paginate()]);
}

public function findByEmail(string $email): ?User
{
    return User::where('email', $email)->first();
}
```
> ⚠️ **Missing Return Type** — `findByEmail()` can return a `User` or `null` but has no
> return type. Declare it as `?User` so callers know to handle the null case.

---

## Output Format

Structure your review like this:

```
## Code Review: <filename or brief description>

### 1. N+1 Queries
<findings or ✅ No issues found.>

### 2. Missing Validation
<findings or ✅ No issues found.>

### 3. Authorization Gaps
<findings or ✅ No issues found.>

### 4. Controller Complexity
<findings or ✅ No issues found.>

### 5. Missing Return Types
<findings or ✅ No issues found.>

---
### Summary
<1–3 sentence overall assessment. Highlight the highest-priority fix.>
```

---

## Tone & Depth Guidelines

- **Be specific**: Always name the method, class, or variable that has the issue.
- **Explain impact**: One sentence on what could go wrong (performance, security, correctness).
- **Show the fix**: Provide a corrected Laravel snippet inline when the fix is non-trivial.
- **Prioritize**: Lead with the most critical finding (security > correctness > performance > style).
- **Skip style nits** unless the user asks — focus on the five checklist categories.
- **Use Laravel idioms**: Prefer Form Requests over inline validate, Policy/Gate over manual checks,
  Eloquent eager-loading over raw SQL, Action/Service classes over fat controllers, and proper
  PHP 8.x return type declarations.