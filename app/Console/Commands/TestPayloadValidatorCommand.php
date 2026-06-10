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

        // Long enough content to pass min word count (>= 500 words) — ~600 words
        $para = "O marketing digital transformou profundamente a forma como as empresas se comunicam com seus clientes e prospects. Em um mundo cada vez mais conectado, a presença online tornou-se absolutamente indispensável para qualquer negócio que deseje crescer, escalar e se manter competitivo no mercado atual. As organizações que ainda não investem em estratégias digitais correm o risco de ficarem para trás, perdendo espaço para concorrentes que já entenderam o poder das ferramentas digitais.";
        $longContent = implode("\n\n", [
            "## Introdução ao Marketing Digital\n\n{$para}",
            "## Estratégias Fundamentais de Marketing Digital\n\nEntre as principais estratégias do marketing digital, destacam-se o SEO, as redes sociais, o e-mail marketing e o marketing de conteúdo. Cada uma dessas ferramentas possui características únicas que, quando combinadas de forma inteligente e estratégica, geram resultados expressivos e sustentáveis para as empresas de todos os segmentos e tamanhos.",
            "## SEO e Visibilidade Orgânica nos Buscadores\n\nO SEO, ou Search Engine Optimization, é o conjunto de técnicas utilizadas para melhorar o posicionamento de um site nos resultados orgânicos dos motores de busca como Google e Bing. Um bom trabalho de SEO envolve pesquisa aprofundada de palavras-chave, criação de conteúdo relevante e original, otimização técnica do site e construção de uma rede de links de qualidade apontando para as páginas.",
            "## Redes Sociais como Canal Estratégico\n\nAs redes sociais tornaram-se canais fundamentais para a comunicação das marcas com seu público-alvo. Plataformas como Instagram, Facebook, LinkedIn e TikTok oferecem diferentes possibilidades de segmentação avançada e formatos de conteúdo diversificados que permitem às empresas alcançar seus clientes de maneira personalizada, relevante e muito mais eficiente do que os meios tradicionais.",
            "## Automação e Inteligência Artificial no Marketing\n\nA automação de marketing e a inteligência artificial estão revolucionando a forma como as campanhas são planejadas, executadas e otimizadas. Com ferramentas modernas baseadas em IA, é possível automatizar tarefas repetitivas que antes consumiam horas de trabalho manual, personalizar mensagens em escala para milhares de leads simultaneamente e analisar grandes volumes de dados em tempo real para tomar decisões mais assertivas e baseadas em evidências.",
            "## Mensuração, Análise e Otimização Contínua\n\nUm dos grandes diferenciais do marketing digital em relação ao marketing tradicional é a capacidade de mensurar com extrema precisão os resultados de cada ação realizada. Métricas como taxa de conversão, custo por aquisição de cliente, retorno sobre o investimento publicitário e nível de engajamento são fundamentais para avaliar continuamente o desempenho das campanhas e identificar oportunidades de melhoria.",
            "## Conclusão e Próximos Passos\n\nInvestir em marketing digital é absolutamente fundamental para empresas que desejam crescer de forma sustentável e escalável no ambiente digital moderno. Com planejamento estratégico bem elaborado, execução consistente e disciplinada, além de análise contínua dos resultados obtidos, é possível construir uma presença digital sólida e duradoura que gera resultados reais e mensuráveis para o negócio a longo prazo.",
        ]);

        $base = [
            'title'           => 'Título de Teste do Artigo',
            'focus_keyword'   => 'marketing digital',
            'seo_title'       => 'Título SEO de Teste',
            'seo_description' => 'Descrição SEO de teste para o artigo.',
        ];

        $cases = [
            [
                'name'    => '1. content_markdown (longo)',
                'payload' => array_merge($base, ['content_markdown' => $longContent]),
                'expect'  => true,
            ],
            [
                'name'    => '2. content_html (longo)',
                'payload' => array_merge($base, ['content_html' => '<article><h2>Introdução</h2><p>' . str_replace("\n", ' ', $longContent) . '</p></article>']),
                'expect'  => true,
            ],
            [
                'name'    => '3. content (longo)',
                'payload' => array_merge($base, ['content' => $longContent]),
                'expect'  => true,
            ],
            [
                'name'    => '4. body (longo)',
                'payload' => array_merge($base, ['body' => $longContent]),
                'expect'  => true,
            ],
            [
                'name'    => '5. article (longo)',
                'payload' => array_merge($base, ['article' => $longContent]),
                'expect'  => true,
            ],
            [
                'name'    => '6. article_content (longo)',
                'payload' => array_merge($base, ['article_content' => $longContent]),
                'expect'  => true,
            ],
            [
                'name'    => '7. text (longo)',
                'payload' => array_merge($base, ['text' => $longContent]),
                'expect'  => true,
            ],
            [
                'name'    => '8. html (longo)',
                'payload' => array_merge($base, ['html' => '<div>' . str_replace("\n", ' ', $longContent) . '</div>']),
                'expect'  => true,
            ],
            [
                'name'    => '9. markdown (longo)',
                'payload' => array_merge($base, ['markdown' => $longContent]),
                'expect'  => true,
            ],
            [
                'name'    => '10. post_content (longo)',
                'payload' => array_merge($base, ['post_content' => $longContent]),
                'expect'  => true,
            ],
            [
                'name'    => '11. sem conteúdo — deve falhar',
                'payload' => array_merge($base, []),
                'expect'  => false,
            ],
            // ── Novos casos: payload parcial (como Gemini retornou) ────────────
            [
                'name'    => '12. partial_blog_payload (só content_type + title) — deve falhar',
                'payload' => ['content_type' => 'blog_post', 'title' => 'Teste'],
                'expect'  => false,
            ],
            [
                'name'    => '13. short_blog_payload (< min words) — deve falhar',
                'payload' => array_merge($base, ['content_markdown' => 'Texto curto demais.']),
                'expect'  => false,
            ],
            [
                'name'    => '14. generation_incomplete sentinel — deve falhar',
                'payload' => ['error' => 'generation_incomplete', 'reason' => 'token limit reached'],
                'expect'  => false,
            ],
            [
                'name'    => '15. complete_blog_payload (≥ min words) — deve passar',
                'payload' => array_merge($base, [
                    'content_markdown' => implode("\n\n", array_fill(0, 60, "Este é um parágrafo longo com várias palavras para atingir o mínimo necessário de quinhentas palavras totais no artigo completo.")),
                ]),
                'expect'  => true,
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
