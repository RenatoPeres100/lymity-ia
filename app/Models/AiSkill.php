<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AiSkill extends Model
{
    protected $fillable = ['key', 'name', 'description', 'module'];

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(AiEmployee::class, 'ai_employee_skill');
    }
}
