<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class FormLink extends Model
{
    protected $fillable = [
        'form_id',
        'code',
        'contact_id',
        'lead_id',
        'task_id',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function submission(): HasOne
    {
        return $this->hasOne(FormSubmission::class);
    }

    public static function generateCode(): string
    {
        do {
            $code = Str::lower(Str::random(12));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function getPublicUrlAttribute(): string
    {
        return url('/f/' . $this->code);
    }
}
