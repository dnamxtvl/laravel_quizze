<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Random\RandomException;

class AnswerSeeder extends Seeder
{
    const ANSWER_FAKE_OF_QUESTION_COUNT = 4;
    const CHUNK_SIZE = 4000;

    /**
     * Run the database seeds.
     * @throws RandomException
     */
    public function run(): void
    {
        Answer::query()->truncate();
        $listQuestions = Question::query()->get();
        $dataAnswerSeeder = [];
        $now = now();
        foreach ($listQuestions as $question) {
            $randomIndexCorrectAnswer = random_int(0, self::ANSWER_FAKE_OF_QUESTION_COUNT - 1);
            for ($i = 0; $i < self::ANSWER_FAKE_OF_QUESTION_COUNT; $i++) {
                $dataAnswerSeeder[] = [
                    'answer' => fake()->sentence,
                    'is_correct' => $i === $randomIndexCorrectAnswer,
                    'question_id' => $question->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        collect($dataAnswerSeeder)->chunk(size: self::CHUNK_SIZE)->each(function ($chunk) {
            Answer::query()->insert($chunk->toArray());
        });
    }
}
