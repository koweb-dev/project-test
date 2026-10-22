@extends('layouts.app')

@section('title', 'Edit Task')

@section('content')
    <div class="page-header">
        <a href="{{ route('tasks.show', $task) }}" class="link-muted">&larr; Back to task</a>
        <h1>Edit Task</h1>
    </div>

    <form method="POST" action="{{ route('tasks.update', $task) }}" class="card form">
        @csrf
        @method('PUT')

        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="{{ old('title', $task->title) }}">

        <label for="description">Description</label>
        <textarea id="description" name="description" rows="5">{{ old('description', $task->description) }}</textarea>

        <label for="programmer_id">Assigned Programmer</label>
        <select id="programmer_id" name="programmer_id">
            <option value="">Unassigned</option>
            @foreach ($programmers as $programmer)
                <option value="{{ $programmer->id }}" @selected(old('programmer_id', $task->programmer_id) == $programmer->id)>{{ $programmer->name }}</option>
            @endforeach
        </select>

        <div class="form-row">
            <div>
                <label for="priority">Priority</label>
                <select id="priority" name="priority">
                    @foreach ($priorities as $value => $label)
                        <option value="{{ $value }}" @selected(old('priority', $task->priority) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $task->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="due_date">Due Date</label>
                <input type="date" id="due_date" name="due_date" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}">
            </div>
        </div>

        <div class="actions">
            <button type="submit" class="button">Save Changes</button>
            <a href="{{ route('tasks.show', $task) }}" class="link-muted">Cancel</a>
        </div>
    </form>
@endsection
