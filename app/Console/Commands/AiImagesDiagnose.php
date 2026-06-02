<?php

namespace App\Console\Commands;

use App\Models\AiImageGeneration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AiImagesDiagnose extends Command
{
    protected $signature   = 'ai-images:diagnose';
    protected $description = 'Diagnose AI Images / Gemini configuration';

    public function handle(): int
    {
        $this->info('=== AI Images Diagnose ===');
        $this->newLine();

        $provider  = config('ai.provider', 'not set');
        $apiKey    = config('ai.gemini_api_key');
        $textModel = config('ai.gemini_text_model');
        $imgModel  = config('ai.gemini_image_model');
        $enabled   = config('ai-images.enabled', false);
        $daily     = config('ai-images.gemini.daily_limit') ?? 'sem limite';
        $monthly   = config('ai-images.gemini.monthly_limit') ?? 'sem limite';
        $disk      = config('ai-images.storage.disk', 'public');
        $path      = config('ai-images.storage.path', 'ai-images');
        $baseUrl   = config('ai-images.storage.public_base_url');

        $this->line('  AI_PROVIDER:             ' . $provider);
        $this->line('  GEMINI_API_KEY:          ' . ($apiKey ? '******* (configurada)' : '<error>NÃO configurada</error>'));
        $this->line('  GEMINI_TEXT_MODEL:       ' . ($textModel ?? '<comment>não definido</comment>'));
        $this->line('  GEMINI_IMAGE_MODEL:      ' . ($imgModel ?? '<comment>não definido</comment>'));
        $this->line('  GEMINI_IMAGE_ENABLED:    ' . ($enabled ? '<info>true</info>' : '<comment>false</comment>'));
        $this->line('  Limite diário:           ' . $daily);
        $this->line('  Limite mensal:           ' . $monthly);

        $this->newLine();
        $this->line('  Gerações hoje:           ' . AiImageGeneration::whereDate('created_at', today())->count());
        $this->line('  Gerações este mês:       ' . AiImageGeneration::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count());
        $this->line('  Total concluídas:        ' . AiImageGeneration::where('status', 'completed')->count());
        $this->line('  Total com falha:         ' . AiImageGeneration::where('status', 'failed')->count());

        $lastError = AiImageGeneration::where('status', 'failed')->whereNotNull('error_message')->latest()->value('error_message');
        $this->line('  Último erro:             ' . ($lastError ? substr($lastError, 0, 100) : 'nenhum'));

        $this->newLine();
        $storageLink  = file_exists(public_path('storage'));
        $storageReady = Storage::disk($disk)->exists($path);
        $this->line('  Storage link ativo:      ' . ($storageLink ? '<info>sim</info>' : '<error>não — rode php artisan storage:link</error>'));
        $this->line('  Pasta AI Images existe:  ' . ($storageReady ? '<info>sim</info>' : '<comment>não — será criada na primeira geração</comment>'));
        $this->line('  URL pública base:        ' . $baseUrl);

        $this->newLine();
        if (!$apiKey) {
            $this->error('GEMINI_API_KEY não configurada. Configure no .env e rode php artisan optimize:clear');
            return self::FAILURE;
        }
        if (!$enabled) {
            $this->warn('GEMINI_IMAGE_ENABLED=false. Para gerar imagens, configure true no .env');
        }
        if (!$imgModel) {
            $this->warn('GEMINI_IMAGE_MODEL não configurado. Configure imagen-3.0-generate-002 ou similar no .env');
        }
        if ($enabled && $apiKey && $imgModel) {
            $this->info('Configuração OK. Use php artisan ai-images:test-gemini para testar a conexão.');
        }

        return self::SUCCESS;
    }
}
