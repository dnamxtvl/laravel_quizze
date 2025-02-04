<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property mixed|string $code
 * @property mixed|string $user_id
 * @property Carbon|mixed $expired_at
 * @property mixed|string $type
 * @property mixed $user
 * @property mixed|string $token
 * @property mixed $id
 * @property mixed|string $ip
 * @property mixed|string $user_agent
 * @property mixed|string $old_password
 * @property mixed|string $new_password
 * @property mixed|string $change_by
 */
class UserChangePasswordLog extends Model
{
    use HasFactory;

    protected $table = 'user_change_password_logs';

    protected $primaryKey = 'id';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
