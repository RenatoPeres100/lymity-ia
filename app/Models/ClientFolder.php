<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientFolder extends Model
{
    protected $fillable = [
        'client_id', 'name', 'description',
        'external_folder_id', 'local_path',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
