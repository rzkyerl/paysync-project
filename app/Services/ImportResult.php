<?php

namespace App\Services;

final class ImportResult
{
    /**
     * @param  array<int, array{row:int, field:string, message:string}>  $errors
     */
    public function __construct(
        public readonly int $imported = 0,
        public readonly array $errors = [],
    ) {}

    public function successful(): bool
    {
        return $this->errors === [];
    }
}
