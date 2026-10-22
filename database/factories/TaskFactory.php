<?php

namespace Database\Factories;

use App\Models\Programmer;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'programmer_id' => Programmer::factory(),
            'priority' => fake()->randomElement(array_keys(Task::PRIORITIES)),
            'status' => 'planning',
            'due_date' => now()->addDays(fake()->numberBetween(1, 30))->toDateString(),
        ];
    }

    /**
     * Task with the given status.
     */
    public function status(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }

    /**
     * Completed task.
     */
    public function completed(): static
    {
        return $this->status('completed');
    }

    /**
     * Task without an assigned programmer.
     */
    public function unassigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'programmer_id' => null,
        ]);
    }
}
