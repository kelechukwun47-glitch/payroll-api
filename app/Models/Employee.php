<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'employee_code',
        'job_title',
        'basic_salary',
        'joined_at',
        'department_id',
        'manager_id',
    ];

    // User relationship
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Department relationship
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // Reporting Manager
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    // Direct Subordinates
    public function directReports(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }
}