<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;

/**
 * @property mixed|string $quizze_id
 * @property int|mixed $status
 * @property int|mixed $code
 * @property mixed $created_at
 * @property mixed $id
 * @property mixed $quizze
 * @property Carbon|mixed $started_at
 * @property mixed $current_question_id
 * @property Carbon|mixed $current_question_end_at
 * @property mixed $gamerTokens
 * @property \Carbon\Carbon|mixed $current_question_start_at
 * @property \Carbon\Carbon|mixed|null $start_at
 * @property \Carbon\Carbon|mixed|null $end_at
 * @property mixed $gamers
 * @property \Illuminate\Support\Carbon|mixed $ended_at
 */
class Room extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'rooms';

    protected $primaryKey = 'id';

    public function quizze(): BelongsTo
    {
        return $this->belongsTo(related: Quizze::class);
    }

    public function gamerTokens(): HasMany
    {
        return $this->hasMany(related: GamerToken::class);
    }

    public function gamers(): HasManyThrough
    {
        return $this->hasManyThrough(
            Gamer::class,
            GamerToken::class,
            'room_id',
            'id',
            'id',
            'gamer_id'
        );
    }
}
