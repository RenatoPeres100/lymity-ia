<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GeneratedContentPackage extends Model
{
    protected $fillable = [
        'company_id', 'client_id',
        'agent_task_id', 'agent_task_run_id', 'ai_employee_id', 'approval_request_id',
        'package_type', 'title', 'summary',
        'content_payload', 'visual_payload', 'sources_payload',
        'status',
        'generated_entity_type', 'generated_entity_id',
        'scheduled_at', 'published_at',
        'metadata',
    ];

    protected $casts = [
        'content_payload'  => 'array',
        'visual_payload'   => 'array',
        'sources_payload'  => 'array',
        'metadata'         => 'array',
        'scheduled_at'     => 'datetime',
        'published_at'     => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function agentTask(): BelongsTo
    {
        return $this->belongsTo(AgentTask::class);
    }

    public function agentTaskRun(): BelongsTo
    {
        return $this->belongsTo(AgentTaskRun::class);
    }

    public function aiEmployee(): BelongsTo
    {
        return $this->belongsTo(AiEmployee::class);
    }

    public function approvalRequest(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class);
    }

    public function generatedEntity(): MorphTo
    {
        return $this->morphTo('generated_entity');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(GeneratedContentAsset::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->role === 'admin_geral') return $query;
        if ($user->company_id) return $query->where('company_id', $user->company_id);
        return $query->whereRaw('1=0');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'            => 'Rascunho',
            'pending_approval' => 'Aguardando Aprovação',
            'approved'         => 'Aprovado',
            'rejected'         => 'Rejeitado',
            'scheduled'        => 'Agendado',
            'published'        => 'Publicado',
            'failed'           => 'Falhou',
            'archived'         => 'Arquivado',
            default            => $this->status ?? '—',
        ];
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'approved', 'published', 'scheduled' => 'green',
            'pending_approval'                   => 'yellow',
            'rejected', 'failed'                 => 'red',
            'draft'                              => 'blue',
            default                              => 'gray',
        ];
    }

    public function hasImage(): bool
    {
        $visual = $this->visual_payload ?? [];
        return !empty($visual['featured_image_url']) || !empty($visual['feed_image_url']);
    }

    public function getVisualStatus(): string
    {
        $visual = $this->visual_payload ?? [];
        return $visual['visual_status'] ?? 'unknown';
    }

    public function isImagePendingConfiguration(): bool
    {
        return $this->getVisualStatus() === 'pending_configuration';
    }
}
