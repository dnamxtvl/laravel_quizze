<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $room_id
 * @property mixed $previous_question_id
 * @property mixed $current_question_id
 * @property int|mixed $status
 */
class RoomChangeLog extends Model
{
    use HasFactory;

    protected $table = 'room_change_logs';

    protected $primaryKey = 'id';
}
