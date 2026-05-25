<?php

namespace App\Services\Logs;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    public function log(array $data): ActivityLog
    {
        return ActivityLog::create(array_merge([
            'user_id'      => auth()->id(),
            'ip_address'   => Request::ip(),
            'user_agent'   => Request::userAgent(),
            'level'        => 'info',
            'module'       => $data['metadata']['module'] ?? 'general',
        ], $data));
    }

    public function info(string $action, ?string $description = null, array $data = []): ActivityLog
    {
        return $this->log($this->buildPayload($action, $description, 'info', $data));
    }

    public function success(string $action, ?string $description = null, array $data = []): ActivityLog
    {
        return $this->log($this->buildPayload($action, $description, 'success', $data));
    }

    public function warning(string $action, ?string $description = null, array $data = []): ActivityLog
    {
        return $this->log($this->buildPayload($action, $description, 'warning', $data));
    }

    public function error(string $action, ?string $description = null, array $data = []): ActivityLog
    {
        return $this->log($this->buildPayload($action, $description, 'error', $data));
    }

    public function critical(string $action, ?string $description = null, array $data = []): ActivityLog
    {
        return $this->log($this->buildPayload($action, $description, 'critical', $data));
    }

    public function logSubject(
        string $action,
        Model $subject,
        ?User $user = null,
        ?Client $client = null,
        string $level = 'info',
        ?string $description = null,
        array $metadata = []
    ): ActivityLog {
        return ActivityLog::create([
            'user_id'      => $user?->id ?? auth()->id(),
            'client_id'    => $client?->id,
            'action'       => $action,
            'module'       => $metadata['module'] ?? 'general',
            'level'        => $level,
            'description'  => $description,
            'subject_type' => get_class($subject),
            'subject_id'   => $subject->getKey(),
            'metadata'     => $metadata,
            'ip_address'   => Request::ip(),
            'user_agent'   => Request::userAgent(),
        ]);
    }

    private function buildPayload(string $action, ?string $description, string $level, array $data): array
    {
        return array_merge([
            'action'      => $action,
            'description' => $description,
            'level'       => $level,
        ], $data);
    }
}
