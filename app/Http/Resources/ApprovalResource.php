<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'description'     => $this->description,
            'approval_type'   => $this->approval_type,
            'status'          => $this->status,
            'sensitive_level' => $this->sensitive_level,
            'due_at'          => $this->due_at?->toIso8601String(),
            'client'          => $this->client ? ['id' => $this->client->id, 'name' => $this->client->name] : null,
            'requested_by'    => $this->requestedBy ? ['id' => $this->requestedBy->id, 'name' => $this->requestedBy->name] : null,
            'created_at'      => $this->created_at->toIso8601String(),
            'actions_count'   => $this->actions()->count(),
            'comments_count'  => $this->comments()->count(),
            'payload'         => $this->payload,
        ];
    }
}
