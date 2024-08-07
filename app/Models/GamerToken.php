<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GamerToken extends Model
{
    use HasFactory;

    protected $table = 'gamer_token';
    protected $primaryKey = 'id';
}
