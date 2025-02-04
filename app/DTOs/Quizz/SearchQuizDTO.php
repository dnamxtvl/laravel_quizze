<?php

namespace App\DTOs\Quizz;

use App\Enums\Quiz\CreatedByEnum;

readonly class SearchQuizDTO
{
    public function __construct(
        private ?array $userIds,
        private ?string $code,
        private array $createdAt = [],
        private ?int $categoryId,
        private ?CreatedByEnum $createdBy,
    ) {}

    public function toArray(): array
    {
        return [
            'user_ids' => $this->userIds,
            'code' => $this->code,
            'created_at_between' => $this->createdAt,
            'category_id' => $this->categoryId,
            'created_by_sys' => !is_null($this->createdBy) ? (int)($this->createdBy == CreatedByEnum::SYS) : null,
        ];
    }
}
