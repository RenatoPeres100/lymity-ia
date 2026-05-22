<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiEmployee extends Model
{
    protected $fillable = [
        'name', 'slug', 'role_key', 'title', 'description',
        'autonomy_level', 'status', 'avatar',
        'default_client_id', 'max_tasks_per_day', 'requires_approval_default',
        'provider_type', 'model_name',
        'approval_required', 'can_publish', 'can_send_messages', 'can_manage_ads_budget',
        'routine_description', 'system_prompt', 'created_by',
    ];

    protected $casts = [
        'approval_required'         => 'boolean',
        'can_publish'               => 'boolean',
        'can_send_messages'         => 'boolean',
        'can_manage_ads_budget'     => 'boolean',
        'requires_approval_default' => 'boolean',
    ];

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(AiSkill::class, 'ai_employee_skill')->withPivot('level')->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(AiTask::class);
    }

    public function taskLogs(): HasMany
    {
        return $this->hasMany(AiTaskLog::class);
    }

    public function memories(): HasMany
    {
        return $this->hasMany(AiMemory::class);
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(AiFeedback::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(AiWorkSchedule::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function defaultClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'default_client_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function requiresApproval(): bool
    {
        return $this->requires_approval_default ?? $this->approval_required ?? true;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active'   => 'Ativo',
            'paused'   => 'Pausado',
            'inactive' => 'Inativo',
            'disabled' => 'Desativado',
            default    => $this->status ?? '—',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active'   => 'badge-green',
            'paused'   => 'badge-yellow',
            'inactive',
            'disabled' => 'badge-gray',
            default    => 'badge-gray',
        };
    }

    public function getAutonomyLabelAttribute(): string
    {
        return match ($this->autonomy_level ?? 'low') {
            'low'    => 'Baixa',
            'medium' => 'Média',
            'high'   => 'Alta',
            default  => $this->autonomy_level ?? '—',
        };
    }

    public function getAvatarEmojiAttribute(): string
    {
        return match ($this->role_key) {
            'social_media_ai'    => '📱',
            'copywriter_ai'      => '✍️',
            'traffic_manager_ai' => '🎯',
            'seo_ai'             => '🔍',
            'designer_ai'        => '🎨',
            'sdr_ai'             => '📞',
            'analyst_ai'         => '📊',
            'project_manager_ai' => '🗂️',
            default              => '🤖',
        };
    }

    public function getTodayTasksCountAttribute(): int
    {
        return $this->tasks()->whereDate('created_at', today())->count();
    }
}
