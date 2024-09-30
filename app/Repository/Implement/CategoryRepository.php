<?php

namespace App\Repository\Implement;

use App\Models\Category;
use App\Repository\Interface\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

readonly class CategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(
        private Category $category
    ) {}

    public function listCategory(): Collection
    {
        return $this->category->all();
    }
}
