<?php

namespace App\Console\Commands;

use App\Exceptions\AIInvalidJsonResponseException;
use App\Services\AI\Response\AIContentPayloadValidatorService;
use Illuminate\Console\Command;

class TestPayloadValidatorCommand extends Command
{
    protected $signature = 'ai:test-payload-validator';
    protected $description = 'Test AIContentPayloadValidatorService with all content aliases';

    public function handle(AIContentPayloadValidatorService $validator): int
    {
        $this->info('=== TEST: AIContentPayloadValidatorService ===');
        $this->newLine();

        $base = [
            'title'           => 'Título de Teste do Artigo',
            'focus_keyword'   => 'marketing digital',
            'seo_title'       => 'Título SEO de Teste',
            'seo_description' => 'Descrição SEO de teste para o artigo.',
        ];

        $cases = [
            [
                'name'    => '1. content_markdown',
                'payload' => array_merge($base, ['content_markdown' => "## Introdução\n\nTexto do artigo com **markdown** e detalhes."]),
                'expect'  => true,
            ],
            [
                'name'    => '2. content_html',
                'payload' => array_merge($base, ['content_html' => '<article><h2>Título</h2><p>Conteúdo em HTML.</p></article>']),
                'expect'  => true,
            ],
            [
                'name'    => '3. content',
                'payload' => array_merge($base, ['content' => "## Seção\n\nTexto do conteúdo."]),
                'expect'  => true,
            ],
            [
                'name'    => '4. body',
                'payload' => array_merge($base, ['body' => "Texto do corpo do artigo com detalhes."]),
                'expect'  => true,
            ],
            [
                'name'    => '5. article',
                'payload' => array_merge($base, ['article' => "## Artigo\n\nConteúdo completo aqui."]),
                'expect'  => true,
            ],
            [
                'name'    => '6. article_content',
                'payload' => array_merge($base, ['article_content' => "## Conteúdo\n\nTexto completo."]),
                'expect'  => true,
            ],
            [
                'name'    => '7. text',
                'payload' => array_merge($base, ['text' => "Texto simples do artigo."]),
                'expect'  => true,
            ],
            [
                'name'    => '8. html',
                'payload' => array_merge($base, ['html' => '<p>Conteúdo HTML direto.</p>']),
                'expect'  => true,
            ],
            [
                'name'    => '9. markdown',
                'payload' => array_merge($base, ['markdown' => "## Seção\n\nTexto em markdown."]),
                'expect'  => true,
            ],
            [
                'name'    => '10. post_content',
                'payload' => array_merge($base, ['post_content' => "Conteúdo do post completo."]),
                'expect'  => true,
            ],
            [
                'name'    => '11. sem conteúdo — deve falhar',
                'payload' => array_merge($base, []),
                'expect'  => false,
            ],
        ];

        $passed = 0;
        $failed = 0;

        foreach ($cases as $case) {
            try {
                $result = $validator->validateBlogPostPayload($case['payload']);

                if (!$case['expect']) {
                    $this->line("<fg=red>✗ FAIL</> {$case['name']} — esperava exceção, mas validou.");
                    $failed++;
                    continue;
                }

                $hasHtml     = !empty($result['content_html']);
                $hasMarkdown = !empty($result['content_markdown']);

                if ($hasHtml && $hasMarkdown) {
                    $this->line("<fg=green>✓ PASS</> {$case['name']} → content_html + content_markdown preenchidos");
                    $passed++;
                } else {
                    $missing = array_filter(['content_html' => !$hasHtml, 'content_markdown' => !$hasMarkdown]);
                    $this->line("<fg=red>✗ FAIL</> {$case['name']} — campos ausentes: " . implode(', ', array_keys($missing)));
                    $failed++;
                }
            } catch (AIInvalidJsonResponseException $e) {
                if (!$case['expect']) {
                    $this->line("<fg=green>✓ PASS</> {$case['name']} → exceção esperada: " . \Str::limit($e->getMessage(), 100));
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
