<?php

namespace App\Models;

use App\Enums\EmploymentStatus;
use Illuminate\Database\Eloquent\Builder;
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
        'department_id',
        'manager_id',
        'employee_code',
        'job_title',
        'basic_salary',
        'employment_status',
        'joined_at',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'employment_status' => EmploymentStatus::class,
        'joined_at' => 'date',
    ];

    // --- RELATIONSHIPS ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function directReports(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function bonuses(): HasMany
    {
        return $this->hasMany(Bonus::class);
    }

    // --- LOCAL ELOQUENT SCOPES ---

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('employment_status', EmploymentStatus::ACTIVE);
    }

    public function scopeInDepartment(Builder $query, string $departmentId): Builder
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeManagedBy(Builder $query, string $managerId): Builder
    {
        return $query->where('manager_id', $managerId);
    }
}