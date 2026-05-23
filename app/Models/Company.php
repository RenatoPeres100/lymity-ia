<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Company extends Model
{
    protected $fillable = [
        'name', 'legal_name', 'tax_id', 'email',
        'phone', 'website', 'logo', 'status',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function socialChannels(): HasMany
    {
        return $this->hasMany(SocialChannel::class);
    }

    public function socialPosts(): HasMany
    {
        return $this->hasMany(SocialPost::class);
    }

    public function socialCalendars(): HasMany
    {
        return $this->hasMany(SocialCalendar::class);
    }

    public function socialContentBriefs(): HasMany
    {
        return $this->hasMany(SocialContentBrief::class);
    }
}
