<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalItem extends Model
{
    protected $fillable = [
        'proposal_id', 'name', 'description', 'quantity', 'unit_price', 'total_price',
    ];

    protected $casts = [
        'quantity'    => 'float',
        'unit_price'  => 'float',
        'total_price' => 'float',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    protected static function booted(): void
    {
        static::saving(function (self $item) {
            $item->total_price = round($item->quantity * $item->unit_price, 2);
        });
    }
}
