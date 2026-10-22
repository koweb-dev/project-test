<?php

namespace Tests\Feature;

use App\Models\Programmer;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_redirects_to_task_list(): void
    {
        $this->get('/')->assertRedirect('/tasks');
    }

    public function test_task_list_displays_tasks(): void
    {
        $task = Task::factory()->create(['title' => 'Write release notes']);

        $this->get(route('tasks.index'))
            ->assertOk()
            ->assertSee('Write release notes')
            ->assertSee($task->programmer->name);
    }

    public function test_task_list_can_be_filtered_by_status(): void
    {
        Task::factory()->status('review')->create(['title' => 'Task in review']);
        Task::factory()->status('planning')->create(['title' => 'Task in planning']);

        $this->get(route('tasks.index', ['status' => 'review']))
            ->assertOk()
            ->assertSee('Task in review')
            ->assertDontSee('Task in planning');
    }

    public function test_task_detail_page_displays_task(): void
    {
        $task = Task::factory()->create(['title' => 'Fix login bug']);

        $this->get(route('tasks.show', $task))
            ->assertOk()
            ->assertSee('Fix login bug');
    }

    public function test_create_and_edit_pages_load(): void
    {
        $task = Task::factory()->create();

        $this->get(route('tasks.create'))->assertOk();
        $this->get(route('tasks.edit', $task))->assertOk();
    }

    public function test_a_task_can_be_created(): void
    {
        $programmer = Programmer::factory()->create();

        $response = $this->post(route('tasks.store'), [
            'title' => 'Build export feature',
            'description' => 'Export tasks to CSV.',
            'programmer_id' => $programmer->id,
            'priority' => 'high',
            'status' => 'planning',
            'due_date' => now()->addWeek()->toDateString(),
        ]);

        $task = Task::firstWhere('title', 'Build export feature');

        $this->assertNotNull($task);
        $response->assertRedirect(route('tasks.show', $task));
        $this->assertSame($programmer->id, $task->programmer_id);
    }

    public function test_a_task_requires_a_title(): void
    {
        $this->post(route('tasks.store'), [
            'title' => '',
            'priority' => 'low',
            'status' => 'planning',
        ])->assertSessionHasErrors('title');

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_overdue_page_lists_late_tasks(): void
    {
        Task::factory()->status('in_progress')->create([
            'title' => 'Late task',
            'due_date' => now()->subDays(3)->toDateString(),
        ]);

        $this->get(route('tasks.overdue'))
            ->assertOk()
            ->assertSee('Late task');
    }
}
