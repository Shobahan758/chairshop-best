<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'phone', 'avatar_path', 'password', 'is_admin', 'role', 'permissions'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function isSuperAdmin(): bool
    {
        return $this->is_admin && $this->role === 'super_admin';
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'manager' => 'Manager',
            default => 'Customer',
        };
    }

    public function canAccessAdminRoute(string $route): bool
    {
        if (! $this->is_admin) {
            return false;
        }
        if ($this->isSuperAdmin()) {
            return true;
        }
        if (str_starts_with($route, 'admin.settings.users')) {
            return false;
        }
        if (! in_array($this->role, ['admin', 'manager'], true)) {
            return false;
        }
        if (Str::is(['admin', 'admin.settings.profile*', 'admin.settings.password.update'], $route)) {
            return true;
        }
        foreach (config('staff_permissions') as $permission => $details) {
            if (in_array($permission, $this->staffPermissions(), true) && Str::is($details['routes'], $route)) {
                return true;
            }
        }

        return false;
    }

    /** @return list<string> */
    public function staffPermissions(): array
    {
        if ($this->isSuperAdmin()) {
            return array_keys(config('staff_permissions'));
        }
        if (! $this->is_admin || ! in_array($this->role, ['admin', 'manager'], true)) {
            return [];
        }

        return $this->permissions ?? ($this->role === 'admin'
            ? array_keys(config('staff_permissions'))
            : ['dashboard', 'catalog', 'products', 'orders', 'incomplete_orders', 'fake_orders']);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'permissions' => 'array',
        ];
    }
}
