<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'company', 'message',
        'service_interest', 'source', 'status', 'notes',
        'utm_source', 'utm_medium', 'utm_campaign', 'ip_address',
    ];

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'new'       => 'Novo',
            'contacted' => 'Contatado',
            'qualified' => 'Qualificado',
            'lost'      => 'Perdido',
            'converted' => 'Convertido',
            default     => $this->status ?? '—',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'new'       => 'badge-blue',
            'contacted' => 'badge-orange',
            'qualified' => 'badge-green',
            'lost'      => 'badge-red',
            'converted' => 'badge-purple',
            default     => 'badge-gray',
        };
    }
}
