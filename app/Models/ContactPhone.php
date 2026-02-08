<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactPhone extends Model
{
    protected $fillable = ['contact_id', 'phone', 'label', 'sort'];

    protected $casts = [
        'sort' => 'integer',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
