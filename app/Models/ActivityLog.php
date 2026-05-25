<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Request;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'ai_employee_id', 'client_id',
        'action', 'module', 'level', 'description', 'metadata',
        'subject_type', 'subject_id',
        'ip_address', 'user_agent',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function aiEmployee(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function getLevelColorAttribute(): string
    {
        return match ($this->level) {
            'success'  => 'text-green-400',
            'warning'  => 'text-yellow-400',
            'error'    => 'text-red-400',
            'critical' => 'text-red-600',
            default    => 'text-slate-400',
        };
    }

    public function getLevelBadgeAttribute(): string
    {
        return match ($this->level) {
            'success'  => 'badge-success',
            'warning'  => 'badge-warning',
            'error'    => 'badge-error',
            'critical' => 'badge-critical',
            default    => 'badge-info',
        };
    }

    public static function record(
        string $action,
        string $module,
        string $description,
        ?int $clientId = null,
        ?array $metadata = null,
        string $level = 'info'
    ): self {
        return self::create([
            'user_id'     => auth()->id(),
            'client_id'   => $clientId,
            'action'      => $action,
            'module'      => $module,
            'level'       => $level,
            'description' => $description,
            'metadata'    => $metadata,
            'ip_address'  => Request::ip(),
            'user_agent'  => Request::userAgent(),
        ]);
    }
}
