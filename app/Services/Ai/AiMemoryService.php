<?php

namespace App\Services\Ai;

use App\Models\AiMemory;
use Illuminate\Database\Eloquent\Collection;

class AiMemoryService
{
    public function getRelevantMemories(int $employeeId, ?int $clientId = null, int $limit = 10): Collection
    {
        $query = AiMemory::where('ai_employee_id', $employeeId)
            ->orderByDesc('weight')
            ->orderByDesc('updated_at')
            ->limit($limit);

        if ($clientId) {
            $query->where(function ($q) use ($clientId) {
                $q->where('client_id', $clientId)->orWhereNull('client_id');
            });
        }

        return $query->get();
    }

    public function buildMemoryContext(int $employeeId, ?int $clientId = null): string
    {
        $memories = $this->getRelevantMemories($employeeId, $clientId);

        if ($memories->isEmpty()) {
            return '';
        }

        $lines = ["## Memórias relevantes\n"];
        foreach ($memories as $memory) {
            $lines[] = "**[{$memory->memory_type_label}] {$memory->title}**\n{$memory->content}";
        }

        return implode("\n\n", $lines);
    }

    public function store(int $employeeId, string $type, string $title, string $content, ?int $clientId = null, int $weight = 1): AiMemory
    {
        return AiMemory::create([
            'ai_employee_id' => $employeeId,
            'client_id'      => $clientId,
            'memory_type'    => $type,
            'title'          => $title,
            'content'        => $content,
            'weight'         => $weight,
        ]);
    }

    public function update(AiMemory $memory, array $data): AiMemory
    {
        $memory->update($data);
        return $memory->fresh();
    }

    public function delete(AiMemory $memory): void
    {
        $memory->delete();
    }
}
