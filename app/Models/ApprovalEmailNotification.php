<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalEmailNotification extends Model
{
    protected $fillable = [
        'approval_request_id',
        'user_id',
        'email',
        'notification_type',
        'status',
        'subject',
        'sent_at',
        'failed_at',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'sent_at'    => 'datetime',
        'failed_at'  => 'datetime',
    ];

    public function approvalRequest(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isSent(): bool   { return $this->status === 'sent'; }
    public function isFailed(): bool { return $this->status === 'failed'; }
    public function isSkipped(): bool{ return $this->status === 'skipped'; }
}
