<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property mixed|string $user_id
 * @property int|mixed $type
 * @property mixed|string $title
 * @property mixed|string $content
 * @property mixed|string|null $link
 */
class Notification extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'notifications';

    protected $primaryKey = 'id';

    public function user(): BelongsTo
    {
        return $this->belongsTo(related: User::class);
    }
}
