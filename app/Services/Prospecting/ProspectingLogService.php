<?php

namespace App\Services\Prospecting;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class ProspectingLogService
{
    public function log(string $action, mixed $subject, ?User $user, array $metadata = []): void
    {
        ActivityLog::create([
            'user_id'      => $user?->id,
            'action'       => $action,
            'module'       => 'prospecting',
            'level'        => 'info',
            'description'  => $this->buildDescription($action, $subject),
            'subject_type' => $subject instanceof Model ? get_class($subject) : null,
            'subject_id'   => $subject instanceof Model ? $subject->getKey() : null,
            'metadata'     => $metadata,
            'ip_address'   => Request::ip(),
            'user_agent'   => Request::userAgent(),
        ]);
    }

    private function buildDescription(string $action, mixed $subject): string
    {
        return match($action) {
            'prospect.lead.created'              => 'Lead criado',
            'prospect.lead.updated'              => 'Lead atualizado',
            'prospect.lead.stage_moved'          => 'Lead movido de etapa',
            'prospect.lead.archived'             => 'Lead arquivado',
            'prospect.activity.created'          => 'Atividade criada',
            'prospect.activity.completed'        => 'Atividade concluída',
            'prospect.activity.canceled'         => 'Atividade cancelada',
            'prospect.note.created'              => 'Nota adicionada',
            'prospect.ai.analyzed'               => 'Lead analisado pela IA',
            'prospect.ai.qualified'              => 'Lead qualificado pela IA',
            'prospect.ai.message_generated'      => 'Mensagem gerada pela IA',
            'prospect.ai.next_action_suggested'  => 'Próxima ação sugerida pela IA',
            'prospect.ai.history_summarized'     => 'Histórico resumido pela IA',
            'prospect.message.approved'          => 'Mensagem IA aprovada',
            'prospect.message.rejected'          => 'Mensagem IA rejeitada',
            'prospect.message.used'              => 'Mensagem IA marcada como usada',
            default                              => $action,
        };
    }
}
