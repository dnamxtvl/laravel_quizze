<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property mixed|string $longitude
 * @property mixed|string $latitude
 * @property mixed|string $ip_address
 * @property mixed|string $country_name
 * @property mixed|string $city_name
 * @property mixed|string $user_agent
 * @property mixed $id
 * @property false|mixed $display_meme
 * @property mixed $token
 * @property mixed $gamerToken
 * @property mixed|string $name
 */
class Gamer extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'gamers';

    protected $primaryKey = 'id';

    public function gamerToken(): HasOne
    {
        return $this->hasOne(related: GamerToken::class);
    }

    public function gamerAnswers(): HasMany
    {
        return $this->hasMany(related: GamerAnswer::class);
    }
}
