<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property mixed $is_correct
 * @property mixed $question_id
 */
class Answer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'answers';

    protected $primaryKey = 'id';
}
