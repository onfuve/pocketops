<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Auth;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'color',
        'user_id',
    ];

    /** Tags visible to current user: admin sees all, team sees own + shared (null), guest sees shared only. */
    public function scopeForCurrentUser(Builder $query): Builder
    {
        $user = Auth::user();
        if ($user === null) {
            return $query->whereNull('user_id');
        }
        if ($user->isAdmin()) {
            return $query;
        }
        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhereNull('user_id');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leads(): MorphToMany
    {
        return $this->morphedByMany(Lead::class, 'taggable');
    }

    public function contacts(): MorphToMany
    {
        return $this->morphedByMany(Contact::class, 'taggable');
    }

    public function invoices(): MorphToMany
    {
        return $this->morphedByMany(Invoice::class, 'taggable');
    }

    public function reminders(): MorphToMany
    {
        return $this->morphedByMany(Reminder::class, 'taggable');
    }
}
