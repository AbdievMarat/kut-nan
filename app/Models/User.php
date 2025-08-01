<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property int $role
 * @property string|null $remember_token
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
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
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public const ROLE_ADMIN = 1;
    public const ROLE_MANAGER = 2;


    /**
     * @param $role
     * @return bool
     */
    public function hasRole($role): bool
    {
        $roleMap = [
            'admin' => self::ROLE_ADMIN,
            'manager' => self::ROLE_MANAGER,
        ];

        if (is_string($role)) {
            $roleValue = $roleMap[$role];
        }

        return $this->role === $roleValue;
    }

    /**
     * @return array
     */
    public static function getRoles(): array
    {
        return [
            self::ROLE_ADMIN => 'Администратор',
            self::ROLE_MANAGER => 'Менеджер',
        ];
    }
}
