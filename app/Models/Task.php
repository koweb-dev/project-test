<?php

namespace App\Models;

use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['title', 'description', 'programmer_id', 'priority', 'status', 'due_date'])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    /**
     * Available statuses (database value => label).
     *
     * @var array<string, string>
     */
    public const STATUSES = [
        'planning' => 'Planning',
        'in_progress' => 'In Progress',
        'review' => 'Review',
        'completed' => 'Completed',
    ];

    /**
     * Available priorities (database value => label).
     *
     * @var array<string, string>
     */
    public const PRIORITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
    ];

    /**
     * Allowed status workflow (current status => statuses it may move to).
     *
     * @var array<string, array<int, string>>
     */
    public const STATUS_TRANSITIONS = [
        'planning' => ['in_progress'],
        'in_progress' => ['planning', 'review'],
        'review' => ['in_progress', 'completed'],
        'completed' => [],
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }

    /**
     * The programmer assigned to this task.
     *
     * @return BelongsTo<Programmer, $this>
     */
    public function programmer(): BelongsTo
    {
        return $this->belongsTo(Programmer::class);
    }

    /**
     * Only tasks that are past their due date and not yet completed.
     *
     * @param  Builder<Task>  $query
     * @return Builder<Task>
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', '!=', 'completed')
            ->whereDate('due_date', '<=', today());
    }

    /**
     * Determine whether the task is overdue.
     */
    public function isOverdue(): bool
    {
        if ($this->status === 'completed') {
            return false;
        }

        return $this->due_date < now();
    }

    /**
     * Determine whether the task may move to the given status.
     */
    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::STATUS_TRANSITIONS[$this->status] ?? [], true);
    }

    /**
     * Statuses this task may move to next (database value => label).
     *
     * @return array<string, string>
     */
    public function nextStatuses(): array
    {
        return array_intersect_key(self::STATUSES, array_flip(self::STATUS_TRANSITIONS[$this->status] ?? []));
    }

    /**
     * Human readable status label.
     */
    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Human readable priority label.
     */
    public function priorityLabel(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }
}
