<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed|string $gamer_id
 * @property int|mixed $answer_id
 * @property int|mixed $answer_in_time
 * @property int|mixed $score
 * @property mixed|string $room_id
 */
class GamerAnswer extends Model
{
    use HasFactory;

    protected $table = 'gamer_answers';
    protected $primaryKey = 'id';
}
