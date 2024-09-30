<?php

namespace App\Repository\Interface;

use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    public function listCategory(): Collection;
}
