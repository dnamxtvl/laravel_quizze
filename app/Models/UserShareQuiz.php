<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * @property mixed|string $user_share_id
 * @property mixed|string $receiver_id
 * @property mixed|string $token
 * @property mixed|true $is_accept
 * @property mixed|string $quizze_id
 * @property Carbon|mixed $accepted_at
 */
class UserShareQuiz extends Model
{
    use HasFactory;

    protected $table = 'user_share_quizzes';

    protected $primaryKey = 'id';

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(related: Quizze::class, foreignKey: 'quizze_id', ownerKey: 'id');
    }

    public function userShare(): BelongsTo
    {
        return $this->belongsTo(related: User::class, foreignKey: 'user_share_id', ownerKey: 'id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(related: User::class, foreignKey: 'receiver_id', ownerKey: 'id');
    }
}
