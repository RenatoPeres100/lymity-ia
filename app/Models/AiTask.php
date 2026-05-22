<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiTask extends Model
{
    protected $fillable = [
        'ai_employee_id', 'client_id', 'created_by', 'title', 'description',
        'type', 'status', 'priority', 'input_payload', 'output_payload',
        'scheduled_at', 'started_at', 'finished_at',
    ];

    protected $casts = [
        'input_payload'  => 'array',
        'output_payload' => 'array',
        'scheduled_at'   => 'datetime',
        'started_at'     => 'datetime',
        'finished_at'    => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class, 'ai_employee_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AiTaskLog::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(AiApproval::class);
    }
}
