<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipTransaction extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    public const TYPE_APPLICATION = 'application';
    public const TYPE_RENEWAL = 'renewal';

    protected $fillable = [
        'reference_no',
        'member_id',
        'level_id',
        'type',
        'category_name',
        'level_name',
        'amount_due',
        'validity_months',
        'benefits',
        'status',
        'starts_at',
        'expires_at',
        'created_by',
        'approved_by',
        'approved_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'amount_due' => 'decimal:2',
        'validity_months' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'approved_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function payments()
    {
        return $this->hasMany(
            Payment::class,
            'membership_transaction_id'
        );
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}