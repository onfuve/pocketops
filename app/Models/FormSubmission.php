<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class FormSubmission extends Model
{
    protected $fillable = [
        'form_id',
        'form_link_id',
        'identifier',
        'first_accessed_at',
        'submitted_at',
        'last_activity_at',
        'data',
        'contact_id',
        'lead_id',
        'task_id',
    ];

    protected $casts = [
        'data' => 'array',
        'first_accessed_at' => 'datetime',
        'submitted_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function formLink(): BelongsTo
    {
        return $this->belongsTo(FormLink::class);
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

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function servqualMicroResponses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ServqualMicroResponse::class);
    }

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    /** Check if edit period has expired (no more access for customer). */
    public function isEditPeriodExpired(): bool
    {
        if (!$this->submitted_at) {
            return false;
        }
        $form = $this->form;
        $minutes = $form ? $form->edit_period_minutes : 15;
        if ($minutes <= 0) {
            return true;
        }
        return $this->submitted_at->addMinutes($minutes)->isPast();
    }

    public function touchActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }
}
