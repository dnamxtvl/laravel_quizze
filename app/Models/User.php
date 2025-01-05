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
use Laravel\Scout\Searchable;
use JeroenG\Explorer\Application\Explored;

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
 * @property mixed|string|null $google_id
 * @property mixed $created_at
 */
class User extends Authenticatable implements MustVerifyEmail, Explored
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable, Searchable;

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

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at,
        ];
    }

    public function mappableAs(): array
    {
        return [
            'id' => 'keyword',
            'name' => 'text',
            'email' => 'text',
            'created_at' => 'date',
        ];
    }
}
