@extends('layouts.app')

@section('title', 'All Tasks')

@section('content')
    <div class="page-header">
        <h1>All Tasks</h1>
    </div>

    <form method="GET" action="{{ route('tasks.index') }}" class="filters">
        <label>
            Status
            <select name="status">
                <option value="">All statuses</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label>
            Priority
            <select name="priority">
                <option value="">All priorities</option>
                @foreach ($priorities as $value => $label)
                    <option value="{{ $value }}" @selected(request('priority') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <button type="submit" class="button">Filter</button>
        <a href="{{ route('tasks.index') }}" class="link-muted">Reset</a>
    </form>

    @if ($tasks->isEmpty())
        <p class="empty">No tasks found.</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Assigned To</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Due Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tasks as $task)
                    <tr>
                        <td><a href="{{ route('tasks.show', $task) }}">{{ $task->title }}</a></td>
                        <td>{{ $task->programmer?->name ?? 'Unassigned' }}</td>
                        <td><span class="badge priority-{{ $task->priority }}">{{ $task->priorityLabel() }}</span></td>
                        <td><span class="badge status-{{ $task->status }}">{{ $task->statusLabel() }}</span></td>
                        <td>
                            {{ $task->due_date?->format('d M Y') ?? '—' }}
                            @if ($task->isOverdue())
                                <span class="badge badge-overdue">Overdue</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="pagination-wrapper">
            {{ $tasks->links('pagination::bootstrap-4') }}
        </div>
    @endif
@endsection
