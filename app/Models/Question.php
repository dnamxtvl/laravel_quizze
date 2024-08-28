<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property mixed $id
 */
class Question extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'questions';

    protected $primaryKey = 'id';

    public function answers(): HasMany
    {
        return $this->hasMany(related: Answer::class);
    }
}
