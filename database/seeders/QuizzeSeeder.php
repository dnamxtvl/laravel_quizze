<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Quizze;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class QuizzeSeeder extends Seeder
{
    const QUIZZE_FAKE_COUNT = 1000;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Quizze::query()->truncate();
        $categoryIds = Category::query()->pluck(column: 'id')->toArray();
        $userIds = User::query()->pluck(column: 'id')->toArray();
        $dataQuizzeSeeder = [];
        for ($i = 0; $i < self::QUIZZE_FAKE_COUNT; $i++) {
            $dataQuizzeSeeder[] = [
                'id' => Str::uuid(),
                'title' => fake()->sentence,
                'category_id' => $categoryIds[array_rand($categoryIds)],
                'user_id' => $userIds[array_rand($userIds)],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        Quizze::query()->insert(values: $dataQuizzeSeeder);
    }
}
