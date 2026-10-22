@extends('layouts.app')

@section('title', 'Overdue Tasks')

@section('content')
    <div class="page-header">
        <h1>Overdue Tasks</h1>
        <p class="muted">Tasks past their due date that are not completed yet. Today is {{ today()->format('d M Y') }}.</p>
    </div>

    @if ($tasks->isEmpty())
        <p class="empty">No overdue tasks. Nice work!</p>
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
                        <td>{{ $task->due_date->format('d M Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
