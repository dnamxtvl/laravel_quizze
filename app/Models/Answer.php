<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property mixed $is_correct
 * @property mixed $question_id
 * @property mixed $question
 */
class Answer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'answers';

    protected $primaryKey = 'id';

    public function gamerAnswers()
    {
        return $this->hasMany(GamerAnswer::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
