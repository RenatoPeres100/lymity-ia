<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Request;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'client_id', 'action', 'module',
        'description', 'metadata', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public static function record(
        string $action,
        string $module,
        string $description,
        ?int $clientId = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'user_id'     => auth()->id(),
            'client_id'   => $clientId,
            'action'      => $action,
            'module'      => $module,
            'description' => $description,
            'metadata'    => $metadata,
            'ip_address'  => Request::ip(),
            'user_agent'  => Request::userAgent(),
        ]);
    }
}
