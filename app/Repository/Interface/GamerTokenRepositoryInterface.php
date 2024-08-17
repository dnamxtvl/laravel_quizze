<?php

namespace App\Repository\Interface;

use App\DTOs\User\CreateGamerTokenDTO;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface GamerTokenRepositoryInterface
{
    public function createGamerToken(CreateGamerTokenDTO $gamerTokenDTO): Model;

    public function getQuery(array $columnSelects = [], array $filters = []): Builder;
}
