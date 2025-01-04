<?php

namespace App\Models;

use App\Notifications\VerifyEmailRegister;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property mixed $email_verified_at
 * @property mixed $id
 * @property mixed $name
 * @property mixed $email
 * @property mixed $type
 * @property mixed $super_admin
 * @property mixed|true $disabled
 * @property Carbon|mixed $disabled_at
 * @property mixed|string $latest_ip_login
 * @property \Illuminate\Support\Carbon|mixed $latest_login
 * @property mixed|string $password
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quizze::class);
    }

    public function sendEmailVerifyNotification(string $verifyCode): void
    {
        $this->notify(new VerifyEmailRegister(verifyCode: $verifyCode));
    }

    public function userLoginHistories(): HasMany
    {
        return $this->hasMany(UserLoginHistory::class);
    }
}
