<?php

namespace App\Models;

use App\Enums\BonusStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonusDistribution extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'bonus_id',
        'source_employee_id',
        'beneficiary_employee_id',
        'upline_level',
        'percentage',
        'amount',
        'status',
    ];

    protected $casts = [
        'upline_level' => 'integer',
        'percentage' => 'decimal:2',
        'amount' => 'decimal:2',
        'status' => BonusStatus::class,
    ];

    public function bonus(): BelongsTo
    {
        return $this->belongsTo(Bonus::class);
    }

    public function sourceEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'source_employee_id');
    }

    public function beneficiaryEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'beneficiary_employee_id');
    }
}