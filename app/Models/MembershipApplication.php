<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipApplication extends Model
{
    protected $fillable = [
        'category_id',
        'level_id',
        'reference_no',
        'full_name',
        'email',
        'phone',
        'address',
        'category_name',
        'level_name',
        'amount_due',
        'validity_months',
        'benefits',
        'latitude',
        'longitude',
        'status',
    ];

    protected $casts = [
        'amount_due' => 'decimal:2',
        'validity_months' => 'integer',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'processed_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function member()
    {
    return $this->belongsTo(Member::class);
    }

    public function transaction()
    {
    return $this->belongsTo(
        MembershipTransaction::class,
        'membership_transaction_id'
    );
    }

    public function processedBy()
    {
    return $this->belongsTo(User::class, 'processed_by');
    }
}