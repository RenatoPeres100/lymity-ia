<?php

namespace App\Console\Commands;

use App\Services\AiImages\AiGeneratedImageValidatorService;
use Illuminate\Console\Command;

class AiImagesValidate extends Command
{
    protected $signature   = 'ai-images:validate {generation_id : ID of the AiImageGeneration to validate}';
    protected $description = 'Validate all images in an AI image generation';

    public function handle(AiGeneratedImageValidatorService $validator): int
    {
        $generationId = (int) $this->argument('generation_id');

        $this->info("Validating generation #{$generationId}...");

        $result = $validator->validateGeneration($generationId);

        $this->line("  Total images: {$result['total']}");

        if ($result['valid']) {
            $this->info('All images are valid!');
            return self::SUCCESS;
        }

        $this->error('Validation failed:');
        foreach ($result['errors'] as $err) {
            $this->line("  - {$err}");
        }

        return self::FAILURE;
    }
}
