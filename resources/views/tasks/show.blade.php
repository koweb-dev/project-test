@extends('layouts.app')

@section('title', $task->title)

@section('content')
    <div class="page-header">
        <a href="{{ route('tasks.index') }}" class="link-muted">&larr; Back to tasks</a>
        <h1>{{ $task->title }}</h1>
    </div>

    <div class="card">
        <dl class="details">
            <dt>Assigned To</dt>
            <dd>{{ $task->programmer?->name ?? 'Unassigned' }}</dd>

            <dt>Priority</dt>
            <dd><span class="badge priority-{{ $task->priority }}">{{ $task->priorityLabel() }}</span></dd>

            <dt>Status</dt>
            <dd><span class="badge status-{{ $task->status }}">{{ $task->statusLabel() }}</span></dd>

            <dt>Due Date</dt>
            <dd>
                {{ $task->due_date?->format('d M Y') ?? 'No due date' }}
                @if ($task->isOverdue())
                    <span class="badge badge-overdue">Overdue</span>
                @endif
            </dd>

            <dt>Description</dt>
            <dd class="description">{{ $task->description ?: '—' }}</dd>

            <dt>Created</dt>
            <dd>{{ $task->created_at->format('d M Y H:i') }}</dd>

            <dt>Last Updated</dt>
            <dd>{{ $task->updated_at->format('d M Y H:i') }}</dd>
        </dl>

        <div class="actions">
            <a href="{{ route('tasks.edit', $task) }}" class="button">Edit Task</a>
        </div>
    </div>

    <div class="card">
        <h2>Update Status</h2>

        @error('status')
            <div class="alert alert-error">{{ $message }}</div>
        @enderror

        @if (count($task->nextStatuses()) > 0)
            <form method="POST" action="{{ route('tasks.status.update', $task) }}" class="inline-form">
                @csrf
                @method('PATCH')
                <select name="status">
                    @foreach ($task->nextStatuses() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="button">Move</button>
            </form>
        @else
            <p class="muted">This task is completed. No further status changes are available.</p>
        @endif
    </div>
@endsection
