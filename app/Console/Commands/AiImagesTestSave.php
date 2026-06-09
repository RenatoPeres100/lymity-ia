<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AiImagesTestSave extends Command
{
    protected $signature   = 'ai-images:test-save';
    protected $description = 'Write a test file to generated-content storage and verify it is publicly accessible';

    public function handle(): int
    {
        $disk    = config('ai-images.storage.disk', 'public');
        $folder  = 'generated-content/test';
        $name    = 'storage_test_' . time() . '.txt';
        $path    = $folder . '/' . $name;
        $content = 'storage_write_test_ok';

        $this->info('=== AI Images Storage Test ===');
        $this->line('  Disk:   ' . $disk);
        $this->line('  Path:   ' . $path);

        try {
            Storage::disk($disk)->put($path, $content);
            $this->info('  Escrita: OK');
        } catch (\Throwable $e) {
            $this->error('  Escrita FALHOU: ' . $e->getMessage());
            return self::FAILURE;
        }

        $exists = Storage::disk($disk)->exists($path);
        $this->line('  Existe no disco: ' . ($exists ? '<info>sim</info>' : '<error>não</error>'));

        $url = Storage::disk($disk)->url($path);
        $this->line('  URL pública:     ' . $url);

        // Cleanup
        Storage::disk($disk)->delete($path);
        $this->line('  Arquivo de teste removido.');

        $this->newLine();
        $this->info('Storage OK — gravação e leitura funcionam corretamente.');
        return self::SUCCESS;
    }
}
