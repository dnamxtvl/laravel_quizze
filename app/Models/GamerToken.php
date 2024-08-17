<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property mixed|string $gamer_id
 * @property mixed|string $token
 * @property mixed|string $room_id
 * @property mixed|string $expired_at
 */
class GamerToken extends Model
{
    use HasFactory;

    protected $table = 'gamer_token';
    protected $primaryKey = 'id';

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    public function gamer(): BelongsTo
    {
        return $this->belongsTo(related: Gamer::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(related: Room::class);
    }
}
