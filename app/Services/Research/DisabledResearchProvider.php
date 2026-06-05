<?php

namespace App\Services\Research;

class DisabledResearchProvider implements ExternalResearchProviderInterface
{
    public function searchRecent(string $query, int $recencyDays, int $limit = 5): array
    {
        return [];
    }
}
