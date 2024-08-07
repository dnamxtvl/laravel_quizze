<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\Quizze;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class QuestionSeeder extends Seeder
{
    const QUESTION_FAKE_COUNT = 10000;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Question::query()->truncate();
        $dataQuestionSeeder = [];
        $quizzeIds = Quizze::query()->pluck('id')->toArray();
        $now = now();
        for ($i = 0; $i < self::QUESTION_FAKE_COUNT; $i++) {
            $dataQuestionSeeder[] = [
                'id' => Str::uuid(),
                'title' => fake()->sentence,
                'quizze_id' => array_rand(array: $quizzeIds),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        Question::query()->insert(values: $dataQuestionSeeder);
    }
}
