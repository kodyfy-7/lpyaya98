<?php

namespace App\Models;

use App\Enums\DueStatus;
use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class MonthlyDue extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'area_id',
        'month',
        'year',
        'due_amount',
        'paid_amount',
        'status',
    ];

    protected $casts = [
        'due_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'status' => DueStatus::class,
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'monthly_due_id');
    }

}
