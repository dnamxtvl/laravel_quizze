<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property mixed|string $quizze_id
 * @property int|mixed $status
 * @property int|mixed $code
 */
class Room extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'rooms';
    protected $primaryKey = 'id';
}
