<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/tasks');

Route::get('/tasks/overdue', [TaskController::class, 'overdue'])->name('tasks.overdue');
Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status.update');
Route::resource('tasks', TaskController::class)->except(['destroy']);
