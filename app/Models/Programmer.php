<?php

namespace App\Models;

use Database\Factories\ProgrammerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'email', 'role'])]
class Programmer extends Model
{
    /** @use HasFactory<ProgrammerFactory> */
    use HasFactory;

    /**
     * Tasks assigned to this programmer.
     *
     * @return HasMany<Task, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
