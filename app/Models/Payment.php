<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Payment extends Model
{
    use HasUuids, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'transaction_id',
        'area_id',
        'paid_by_id',
        'created_by_id',
        'amount',
        'month',
        'year',
        'paid_at',
        'status',
        'monthly_due_id',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function monthlyDue(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MonthlyDue::class, 'monthly_due_id');
    }
}
