<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizzeSetting extends Model
{
    use HasFactory;

    protected $table = 'quizze_settings';
    protected $primaryKey = 'id';

    public function quizze(): BelongsTo
    {
        return $this->belongsTo(related: Quizze::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(related: User::class, foreignKey: 'last_updated_by');
    }
}
