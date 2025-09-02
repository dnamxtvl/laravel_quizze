<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $room_id
 * @property mixed $previous_question_id
 * @property mixed $current_question_id
 * @property int|mixed $status
 * @property mixed $id
 * @property int|mixed|string|null $updated_by
 * @property mixed|string|null $old_question_id
 * @property mixed|string|null $new_question_id
 * @property mixed|string $quizze_id
 */
class UpdateQuizzeHistory extends Model
{
    use HasFactory;

    protected $table = 'update_quizze_histories';

    protected $primaryKey = 'id';
}
