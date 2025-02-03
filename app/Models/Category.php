<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $table = 'category';

    protected $primaryKey = 'id';

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quizze::class, 'category_id', 'id');
    }
}
