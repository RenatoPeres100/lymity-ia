<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiEmployee extends Model
{
    protected $fillable = [
        'name', 'slug', 'role_key', 'title', 'description', 'status',
        'provider_type', 'model_name', 'avatar',
        'approval_required', 'can_publish', 'can_send_messages', 'can_manage_ads_budget',
        'routine_description', 'system_prompt', 'created_by',
    ];

    protected $casts = [
        'approval_required'     => 'boolean',
        'can_publish'           => 'boolean',
        'can_send_messages'     => 'boolean',
        'can_manage_ads_budget' => 'boolean',
    ];

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(AiSkill::class, 'ai_employee_skill');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(AiTask::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function requiresApproval(): bool
    {
        return $this->approval_required;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active'   => 'Ativo',
            'paused'   => 'Pausado',
            'inactive' => 'Inativo',
            default    => $this->status ?? '—',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active'   => 'badge-green',
            'paused'   => 'badge-orange',
            'inactive' => 'badge-gray',
            default    => 'badge-gray',
        };
    }

    public function getAvatarEmojiAttribute(): string
    {
        return match ($this->role_key) {
            'social_media_ai'      => '📱',
            'copywriter_ai'        => '✍️',
            'traffic_manager_ai'   => '🎯',
            'seo_ai'               => '🔍',
            'designer_ai'          => '🎨',
            'sdr_ai'               => '📞',
            'analyst_ai'           => '📊',
            'project_manager_ai'   => '🗂️',
            default                => '🤖',
        };
    }
}
