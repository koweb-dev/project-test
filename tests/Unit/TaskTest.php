<?php

namespace Tests\Unit;

use App\Models\Task;
use Tests\TestCase;

class TaskTest extends TestCase
{
    public function test_task_past_its_due_date_is_overdue(): void
    {
        $task = new Task([
            'status' => 'in_progress',
            'due_date' => now()->subDays(2)->toDateString(),
        ]);

        $this->assertTrue($task->isOverdue());
    }

    public function test_completed_task_is_not_overdue(): void
    {
        $task = new Task([
            'status' => 'completed',
            'due_date' => now()->subDays(2)->toDateString(),
        ]);

        $this->assertFalse($task->isOverdue());
    }

    public function test_status_and_priority_labels(): void
    {
        $task = new Task(['status' => 'in_progress', 'priority' => 'high']);

        $this->assertSame('In Progress', $task->statusLabel());
        $this->assertSame('High', $task->priorityLabel());
    }

    public function test_workflow_allows_review_to_completed(): void
    {
        $task = new Task(['status' => 'review']);

        $this->assertTrue($task->canTransitionTo('completed'));
        $this->assertFalse($task->canTransitionTo('planning'));
    }
}
