<?php

namespace Database\Seeders;

use App\Models\Programmer;
use Illuminate\Database\Seeder;

class ProgrammerSeeder extends Seeder
{
    /**
     * Seed the programmers of the team.
     */
    public function run(): void
    {
        $programmers = [
            ['name' => 'Andi Pratama', 'email' => 'andi@example.com', 'role' => 'Backend Developer'],
            ['name' => 'Siti Rahmawati', 'email' => 'siti@example.com', 'role' => 'Frontend Developer'],
            ['name' => 'Budi Santoso', 'email' => 'budi@example.com', 'role' => 'Full Stack Developer'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@example.com', 'role' => 'QA Engineer'],
            ['name' => 'Rizky Hidayat', 'email' => 'rizky@example.com', 'role' => 'Backend Developer'],
        ];

        foreach ($programmers as $programmer) {
            Programmer::updateOrCreate(['email' => $programmer['email']], $programmer);
        }
    }
}
