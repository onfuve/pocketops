<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadChannel extends Model
{
    protected $fillable = ['name', 'is_referral', 'sort'];

    protected $casts = [
        'is_referral' => 'boolean',
    ];

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'lead_channel_id');
    }
}
