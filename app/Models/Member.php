<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'membership_no',
        'first_name',
        'last_name',
        'email',
        'category_id',
        'level_id',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'membership_starts_at' => 'datetime',
        'membership_expires_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function transactions()
    {
        return $this->hasMany(MembershipTransaction::class);
    }

    public function payments()
    {
        return $this->hasManyThrough(
            Payment::class,
            MembershipTransaction::class,
            'member_id',
            'membership_transaction_id'
        );
    }
}