<?php

namespace App\Contracts;

interface RequiresMediaCustomProperties
{
    /**
     * Validation rules for media custom properties per collection.
     *
     * @return array<string, array<int, string>>
     */
    public function requiredMediaCustomProperties(string $collectionName): array;
}
