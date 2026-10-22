<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Programmer;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Display the task list, optionally filtered by status and priority.
     */
    public function index(Request $request): View
    {
        $query = Task::with('programmer')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('priority')) {
            $query->orWhere('priority', $request->input('priority'));
        }

        return view('tasks.index', [
            'tasks' => $query->paginate(10),
            'statuses' => Task::STATUSES,
            'priorities' => Task::PRIORITIES,
        ]);
    }

    /**
     * Display tasks that are past their due date.
     */
    public function overdue(): View
    {
        $tasks = Task::with('programmer')
            ->overdue()
            ->orderBy('due_date')
            ->get();

        return view('tasks.overdue', ['tasks' => $tasks]);
    }

    /**
     * Show the form for creating a new task.
     */
    public function create(): View
    {
        return view('tasks.create', [
            'programmers' => Programmer::orderBy('name')->get(),
            'statuses' => Task::STATUSES,
            'priorities' => Task::PRIORITIES,
        ]);
    }

    /**
     * Store a newly created task.
     */
    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $task = Task::create($request->validated());

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Task created successfully.');
    }

    /**
     * Display a single task.
     */
    public function show($id): View
    {
        $task = Task::with('programmer')->find($id);

        return view('tasks.show', ['task' => $task]);
    }

    /**
     * Show the form for editing a task.
     */
    public function edit(Task $task): View
    {
        return view('tasks.edit', [
            'task' => $task,
            'programmers' => Programmer::orderBy('name')->get(),
            'statuses' => Task::STATUSES,
            'priorities' => Task::PRIORITIES,
        ]);
    }

    /**
     * Update a task.
     */
    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $task->update($request->validated());

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Task updated successfully.');
    }

    /**
     * Move a task to another status following the workflow.
     */
    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Task::STATUSES))],
        ]);

        if (! $task->canTransitionTo($validated['status'])) {
            return back()->withErrors([
                'status' => "A task cannot move from {$task->statusLabel()} to ".Task::STATUSES[$validated['status']].'.',
            ]);
        }

        $task->update(['status' => $validated['status']]);

        return back()->with('success', 'Status updated to '.$task->statusLabel().'.');
    }
}
