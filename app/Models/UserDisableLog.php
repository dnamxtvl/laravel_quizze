<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property mixed|string $user_id
 * @property int|mixed $status
 * @property mixed|string $disabled_by
 */
class UserDisableLog extends Model
{
    use HasFactory;

    protected $table = 'disable_user_logs';

    protected $primaryKey = 'id';

    public function user(): BelongsTo
    {
        return $this->belongsTo(related: User::class);
    }
}
