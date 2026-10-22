# ASSESSOR GUIDE — CONFIDENTIAL

> **Do not give this file to candidates.**
> Remove `ASSESSOR_GUIDE.md` from the project **before** handing it over. Candidates are allowed to use AI tools, and those tools read every file in the project folder — leaving this file in place would reveal every answer. See [Preparing the candidate package](#2-preparing-the-candidate-package).

---

## 1. Assessment Overview

| Item | Value |
| --- | --- |
| Role | Junior PHP / Laravel Programmer |
| Duration | 120 minutes |
| Stack | Laravel 13, PHP 8.3, MySQL/MariaDB, Blade, plain CSS, PHPUnit |
| Domain | Task management for a programming team |
| Intentional defects | 6 (D1–D6) |
| Intentional gaps (incomplete, not "bugs") | 4 (G1–G4) |
| Baseline tests | 15 (all pass on the starter project) |

The candidate receives a working but partially implemented application (~60% of the required behaviour). Every page loads, migrations and seeders run, and the baseline tests pass. The defects are discoverable by reading the code, using the UI with the seed data, or writing simple tests. None require obscure Laravel knowledge.

The candidate is **not** expected to fix everything. A strong junior candidate typically finds 3–5 defects, fixes 2–4 of them with tests, and writes an honest `NOTES.md`.

---

## 2. Preparing the Candidate Package

1. Reset the starter to a clean state (from the original copy you keep for yourself).
2. Copy the project **without** these items:
   - `ASSESSOR_GUIDE.md` (this file)
   - `vendor/`
   - `.env`
   - `storage/logs/*.log`
3. Recommended: initialise Git and make an initial commit so you can diff the candidate's work later.

Example (Git Bash / macOS / Linux), run from the parent folder:

```bash
rsync -a --exclude vendor --exclude .env --exclude ASSESSOR_GUIDE.md \
      --exclude 'storage/logs/*.log' project-test/ task-manager-candidate/
cd task-manager-candidate
git init && git add -A && git commit -m "Starter project"
```

Windows PowerShell alternative:

```powershell
robocopy project-test task-manager-candidate /E /XD vendor /XF .env ASSESSOR_GUIDE.md *.log
cd task-manager-candidate
git init; git add -A; git commit -m "Starter project"
```

4. Before the session, check on the candidate's machine (or the provided machine) that `composer install`, `php artisan migrate --seed` and `php artisan test` work. Setup issues should not consume the candidate's 120 minutes — pause the clock if the environment is broken through no fault of theirs.

> **Seed dates are relative.** The seeder creates due dates relative to the day it runs (e.g. "due today", "due 3 days ago"). Re-run `php artisan migrate:fresh --seed` on the day of the assessment so the overdue examples are correct.

### Exact commands (assessor reference)

```bash
composer install
cp .env.example .env            # PowerShell: Copy-Item .env.example .env
php artisan key:generate
# create databases task_manager and task_manager_testing (see README)
php artisan migrate --seed
php artisan test
php artisan serve               # http://127.0.0.1:8000
```

---

## 3. Defect Summary

| ID | Area | Location | Difficulty | Symptom visible in the UI with seed data? |
| --- | --- | --- | --- | --- |
| D1 | Overdue calculation (model method) | `app/Models/Task.php` → `isOverdue()` (line ~96) | Easy–Medium | Yes — tasks with **no due date** and tasks **due today** show a red "Overdue" badge |
| D2 | Overdue list (query scope) | `app/Models/Task.php` → `scopeOverdue()` (line ~84) | Medium | Yes — tasks due **today** appear on the Overdue page |
| D3 | Combined filters + pagination | `app/Http/Controllers/TaskController.php` → `index()` (lines ~28, ~32) | Medium | Yes — Status=Review + Priority=High also shows non-review tasks; page 2 of a filtered list loses the filter |
| D4 | Incomplete validation | `app/Http/Requests/StoreTaskRequest.php` (line 32), `UpdateTaskRequest.php` (lines 31, 33) | Easy–Medium | Not through the normal UI (selects / date input) — found by reading the code, tests, or editing the HTML |
| D5 | Workflow bypass | `app/Http/Controllers/TaskController.php` → `update()` (line ~103) + `edit.blade.php` status select | Medium–Hard | Yes — edit a Completed task, set status to In Progress, save → accepted |
| D6 | Missing / invalid task ID | `app/Http/Controllers/TaskController.php` → `show()` (lines ~78–80) | Easy | Yes — `/tasks/999` or `/tasks/abc` → 500 error page instead of 404 |

Line numbers refer to the untouched starter project.

**Defect points** (used in rubric criterion 2, section 7): D1 = 2, D2 = 2, D3 = 2, D4 = 2, D5 = 3, D6 = 1.

---

## 4. Defect Details

### D1 — `isOverdue()` treats tasks without a due date (and tasks due today) as overdue

**Location**: `app/Models/Task.php`, method `isOverdue()`.

```php
public function isOverdue(): bool
{
    if ($this->status === 'completed') {
        return false;
    }

    return $this->due_date < now();
}
```

**Problem**

1. **Null due date**: `$this->due_date` is `null`. In PHP 8, comparing `null < $object` converts both sides to booleans (`false < true`) → `true`. Every non-completed task without a due date is reported as overdue.
2. **Date boundary**: `due_date` is cast to a Carbon date at `00:00:00`. Comparing with `now()` (current time) means a task due **today** is overdue as soon as the day starts.

**Expected behaviour**: Overdue only if `due_date` is not null, `due_date` is strictly before today's date, and status is not `completed`.

**How a candidate might discover it**: The task list shows "Overdue" badges on "Research caching strategy", "Clean up unused CSS" and "Investigate slow admin dashboard" (no due date) and on the two tasks due today. Or by reading the method and asking "what if `due_date` is null?".

**Suggested fix**

```php
public function isOverdue(): bool
{
    if ($this->status === 'completed' || $this->due_date === null) {
        return false;
    }

    return $this->due_date->lt(today());
}
```

**Discussion points**: Why does `null < now()` return `true`? Why compare with `today()` and not `now()`? Should the logic live in one place (D1 and D2 duplicate the rule — a candidate who notices this deserves credit).

---

### D2 — Overdue page includes tasks that are due today

**Location**: `app/Models/Task.php`, `scopeOverdue()`.

```php
return $query->where('status', '!=', 'completed')
    ->whereDate('due_date', '<=', today());
```

**Problem**: `<=` includes tasks due today. The requirement says "before the current date". (Null due dates are correctly excluded here because SQL comparisons with `NULL` are never true — worth asking the candidate whether they know why.)

**Expected behaviour**: Only tasks with `due_date < today` and status ≠ completed.

**How to discover**: The seed data contains two non-completed tasks due today ("Prepare sprint demo environment", "Review pull request #142"). They appear on the Overdue page, which states "Today is …".

**Suggested fix**

```php
return $query->where('status', '!=', 'completed')
    ->whereNotNull('due_date')
    ->whereDate('due_date', '<', today());
```

(`whereNotNull` is optional but makes the intent explicit.)

---

### D3 — Combined filters use OR instead of AND; pagination drops the filters

**Location**: `app/Http/Controllers/TaskController.php`, `index()`.

```php
if ($request->filled('priority')) {
    $query->orWhere('priority', $request->input('priority'));
}
...
'tasks' => $query->paginate(10),
```

**Problem**

1. With only one filter the result is correct (`orWhere` as the first condition behaves like `where`), which is why the baseline status-filter test passes. With **both** filters, the SQL becomes `WHERE status = ? OR priority = ?`, returning tasks that match either condition.
2. **Secondary**: `paginate(10)` without `->withQueryString()` generates page links without the filter parameters, so page 2 of a filtered list shows unfiltered tasks. (With 24 seed tasks, the unfiltered list has 3 pages; a filtered list needs > 10 matches to see this — e.g. create a few more Planning tasks, or lower the page size while testing.)

**Expected behaviour**: Filters are combined with AND; pagination links keep the active filters.

**How to discover**: Select Status = Review and Priority = High → tasks with other statuses appear (e.g. "Fix invoice PDF rounding issue", In Progress/High).

**Suggested fix**

```php
if ($request->filled('priority')) {
    $query->where('priority', $request->input('priority'));
}

return view('tasks.index', [
    'tasks' => $query->paginate(10)->withQueryString(),
    ...
]);
```

**Bonus**: validating the filter values (e.g. `Rule::in(array_keys(Task::STATUSES))`) or ignoring unknown values. Using `->when()` is a nice idiomatic touch but not required.

---

### D4 — Incomplete validation for status, priority and due date

**Locations**

| File | Field | Current rule | Problem |
| --- | --- | --- | --- |
| `StoreTaskRequest.php` | `status` | `['required', 'string']` | Any string (e.g. `done`) is stored |
| `UpdateTaskRequest.php` | `priority` | `['required', 'string']` | Any string (e.g. `urgent`) is stored |
| `UpdateTaskRequest.php` | `due_date` | `['nullable', 'string']` | `not-a-date` reaches the model → `InvalidFormatException` → 500 error |

**Expected behaviour**: Invalid status, priority and date values are rejected with a validation error, on both create and update.

**How to discover**: Compare the two request classes side by side (they are inconsistent with each other), change a `<select>` value with browser dev tools, or write a test that posts an invalid value. The UI's `<select>` and `<input type="date">` hide the problem from normal use — this tests whether the candidate understands that **client-side controls are not validation**.

**Suggested fix**

```php
// StoreTaskRequest
'status' => ['required', Rule::in(array_keys(Task::STATUSES))],

// UpdateTaskRequest
'priority' => ['required', Rule::in(array_keys(Task::PRIORITIES))],
'due_date' => ['nullable', 'date'],
```

**Bonus**: removing duplication (one shared rules array / one `TaskRequest`) — acceptable as long as it is small and tested. Debatable rules to discuss: should a new task be allowed to start as `completed`? Should a due date in the past be allowed on create? Accept any reasoned choice that is documented in `NOTES.md`.

---

### D5 — Edit form bypasses the status workflow

**Location**: `TaskController::update()` and the Status select in `resources/views/tasks/edit.blade.php`.

```php
public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
{
    $task->update($request->validated());
    ...
}
```

**Problem**: The dedicated status endpoint (`PATCH /tasks/{task}/status`, `updateStatus()`) correctly calls `$task->canTransitionTo()`, and the baseline test `test_completed_task_cannot_be_reopened_through_status_endpoint` passes — this can give a false sense of security. But the edit form has a Status select with **all** statuses, and `update()` saves whatever valid status is submitted. A Completed task can be moved back to In Progress (or Planning) via Edit, and a Planning task can jump straight to Completed.

**Expected behaviour**: The workflow rules are enforced on every path that changes the status.

**How to discover**: Open a Completed task (e.g. "Set up CI pipeline for the API"), click Edit, set Status to In Progress, save. Or reason: "the status endpoint checks the workflow — does anything else change the status?"

**Suggested fixes** (any is acceptable if justified):

*Option A — enforce the workflow in `update()` (smallest change):*

```php
public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
{
    $newStatus = $request->validated('status');

    if ($newStatus !== $task->status && ! $task->canTransitionTo($newStatus)) {
        return back()->withInput()->withErrors([
            'status' => "A task cannot move from {$task->statusLabel()} to ".Task::STATUSES[$newStatus].'.',
        ]);
    }

    $task->update($request->validated());
    ...
}
```

*Option B — remove `status` from the edit form and `UpdateTaskRequest`*, so status can only change through the status endpoint. Clean, but the candidate must update the view, the request, and check nothing else relies on it.

*Option C — validation rule in `UpdateTaskRequest`* (e.g. a closure rule or `Rule::in([$task->status, ...nextStatuses])`). Good if the candidate understands how to access the route model inside the form request.

**Important edge case**: keeping the **same** status must still be allowed (a candidate who blocks all edits of completed tasks, or breaks saving an unchanged status, introduced a regression). Also: should a completed task's other fields be editable at all? Accept a documented assumption either way.

Note: because the edit form does not display validation errors (gap G1), a candidate who implements Option A/C must also fix the view for the error to be visible — a good sign if they notice.

---

### D6 — Missing or invalid task ID causes a 500 error

**Location**: `TaskController::show()`.

```php
public function show($id): View
{
    $task = Task::with('programmer')->find($id);

    return view('tasks.show', ['task' => $task]);
}
```

**Problem**: `find()` returns `null` for a missing ID; the view then fails with "Attempt to read property "title" on null" → HTTP 500. `/tasks/abc` behaves the same. Note the inconsistency: `edit()`, `update()` and `updateStatus()` use route model binding (`Task $task`) and correctly return 404.

**Expected behaviour**: HTTP 404 Not Found.

**Suggested fix** (either):

```php
public function show(Task $task): View
{
    $task->load('programmer');

    return view('tasks.show', ['task' => $task]);
}
```

or `Task::with('programmer')->findOrFail($id);`

**Bonus**: noticing that the other actions already use route model binding and making `show()` consistent; adding a `whereNumber('task')` route constraint is fine but not required.

---

## 5. Intentional Gaps (incomplete, not counted as defects)

| ID | Gap | What a good candidate does |
| --- | --- | --- |
| G1 | `edit.blade.php` does not display validation errors (create form does). An invalid update silently redirects back with no message. | Add `@error` blocks (or a shared error summary / form partial). |
| G2 | No tests for overdue logic, null due dates, combined filters, invalid IDs, the edit-form workflow, or update validation. This is the "missing test" that lets every defect above go unnoticed. | Adds focused tests, ideally red → green. |
| G3 | Filter query parameters are not validated (`?status=whatever` just shows an empty list). | Validates or ignores unknown values; documents the choice. |
| G4 | Timezone: `config/app.php` uses `UTC`. "Today" for the overdue rule is the server's UTC date, which differs from local time (e.g. WIB = UTC+7) for 7 hours a day. | Mentions it as an assumption / next step. Changing `APP_TIMEZONE`/`timezone` with justification is acceptable. |

Optional feature ideas from the README (programmer filter, overdue count in navigation) are only bonus — they should never outweigh fixing defects.

---

## 6. Tests

### 6.1 Baseline tests (shipped with the project — all pass on the starter)

| File | Test | What it checks |
| --- | --- | --- |
| `tests/Feature/TaskPagesTest.php` | `test_home_page_redirects_to_task_list` | `/` → `/tasks` |
| | `test_task_list_displays_tasks` | List shows title and programmer |
| | `test_task_list_can_be_filtered_by_status` | Single status filter (passes despite D3) |
| | `test_task_detail_page_displays_task` | Detail page for an existing task |
| | `test_create_and_edit_pages_load` | Create and edit forms return 200 |
| | `test_a_task_can_be_created` | Valid create persists and redirects |
| | `test_a_task_requires_a_title` | Missing title is rejected |
| | `test_overdue_page_lists_late_tasks` | Task due 3 days ago appears (passes despite D2) |
| `tests/Feature/TaskStatusTest.php` | `test_status_can_move_to_the_next_step` | Planning → In Progress via status endpoint |
| | `test_status_endpoint_rejects_unknown_status` | `done` rejected by status endpoint |
| | `test_completed_task_cannot_be_reopened_through_status_endpoint` | Workflow enforced on the status endpoint only (D5 is elsewhere) |
| `tests/Unit/TaskTest.php` | `test_task_past_its_due_date_is_overdue` | Due 2 days ago → overdue (passes despite D1) |
| | `test_completed_task_is_not_overdue` | Completed → never overdue |
| | `test_status_and_priority_labels` | Labels |
| | `test_workflow_allows_review_to_completed` | `canTransitionTo()` basics |

Verified result on the starter project: **15 tests, 36 assertions, all passing**.

Note: the tests use MySQL database `task_manager_testing` (set in `phpunit.xml`), because SQLite (`pdo_sqlite`) is not available in every XAMPP installation. If your environment has `pdo_sqlite`, you may switch `phpunit.xml` back to `sqlite` / `:memory:`.

### 6.2 Recommended tests (assessor reference — NOT shipped)

Each of these **fails on the starter project** and **passes after the suggested fixes**. This was verified: 9/9 failed on the starter; 9/9 passed (and 15/15 baseline still passed) with the fixes from section 4 applied.

Use them to check a candidate's submission (copy into `tests/Feature/AssessorTest.php` on a copy of their code and run `php artisan test --filter=AssessorTest`). Do not expect candidates to write these exact tests; compare coverage, not wording.

```php
<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessorTest extends TestCase
{
    use RefreshDatabase;

    // Overdue calculation + null due dates (D1)
    public function test_task_without_due_date_is_never_overdue(): void
    {
        $task = new Task(['status' => 'in_progress', 'due_date' => null]);

        $this->assertFalse($task->isOverdue());
    }

    public function test_task_due_today_is_not_overdue(): void
    {
        $task = new Task(['status' => 'in_progress', 'due_date' => today()->toDateString()]);

        $this->assertFalse($task->isOverdue());
    }

    // Overdue list boundary (D2)
    public function test_overdue_page_excludes_tasks_due_today(): void
    {
        Task::factory()->status('in_progress')->create([
            'title' => 'Due today task',
            'due_date' => today()->toDateString(),
        ]);

        $this->get(route('tasks.overdue'))
            ->assertOk()
            ->assertDontSee('Due today task');
    }

    // Combined filters (D3)
    public function test_status_and_priority_filters_are_combined(): void
    {
        Task::factory()->status('review')->create(['title' => 'Review High', 'priority' => 'high']);
        Task::factory()->status('review')->create(['title' => 'Review Low', 'priority' => 'low']);
        Task::factory()->status('planning')->create(['title' => 'Planning High', 'priority' => 'high']);

        $this->get(route('tasks.index', ['status' => 'review', 'priority' => 'high']))
            ->assertSee('Review High')
            ->assertDontSee('Review Low')
            ->assertDontSee('Planning High');
    }

    public function test_pagination_links_keep_filters(): void
    {
        Task::factory()->count(12)->status('review')->create();

        $this->get(route('tasks.index', ['status' => 'review']))
            ->assertSee('status=review&amp;page=2', false);
    }

    // Invalid status / priority / date (D4)
    public function test_create_rejects_invalid_status(): void
    {
        $this->post(route('tasks.store'), [
            'title' => 'Invalid status',
            'priority' => 'low',
            'status' => 'done',
        ])->assertSessionHasErrors('status');

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_update_rejects_invalid_priority_and_due_date(): void
    {
        $task = Task::factory()->status('planning')->create();

        $this->put(route('tasks.update', $task), [
            'title' => 'Updated',
            'priority' => 'urgent',
            'status' => 'planning',
            'due_date' => 'not-a-date',
        ])->assertSessionHasErrors(['priority', 'due_date']);
    }

    // Completed task behaviour via edit form (D5)
    public function test_completed_task_cannot_be_reopened_through_edit_form(): void
    {
        $task = Task::factory()->completed()->create();

        $this->put(route('tasks.update', $task), [
            'title' => $task->title,
            'priority' => $task->priority,
            'status' => 'in_progress',
        ]);

        $this->assertSame('completed', $task->fresh()->status);
    }

    // Invalid task IDs (D6)
    public function test_missing_or_invalid_task_id_returns_404(): void
    {
        $this->get('/tasks/999999')->assertNotFound();
        $this->get('/tasks/abc')->assertNotFound();
    }
}
```

**Additional recommended cases** (not automated above — check manually or ask the candidate):

- Task creation & update happy paths: updating title/description/programmer/due date of a non-completed task still works after the D5 fix; **keeping the same status** on a completed task is still allowed (regression check for over-eager D5 fixes).
- Clearing the due date on edit stores `NULL`, and the task is then not overdue.
- `programmer_id` that does not exist is rejected (already implemented — a candidate who verifies this deserves credit for checking rather than assuming).
- Completed task whose due date has passed: not overdue on the list badge **and** not on the Overdue page.
- Status endpoint: every allowed transition succeeds, and every disallowed transition (including Planning → Completed) fails.
- Unknown filter values (`?status=foo`) do not cause errors (G3).

---

## 7. Evaluation Rubric

Score each criterion 0–3 (0 = not observed, 1 = weak, 2 = solid, 3 = excellent).

| # | Criterion | What to look for |
| --- | --- | --- |
| 1 | Code reading before changing | Explored routes → controller → model → view before editing; can explain the request flow |
| 2 | Defect discovery | Defect points for each defect correctly identified (fixed or clearly documented): D1=2, D2=2, D3=2, D4=2, D5=3, D6=1 (max 12). Convert: 0–1 → 0, 2–4 → 1, 5–8 → 2, 9+ → 3 |
| 3 | Correctness of fixes | Fixes match the business rules, no regressions, edge cases (null, today, same status) considered |
| 4 | Data integrity thinking | Server-side validation, workflow enforced on all paths, 404 instead of 500 |
| 5 | Testing | Ran the suite; wrote focused tests that fail before / pass after; existing tests still pass |
| 6 | Change discipline | Small, focused changes; no unnecessary rewrites or new architecture; consistent with existing style |
| 7 | Prioritisation | Tackled high-impact issues first (data integrity / crashes before cosmetics or optional features) |
| 8 | Communication | `NOTES.md` is clear and honest: issues, assumptions, unfinished work, next steps |
| 9 | Explanation (debrief) | Can explain every submitted line, including AI-generated code |

**Indicative bands** (max 27):

| Score | Interpretation |
| --- | --- |
| 21–27 | Strong junior — ready to contribute with light supervision |
| 14–20 | Solid junior — good foundation, needs guidance on edge cases/testing |
| 8–13 | Entry level — needs close mentoring |
| < 8 | Not ready for this role yet |

**Red flags**: deleting or weakening existing tests to make them pass; rewriting large parts of the app without reason; claiming fixes that don't work; cannot explain submitted (AI-generated) code; changing business requirements silently.

**Green flags**: reproduces a bug before fixing it; writes a failing test first; notices D1/D2 duplicate the same rule; notices the two Form Requests are inconsistent; documents assumptions (timezone, completed-task editing, past due dates on create).

---

## 8. Suggested Debrief Questions (15–20 minutes)

1. Walk me through what happens when someone opens `/tasks?status=review&priority=high`.
2. Which problem did you fix first and why?
3. Why did `null < now()` evaluate to `true`? (if they found D1)
4. The status endpoint already blocked reopening a completed task. Why wasn't that enough? (D5)
5. Why is a `<select>` in the form not sufficient validation? (D4)
6. What is the difference between `find()`, `findOrFail()` and route model binding? (D6)
7. Show me one test you wrote. How do you know it actually tests the bug?
8. Which part did you use AI for, and how did you verify its output?
9. With another two hours, what would you do next?

---

## 9. Expected Timeline for a Typical Candidate

| Minutes | Activity |
| --- | --- |
| 0–15 | Setup, run app and tests, explore |
| 15–45 | Find obvious UI issues (D6, D1, D3) and fix the easiest |
| 45–90 | Validation (D4), overdue page (D2), workflow (D5), tests |
| 90–120 | More tests, `NOTES.md` |

If a candidate is stuck on environment setup for more than 15 minutes, help them — setup is not what is being assessed.
