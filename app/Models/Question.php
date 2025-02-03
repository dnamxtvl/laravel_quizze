<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property mixed $id
 * @property false|mixed $is_old_question
 * @property int|mixed $index_question
 * @property mixed $quizze_id
 * @property mixed|string|null $title
 * @property mixed|string|null $image
 * @property mixed|string|null $content_html
 * @property int|mixed $type
 * @property int|mixed $time_reply
 * @property bool|mixed $created_by_sys
 * @property bool|mixed $updated_by_sys
 */
class Question extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'questions';

    protected $primaryKey = 'id';

    public function answers(): HasMany
    {
        return $this->hasMany(related: Answer::class);
    }

    public function quizze(): BelongsTo
    {
        return $this->belongsTo(related: Quizze::class);
    }
}
