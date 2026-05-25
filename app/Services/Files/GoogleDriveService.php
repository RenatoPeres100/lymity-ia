<?php

namespace App\Services\Files;

use App\Models\ActivityLog;
use App\Models\Client;

class GoogleDriveService
{
    public function isConfigured(): bool
    {
        return !empty(config('google-drive.client_id'))
            && !empty(config('google-drive.client_secret'));
    }

    public function connectPlaceholder(?Client $client = null): array
    {
        $this->log('google_drive_connect_clicked', 'Usuário clicou em conectar Google Drive (placeholder)');

        if (!$this->isConfigured()) {
            return [
                'configured' => false,
                'message'    => 'Google Drive ainda não configurado. Configure GOOGLE_DRIVE_CLIENT_ID, GOOGLE_DRIVE_CLIENT_SECRET e GOOGLE_DRIVE_REDIRECT_URI no arquivo .env.',
            ];
        }

        return [
            'configured' => true,
            'message'    => 'Próxima etapa: iniciar fluxo OAuth do Google Drive. Implemente o redirect para a URL de autorização do Google.',
        ];
    }

    public function uploadPlaceholder(): array
    {
        if (!$this->isConfigured()) {
            return ['error' => 'Google Drive não configurado.'];
        }
        return ['message' => 'Upload para Google Drive: implementação OAuth pendente.'];
    }

    public function listPlaceholder(): array
    {
        if (!$this->isConfigured()) {
            return ['error' => 'Google Drive não configurado.'];
        }
        return ['message' => 'Listagem do Google Drive: implementação OAuth pendente.'];
    }

    public function getAuthUrlPlaceholder(): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }
        return 'https://accounts.google.com/o/oauth2/auth?[placeholder — OAuth ainda não implementado]';
    }

    private function log(string $action, string $description): void
    {
        try {
            ActivityLog::create([
                'action'      => $action,
                'level'       => 'info',
                'description' => $description,
            ]);
        } catch (\Throwable) {
        }
    }
}
