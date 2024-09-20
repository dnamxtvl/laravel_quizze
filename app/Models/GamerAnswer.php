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
 * @property mixed|string $question_id
 */
class GamerAnswer extends Model
{
    use HasFactory;

    protected $table = 'gamer_answers';

    protected $primaryKey = 'id';

    protected $fillable = [
        'gamer_id',
        'answer_id',
        'answer_in_time',
        'score',
        'room_id',
        'question_id',
        'type',
    ];
}
