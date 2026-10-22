<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_can_move_to_the_next_step(): void
    {
        $task = Task::factory()->status('planning')->create();

        $this->patch(route('tasks.status.update', $task), ['status' => 'in_progress'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('in_progress', $task->fresh()->status);
    }

    public function test_status_endpoint_rejects_unknown_status(): void
    {
        $task = Task::factory()->status('planning')->create();

        $this->patch(route('tasks.status.update', $task), ['status' => 'done'])
            ->assertSessionHasErrors('status');

        $this->assertSame('planning', $task->fresh()->status);
    }

    public function test_completed_task_cannot_be_reopened_through_status_endpoint(): void
    {
        $task = Task::factory()->completed()->create();

        $this->patch(route('tasks.status.update', $task), ['status' => 'in_progress'])
            ->assertSessionHasErrors('status');

        $this->assertSame('completed', $task->fresh()->status);
    }
}
