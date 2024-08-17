<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $is_correct
 * @property mixed $question_id
 */
class Answer extends Model
{
    use HasFactory;

    protected $table = 'answers';
    protected $primaryKey = 'id';
}
