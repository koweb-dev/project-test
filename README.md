# Task Manager — Junior Laravel Developer Assessment

Welcome, and thank you for taking the time to do this assessment.

**Task Manager** is a small internal application our programming team uses to plan and track work. Another developer started building it but did not finish. You are taking over the codebase.

The application runs, but it is **only partially built** and has **not been fully reviewed or tested**. Your job is to understand the existing code, check it against the business requirements below, and improve it.

> **Time limit: 120 minutes.**
> We do **not** expect you to finish everything. We are interested in how you work: how you investigate, what you choose to do first, how you reason about problems, and how clearly you explain your decisions.

---

## 1. Tech Stack

- PHP 8.3+
- Laravel 13
- MySQL 8 / MariaDB 10.4+
- Blade templates with plain CSS (`public/css/app.css`) — **no npm or frontend build step is needed**
- PHPUnit for automated tests

## 2. Prerequisites

Make sure you have:

- PHP 8.3 or newer with the `pdo_mysql` and `mbstring` extensions
- Composer 2
- A running MySQL or MariaDB server (XAMPP, Laragon, Docker, or a native install are all fine)

Check your versions:

```bash
php -v
composer -V
mysql --version
```

## 3. Installation

Run these commands from the project root.

**1. Install PHP dependencies**

```bash
composer install
```

**2. Create your environment file and application key**

```bash
# macOS / Linux / Git Bash
cp .env.example .env

# Windows PowerShell
Copy-Item .env.example .env

php artisan key:generate
```

**3. Configure the database** — open `.env` and check these values:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_manager
DB_USERNAME=root
DB_PASSWORD=
```

**4. Create two databases** — one for the application and one for the automated tests:

```sql
CREATE DATABASE task_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE task_manager_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

You can run these in phpMyAdmin, or from the command line:

```bash
mysql -u root -p -e "CREATE DATABASE task_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE DATABASE task_manager_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

> The tests use the `task_manager_testing` database (configured in `phpunit.xml`) with the same username and password as your `.env`. The tests wipe that database on every run, so **never point it at real data**.

**5. Run the migrations and seed sample data**

```bash
php artisan migrate --seed
```

To reset the database to the original sample data at any time:

```bash
php artisan migrate:fresh --seed
```

The seeders create 5 programmers and 24 tasks. Task due dates are relative to the day you run the seeder.

## 4. Running the Application

```bash
php artisan serve
```

Open <http://127.0.0.1:8000> in your browser. The home page redirects to the task list.

## 5. Running the Tests

```bash
php artisan test
```

All tests that come with the project should pass before you start. If they don't, check your database setup first.

Run a single file or test:

```bash
php artisan test tests/Feature/TaskPagesTest.php
php artisan test --filter=test_a_task_can_be_created
```

## 6. Project Map

| Area | Location |
| --- | --- |
| Routes | `routes/web.php` |
| Controller | `app/Http/Controllers/TaskController.php` |
| Validation | `app/Http/Requests/` |
| Models | `app/Models/Task.php`, `app/Models/Programmer.php` |
| Migrations | `database/migrations/` |
| Factories & seeders | `database/factories/`, `database/seeders/` |
| Views | `resources/views/layouts/`, `resources/views/tasks/` |
| Styles | `public/css/app.css` |
| Tests | `tests/Feature/`, `tests/Unit/` |

---

## 7. Business Requirements

The application manages programming tasks. Each task has:

| Field | Rules |
| --- | --- |
| Title | Required, max 255 characters |
| Description | Optional |
| Assigned programmer | Optional; must be an existing programmer |
| Priority | One of: **Low**, **Medium**, **High** |
| Status | One of: **Planning**, **In Progress**, **Review**, **Completed** |
| Due date | Optional (may be empty); must be a valid date when given |
| Created / updated timestamps | Managed automatically |

### Required features

1. Display a task list.
2. View task details.
3. Create a task.
4. Edit an existing task.
5. Update a task's status.
6. Display overdue tasks.
7. Filter tasks by status and priority.

### Overdue definition

A task is **overdue** when its due date is **before the current date** and its status is **not Completed**.
A task **without a due date is never overdue**.

### Status workflow

| From | May move to |
| --- | --- |
| Planning | In Progress |
| In Progress | Planning, Review |
| Review | In Progress, Completed |
| Completed | — (final) |

### Business rules

- A **Completed** task must not be moved back to In Progress (or any other status) unless there is an explicitly defined and justified workflow for reopening tasks. No such workflow exists yet.
- Invalid status, priority and date values must never be accepted.
- Task data must remain consistent when it is updated.
- Requests for a task that does not exist, or for an invalid task ID, must be handled appropriately (for example, a "404 Not Found" page) instead of an error.

---

## 8. Your Tasks

Work through these in whatever order you think is most valuable. **Prioritise** — you will probably not have time for everything.

### Task 1 — Get it running and explore (suggested: ~15 minutes)

- Install and run the application and the test suite.
- Read the code. Follow a request from the route to the controller, model, and view.
- Use the application like a real user would.

### Task 2 — Review the application against the requirements

- Compare the actual behaviour with the business requirements in section 7.
- Find problems: bugs, missing validation, incorrect business logic, inconsistent data handling, poor error handling.
- Fix the problems you consider most important. Prefer small, focused changes to the existing code over rewrites.

### Task 3 — Complete unfinished functionality

Some parts of the create, edit, status and filter features are incomplete or do not handle edge cases. Complete what you can so that every requirement in section 7 is met.

### Task 4 — Automated tests

- Write tests that prove the business rules work (overdue logic, status workflow, validation, filters, missing tasks, …).
- A good test for a bug you fixed should fail before your fix and pass after it.
- Keep the existing tests passing.

### Task 5 — Optional improvements (only if you have time left)

For example: filter by assigned programmer, show a count of overdue tasks in the navigation, or anything else you think is valuable. Explain why you chose it.

---

## 9. Deliverables

At the end of the 120 minutes, submit:

1. **Your code** — the project folder or a Git repository. If you use Git, please commit in small steps with clear messages so we can follow your progress.
2. **A file named `NOTES.md`** in the project root containing:
   - **Issues found** — each problem you identified, how you found it, and whether you fixed it.
   - **Changes made** — a short summary of what you changed and why.
   - **Assumptions** — any decisions you made where the requirements were unclear.
   - **Unfinished work and next steps** — what you would do next with more time, in priority order.
   - **How to verify** — commands to run your tests, or steps to check your changes manually.

Unfinished work is completely fine. A clear, honest `NOTES.md` is worth more than a rushed change.

## 10. Rules

- You may use the **Laravel documentation**, PHP documentation, search engines, Stack Overflow, and **AI tools** (ChatGPT, Copilot, Claude, etc.).
- However, **you must be able to explain every line of code you submit**. We will ask you to walk us through your changes and your reasoning afterwards.
- Do not change the business requirements. If you think a requirement is wrong or unclear, write it down in `NOTES.md`.
- Do not delete existing tests to make the test suite pass.
- If something blocks you (for example, the environment will not start), tell us early.

Good luck — we're looking forward to seeing how you approach it!
