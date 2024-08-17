<?php

namespace App\Repository\Implement;

use App\DTOs\User\CreateGamerTokenDTO;
use App\Models\GamerToken;
use App\Pipeline\Room\GamerIdFiler;
use App\Pipeline\Room\TokenFilter;
use App\Repository\Interface\GamerTokenRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pipeline\Pipeline;

readonly class GamerTokenRepository implements GamerTokenRepositoryInterface
{
    public function __construct(
        private GamerToken $gamerToken
    ) {
    }

    public function getQuery(array $columnSelects = [], array $filters = []): Builder
    {
        $query = $this->gamerToken->query();
        if (count($columnSelects)) {
            $query->select($columnSelects);
        }

        return app(abstract: Pipeline::class)
            ->send($query)
            ->through([
                new GamerIdFiler(filters: $filters),
                new TokenFilter(filters: $filters),
            ])
            ->thenReturn();
    }
    public function createGamerToken(CreateGamerTokenDTO $gamerTokenDTO): Model
    {
        $gamerToken = new GamerToken();
        $gamerToken->gamer_id = $gamerTokenDTO->getGamerId();
        $gamerToken->token = $gamerTokenDTO->getToken();
        $gamerToken->room_id = $gamerTokenDTO->getRoomId();
        $gamerToken->expired_at = $gamerTokenDTO->getExpiredAt();
        $gamerToken->save();

        return $gamerToken;
    }
}
