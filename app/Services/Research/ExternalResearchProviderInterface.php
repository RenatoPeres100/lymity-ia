<?php

namespace App\Services\Research;

interface ExternalResearchProviderInterface
{
    /**
     * Search for recent content on the given topics.
     *
     * @return array Array of ['title' => ..., 'summary' => ..., 'url' => ..., 'date' => ...]
     */
    public function searchRecent(string $query, int $recencyDays, int $limit = 5): array;
}
