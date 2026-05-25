<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'platform'     => $this->platform,
            'objective'    => $this->objective,
            'status'       => $this->status,
            'daily_budget' => $this->daily_budget,
            'total_budget' => $this->total_budget,
            'start_date'   => $this->start_date,
            'end_date'     => $this->end_date,
        ];
    }
}
