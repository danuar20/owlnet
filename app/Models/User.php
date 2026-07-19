<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
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
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'role' => UserRole::class,
        ];
    }

    /**
     * Whether the user has the given role.
     */
    public function hasRole(UserRole|string $role): bool
    {
        $value = $role instanceof UserRole ? $role->value : $role;

        return $this->role?->value === $value;
    }

    /**
     * Whether the user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(UserRole::SUPER_ADMIN);
    }

    /**
     * Whether the user is an admin (or super admin).
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::ADMIN) || $this->isSuperAdmin();
    }

    /**
     * Whether the user is an operator (or above).
     */
    public function isOperator(): bool
    {
        return $this->hasRole(UserRole::OPERATOR) || $this->isAdmin();
    }
}
