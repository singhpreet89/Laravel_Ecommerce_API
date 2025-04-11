<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $verified
 * @property string|null $verification_token
 * @property string $admin
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    public const VERIFIED_USER = '1';
    public const UNVERIFIED_USER = '0';

    public const ADMIN_USER = 'true';
    public const REGULAR_USER = 'false';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'verified',
        'verification_token',
        'admin',
    ];

    /**
     * Attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_token',
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
        ];
    }

    public function isVerified(): bool
    {
        return $this->verified === self::VERIFIED_USER;
    }

    public function isAdmin(): bool
    {
        return $this->admin === self::ADMIN_USER;
    }

    /**
     * Generate a secure verification token.
     */
    public static function generateVerificationCode(): string
    {
        return bin2hex(random_bytes(20));
    }

    /**
     * Mutator: Set lowercase email.
     */
    public function setEmailAttribute(string $value): void
    {
        $this->attributes['email'] = Str::lower($value);
    }

    /**
     * Accessor: Return formatted name.
     */
    public function getNameAttribute(string $value): string
    {
        return ucwords($value);
    }
}