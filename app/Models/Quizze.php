<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quizze extends Model
{
    use HasFactory, HasUuids;

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
}
