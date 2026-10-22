<?php

namespace Database\Factories;

use App\Models\Programmer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Programmer>
 */
class ProgrammerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'role' => fake()->randomElement(['Backend Developer', 'Frontend Developer', 'Full Stack Developer', 'QA Engineer']),
        ];
    }
}
