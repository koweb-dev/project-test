@extends('layouts.app')

@section('title', 'New Task')

@section('content')
    <div class="page-header">
        <a href="{{ route('tasks.index') }}" class="link-muted">&larr; Back to tasks</a>
        <h1>New Task</h1>
    </div>

    <form method="POST" action="{{ route('tasks.store') }}" class="card form">
        @csrf

        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="{{ old('title') }}">
        @error('title') <p class="field-error">{{ $message }}</p> @enderror

        <label for="description">Description</label>
        <textarea id="description" name="description" rows="5">{{ old('description') }}</textarea>
        @error('description') <p class="field-error">{{ $message }}</p> @enderror

        <label for="programmer_id">Assigned Programmer</label>
        <select id="programmer_id" name="programmer_id">
            <option value="">Unassigned</option>
            @foreach ($programmers as $programmer)
                <option value="{{ $programmer->id }}" @selected(old('programmer_id') == $programmer->id)>{{ $programmer->name }}</option>
            @endforeach
        </select>
        @error('programmer_id') <p class="field-error">{{ $message }}</p> @enderror

        <div class="form-row">
            <div>
                <label for="priority">Priority</label>
                <select id="priority" name="priority">
                    @foreach ($priorities as $value => $label)
                        <option value="{{ $value }}" @selected(old('priority', 'medium') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('priority') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', 'planning') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="due_date">Due Date</label>
                <input type="date" id="due_date" name="due_date" value="{{ old('due_date') }}">
                @error('due_date') <p class="field-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="actions">
            <button type="submit" class="button">Create Task</button>
        </div>
    </form>
@endsection
