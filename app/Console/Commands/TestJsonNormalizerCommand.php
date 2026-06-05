<?php

namespace App\Console\Commands;

use App\Exceptions\AIInvalidJsonResponseException;
use App\Services\AI\Response\AIJsonResponseNormalizerService;
use Illuminate\Console\Command;

class TestJsonNormalizerCommand extends Command
{
    protected $signature = 'ai:test-json-normalizer';
    protected $description = 'Test the AIJsonResponseNormalizerService with various inputs';

    public function handle(AIJsonResponseNormalizerService $normalizer): int
    {
        $this->info('=== TEST: AIJsonResponseNormalizerService ===');
        $this->newLine();

        $cases = [
            [
                'name'     => '1. JSON válido puro',
                'input'    => '{"title":"Teste","content":"Conteúdo simples"}',
                'expected' => true,
            ],
            [
                'name'     => '2. JSON dentro de ```json',
                'input'    => "```json\n{\"title\":\"Teste\",\"content\":\"valor\"}\n```",
                'expected' => true,
            ],
            [
                'name'     => '3. Texto antes e depois do JSON',
                'input'    => "Aqui está o JSON:\n{\"title\":\"ok\",\"slug\":\"ok\"}\nFim.",
                'expected' => true,
            ],
            [
                'name'     => '4. JSON com BOM UTF-8',
                'input'    => "\xEF\xBB\xBF{\"title\":\"BOM\",\"slug\":\"bom\"}",
                'expected' => true,
            ],
            [
                'name'     => '5. JSON com caracteres de controle (newline literal em string)',
                'input'    => "{\"title\":\"ok\",\"content\":\"linha1\nlinha2\nlinha3\"}",
                'expected' => true,
            ],
            [
                'name'     => '6. JSON com HTML e newlines (caso real do Gemini)',
                'input'    => '{"title":"Artigo","content_html":"<h2>Título</h2>' . "\n" . '<p>Parágrafo com\nnewline real</p>"}',
                'expected' => true,
            ],
            [
                'name'     => '7. JSON inválido irrecuperável',
                'input'    => 'Isso não é JSON de jeito nenhum. Apenas texto aleatório.',
                'expected' => false,
            ],
        ];

        $passed = 0;
        $failed = 0;

        foreach ($cases as $case) {
            try {
                $result = $normalizer->normalizeToArray($case['input']);
                if ($case['expected']) {
                    $this->line("<fg=green>✓ PASS</> {$case['name']} → keys: " . implode(',', array_keys($result)));
                    $passed++;
                } else {
                    $this->line("<fg=red>✗ FAIL</> {$case['name']} — esperava exceção, mas parseou como: " . json_encode(array_keys($result)));
                    $failed++;
                }
            } catch (AIInvalidJsonResponseException $e) {
                if (!$case['expected']) {
                    $this->line("<fg=green>✓ PASS</> {$case['name']} → exceção esperada: " . \Str::limit($e->getMessage(), 80));
                    $passed++;
                } else {
                    $this->line("<fg=red>✗ FAIL</> {$case['name']} — exceção inesperada: " . $e->getMessage());
                    $failed++;
                }
            } catch (\Throwable $e) {
                $this->line("<fg=red>✗ ERROR</> {$case['name']} — " . $e->getMessage());
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Resultado: {$passed} passed / {$failed} failed");

        return $failed > 0 ? 1 : 0;
    }
}
