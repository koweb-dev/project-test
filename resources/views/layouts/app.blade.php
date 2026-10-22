<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Tasks') · Task Manager</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="topbar">
        <div class="container topbar-inner">
            <a href="{{ route('tasks.index') }}" class="brand">Task Manager</a>
            <nav>
                <a href="{{ route('tasks.index') }}" @class(['active' => request()->routeIs('tasks.index')])>All Tasks</a>
                <a href="{{ route('tasks.overdue') }}" @class(['active' => request()->routeIs('tasks.overdue')])>Overdue</a>
                <a href="{{ route('tasks.create') }}" class="button button-small">+ New Task</a>
            </nav>
        </div>
    </header>

    <main class="container">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @yield('content')
    </main>
</body>
</html>
