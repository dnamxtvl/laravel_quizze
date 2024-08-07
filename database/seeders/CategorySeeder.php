<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    const CATEGORY_FAKE_COUNT = 70;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::query()->truncate();
        $now = now();
        $dataCategorySeeder = [];
        for ($i = 0; $i < self::CATEGORY_FAKE_COUNT; $i++) {
            $dataCategorySeeder[] = [
                'name' => fake()->sentence,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        Category::query()->insert(values: $dataCategorySeeder);
    }
}
