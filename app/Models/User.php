<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'can_delete_invoice',
        'can_delete_contact',
        'can_delete_lead',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'can_delete_invoice' => 'boolean',
            'can_delete_contact' => 'boolean',
            'can_delete_lead' => 'boolean',
        ];
    }

    public const ROLE_ADMIN = 'admin';
    public const ROLE_TEAM = 'team';

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function canDeleteInvoice(): bool
    {
        return $this->isAdmin() || $this->can_delete_invoice;
    }

    public function canDeleteContact(): bool
    {
        return $this->isAdmin() || $this->can_delete_contact;
    }

    public function canDeleteLead(): bool
    {
        return $this->isAdmin() || $this->can_delete_lead;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

}
