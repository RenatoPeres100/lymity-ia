<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    protected $table = 'app_notifications';

    protected $fillable = [
        'user_id', 'client_id', 'title', 'message',
        'type', 'status', 'action_url', 'metadata', 'read_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'read_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function markAsRead(): void
    {
        $this->update(['status' => 'read', 'read_at' => now()]);
    }

    public static function notify(int $userId, string $title, ?string $message = null, string $type = 'info', ?string $actionUrl = null, ?int $clientId = null, ?array $metadata = null): self
    {
        return self::create([
            'user_id'    => $userId,
            'client_id'  => $clientId,
            'title'      => $title,
            'message'    => $message,
            'type'       => $type,
            'action_url' => $actionUrl,
            'metadata'   => $metadata,
            'status'     => 'unread',
        ]);
    }
}
