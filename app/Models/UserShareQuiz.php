<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property mixed|string $user_share_id
 * @property mixed|string $quiz_id
 * @property mixed|string $receiver_id
 * @property mixed|string $token
 */
class UserShareQuiz extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'user_share_quizzes';

    protected $primaryKey = 'id';

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(related: Quizze::class);
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
