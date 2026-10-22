<?php

namespace Database\Seeders;

use App\Models\Programmer;
use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Seed sample tasks. Due dates are relative to the day the seeder runs.
     */
    public function run(): void
    {
        $programmers = Programmer::pluck('id', 'email');

        $tasks = [
            ['Set up CI pipeline for the API', 'Configure automated tests and code style checks on every push.', 'andi@example.com', 'high', 'completed', -12],
            ['Design login page', 'Create a responsive login page that matches the new brand guide.', 'siti@example.com', 'medium', 'completed', -8],
            ['Fix invoice PDF rounding issue', 'Totals on generated invoices are off by 0.01 for some currencies.', 'budi@example.com', 'high', 'in_progress', -5],
            ['Write regression tests for checkout', 'Cover the discount code and shipping fee scenarios.', 'dewi@example.com', 'medium', 'review', -3],
            ['Migrate user avatars to object storage', 'Move existing avatar files and update the upload logic.', 'rizky@example.com', 'low', 'planning', -2],
            ['Update API documentation', 'Document the new pagination parameters for /orders.', 'andi@example.com', 'low', 'in_progress', -1],
            ['Prepare sprint demo environment', 'Deploy the latest build to the demo server and reset data.', 'budi@example.com', 'high', 'in_progress', 0],
            ['Review pull request #142', 'Review the refactor of the notification module.', 'dewi@example.com', 'medium', 'review', 0],
            ['Implement password reset email', 'Send a reset link that expires after 60 minutes.', 'andi@example.com', 'high', 'planning', 1],
            ['Add dark mode toggle', 'Allow users to switch between light and dark themes.', 'siti@example.com', 'low', 'planning', 3],
            ['Optimize product search query', 'Search takes more than 2 seconds when there are many products.', 'rizky@example.com', 'high', 'in_progress', 4],
            ['Create monthly sales report export', 'Export the monthly sales summary as CSV.', 'budi@example.com', 'medium', 'planning', 7],
            ['Refactor order status labels', 'Use one source of truth for order status labels.', 'rizky@example.com', 'low', 'review', 10],
            ['Accessibility audit for checkout', 'Check keyboard navigation and colour contrast.', 'siti@example.com', 'medium', 'planning', 14],
            ['Upgrade payment gateway SDK', 'The current SDK version will be deprecated next quarter.', 'andi@example.com', 'high', 'planning', 21],
            ['Research caching strategy', 'Compare options for caching the product catalogue.', 'rizky@example.com', 'low', 'planning', null],
            ['Clean up unused CSS', 'Remove styles that are no longer referenced.', 'siti@example.com', 'low', 'in_progress', null],
            ['Investigate slow admin dashboard', 'Dashboard sometimes takes 10 seconds to load.', null, 'high', 'planning', null],
            ['Onboarding guide for new developers', 'Write a short guide covering local setup and conventions.', 'dewi@example.com', 'medium', 'completed', null],
            ['Fix broken link in footer', 'The "Privacy Policy" link returns 404.', 'siti@example.com', 'low', 'completed', -20],
            ['Add rate limiting to public API', 'Limit anonymous requests to 60 per minute.', 'budi@example.com', 'high', 'review', 2],
            ['Translate error messages', 'Provide Indonesian translations for validation messages.', null, 'medium', 'planning', 12],
            ['Archive old log files', 'Logs older than 90 days should be compressed and archived.', 'rizky@example.com', 'low', 'completed', 5],
            ['Update staging server PHP version', 'Staging should run the same PHP version as production.', 'andi@example.com', 'medium', 'in_progress', -9],
        ];

        foreach ($tasks as [$title, $description, $email, $priority, $status, $dueInDays]) {
            Task::create([
                'title' => $title,
                'description' => $description,
                'programmer_id' => $email ? $programmers[$email] : null,
                'priority' => $priority,
                'status' => $status,
                'due_date' => $dueInDays === null ? null : today()->addDays($dueInDays)->toDateString(),
            ]);
        }
    }
}
