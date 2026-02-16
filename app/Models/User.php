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

    public const ROLE_ADMIN = 'admin';
    public const ROLE_TEAM = 'team';

    /** Module-based abilities: view, create, edit, delete */
    public const ABILITY_VIEW = 'view';
    public const ABILITY_CREATE = 'create';
    public const ABILITY_EDIT = 'edit';
    public const ABILITY_DELETE = 'delete';

    public const MODULES = [
        'contacts' => 'مخاطبین',
        'leads' => 'سرنخ‌ها',
        'invoices' => 'فاکتورها',
        'products' => 'محصولات',
        'price_lists' => 'لیست قیمت',
        'product_landing_pages' => 'صفحات محصول',
        'forms' => 'فرم‌ها',
        'tasks' => 'وظایف',
        'calendar' => 'تقویم',
        'transactions' => 'تراکنش‌ها',
        'settings' => 'تنظیمات',
        'users' => 'کاربران',
    ];

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
        'permissions',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'can_delete_invoice' => 'boolean',
            'can_delete_contact' => 'boolean',
            'can_delete_lead' => 'boolean',
            'permissions' => 'array',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function canDeleteInvoice(): bool
    {
        return $this->isAdmin() || $this->canModule('invoices', self::ABILITY_DELETE);
    }

    public function canDeleteContact(): bool
    {
        return $this->isAdmin() || $this->canModule('contacts', self::ABILITY_DELETE);
    }

    public function canDeleteLead(): bool
    {
        return $this->isAdmin() || $this->canModule('leads', self::ABILITY_DELETE);
    }

    /**
     * Check if user can perform an ability on a module (for team members).
     * Admin always returns true. Legacy: when permissions not set, team has view/create/edit; delete uses can_delete_*.
     */
    public function canModule(string $module, string $ability): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        if ($this->role !== self::ROLE_TEAM) {
            return false;
        }
        $permissions = $this->permissions ?? [];
        if ($permissions === []) {
            return $this->legacyCanModule($module, $ability);
        }
        $allowed = $permissions[$module] ?? [];
        if (!is_array($allowed)) {
            $allowed = [];
        }
        return in_array($ability, $allowed, true);
    }

    private function legacyCanModule(string $module, string $ability): bool
    {
        if (in_array($ability, [self::ABILITY_VIEW, self::ABILITY_CREATE, self::ABILITY_EDIT], true)) {
            return true;
        }
        if ($ability === self::ABILITY_DELETE) {
            return match ($module) {
                'invoices' => $this->can_delete_invoice,
                'contacts' => $this->can_delete_contact,
                'leads' => $this->can_delete_lead,
                default => false,
            };
        }
        return false;
    }

    /** Get effective permissions for display (admin = all). */
    public function getEffectivePermissions(): array
    {
        if ($this->isAdmin()) {
            $all = [self::ABILITY_VIEW, self::ABILITY_CREATE, self::ABILITY_EDIT, self::ABILITY_DELETE];
            return array_fill_keys(array_keys(self::MODULES), $all);
        }
        $permissions = $this->permissions ?? [];
        if ($permissions === []) {
            $permissions = [];
            foreach (array_keys(self::MODULES) as $mod) {
                $permissions[$mod] = [self::ABILITY_VIEW, self::ABILITY_CREATE, self::ABILITY_EDIT];
                if ($mod === 'invoices' && $this->can_delete_invoice) {
                    $permissions[$mod][] = self::ABILITY_DELETE;
                }
                if ($mod === 'contacts' && $this->can_delete_contact) {
                    $permissions[$mod][] = self::ABILITY_DELETE;
                }
                if ($mod === 'leads' && $this->can_delete_lead) {
                    $permissions[$mod][] = self::ABILITY_DELETE;
                }
            }
        }
        return $permissions;
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
