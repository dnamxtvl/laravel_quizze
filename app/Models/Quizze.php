<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property mixed|string $title
 * @property int|mixed $category_id
 * @property mixed|string $user_id
 * @property mixed $id
 */
class Quizze extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'quizzes';

    protected $primaryKey = 'id';

    public function questions(): HasMany
    {
        return $this->hasMany(related: Question::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(related: Room::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(related: Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(related: User::class);
    }
}
