<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed|string $ip
 * @property int|mixed $user_id
 * @property mixed|string $device
 * @property mixed|string|null $longitude
 * @property mixed|string|null $latitude
 * @property mixed|string|null $country_name
 * @property mixed|string|null $city_name
 * @property int|mixed $type
 */
class UserLoginHistory extends Model
{
    use HasFactory;

    protected $table = 'user_history_login';

    protected $primaryKey = 'id';
}
