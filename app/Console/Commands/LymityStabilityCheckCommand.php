<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;

class LymityStabilityCheckCommand extends Command
{
    protected $signature   = 'lymity:stability-check';
    protected $description = 'Verifica todos os pontos críticos de estabilidade da plataforma';

    private int $passed   = 0;
    private int $errCount = 0;
    private int $warned   = 0;

    public function handle(): int
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════╗');
        $this->info('║     Lymity IA — Stability Check                 ║');
        $this->info('║     ' . now()->format('Y-m-d H:i:s') . '                       ║');
        $this->info('╚══════════════════════════════════════════════════╝');
        $this->info('');

        $this->checkThreadsConfig();
        $this->checkRedis();
        $this->checkBladeLayout();
        $this->checkPayloadValidator();
        $this->checkAiEmployeeSkills();
        $this->checkMainRoutes();
        $this->checkBlogTokenConfig();
        $this->checkEnvExample();

        $this->info('');
        $this->info('──────────────────────────────────────────────────');
        $total = $this->passed + $this->errCount + $this->warned;
        $this->info("Resultado: {$this->passed} OK | {$this->warned} avisos | {$this->errCount} erros | {$total} verificações");
        $this->info('──────────────────────────────────────────────────');
        $this->info('');

        return $this->errCount > 0 ? self::FAILURE : self::SUCCESS;
    }

    // ── Checks ───────────────────────────────────────────────────────────────

    private function checkThreadsConfig(): void
    {
        $this->section('Threads — Configuração');

        $appId     = config('threads.app_id');
        $appSecret = config('threads.app_secret');
        $redirect  = config('threads.redirect_uri');
        $enabled   = config('threads.publishing_enabled', false);

        $configured = !empty($appId) && !empty($appSecret) && !empty($redirect);

        if ($configured) {
            $this->ok('THREADS_APP_ID presente');
            $this->ok('THREADS_APP_SECRET presente');
            $this->ok('THREADS_REDIRECT_URI: ' . $redirect);
        } else {
            if (empty($appId))     $this->warn_('THREADS_APP_ID ausente — Threads não conectável (esperado se não configurado)');
            if (empty($appSecret)) $this->warn_('THREADS_APP_SECRET ausente');
            if (empty($redirect))  $this->warn_('THREADS_REDIRECT_URI ausente');
        }

        $this->ok('THREADS_PUBLISHING_ENABLED=' . ($enabled ? 'true' : 'false') . ' (false = seguro)');

        // Ensure the index view exists with correct syntax (no @extends)
        $viewPath = resource_path('views/admin/social/threads/index.blade.php');
        if (file_exists($viewPath)) {
            $content = file_get_contents($viewPath);
            if (str_contains($content, '@extends')) {
                $this->err('threads/index.blade.php ainda usa @extends — causará $slot undefined');
            } else {
                $this->ok('threads/index.blade.php usa sintaxe correta (x-layouts.app)');
            }
        }

        // Check feature flag
        $featureEnabled = config('features.threads_connection', false);
        $this->ok('Feature threads_connection=' . ($featureEnabled ? 'true' : 'false'));
    }

    private function checkRedis(): void
    {
        $this->section('Redis / Fila');

        $queueConn  = config('queue.default');
        $cacheStore = config('cache.default');
        $redisHost  = config('database.redis.default.host', '127.0.0.1');
        $redisPort  = config('database.redis.default.port', 6379);

        $this->line("  Queue connection : {$queueConn}");
        $this->line("  Cache store      : {$cacheStore}");

        if ($queueConn === 'redis' || $cacheStore === 'redis') {
            try {
                $ping = Redis::ping();
                $this->ok('Redis respondendo (' . $redisHost . ':' . $redisPort . ')');
            } catch (\Exception $e) {
                $this->err('Redis configurado mas não responde: ' . $e->getMessage());
                $this->line('  → Sugestão: sudo systemctl start redis-server');
                $this->line('  → Alternativa temporária: QUEUE_CONNECTION=database no .env');
            }
        } else {
            $this->ok("Redis não é dependência crítica (queue={$queueConn}, cache={$cacheStore})");
        }
    }

    private function checkBladeLayout(): void
    {
        $this->section('Blade Layout — $slot');

        $layoutPath = resource_path('views/components/layouts/app.blade.php');
        if (!file_exists($layoutPath)) {
            $this->err('Layout app.blade.php não encontrado em ' . $layoutPath);
            return;
        }

        $content = file_get_contents($layoutPath);

        if (!str_contains($content, '{{ $slot }}') && !str_contains($content, '{!! $slot !!}')) {
            $this->err('Layout não possui {{ $slot }} — pode quebrar componentes');
            return;
        }

        // Find views still using @extends with this layout
        $threadsViews = glob(resource_path('views/admin/social/threads/**/*.blade.php'));
        $threadsViews = array_merge($threadsViews, glob(resource_path('views/admin/social/threads/*.blade.php')));
        $badViews = [];
        foreach ($threadsViews as $v) {
            $c = file_get_contents($v);
            if (str_contains($c, '@extends')) {
                $badViews[] = str_replace(resource_path('views/'), '', $v);
            }
        }

        if (!empty($badViews)) {
            $this->err('Views Threads ainda usam @extends (causam $slot undefined):');
            foreach ($badViews as $bv) {
                $this->line('    ' . $bv);
            }
        } else {
            $this->ok('Todas as views Threads usam <x-layouts.app> (sem $slot undefined)');
        }

        // Scan all views for @extends with components.layouts.app
        $allViews = $this->globRecursive(resource_path('views'), '*.blade.php');
        $badExtends = [];
        foreach ($allViews as $v) {
            $c = file_get_contents($v);
            if (str_contains($c, "@extends('components.layouts.app')")) {
                $badExtends[] = str_replace(resource_path('views/') . '/', '', $v);
            }
        }

        if (!empty($badExtends)) {
            $this->err(count($badExtends) . ' view(s) usam @extends — $slot será undefined:');
            foreach ($badExtends as $bv) {
                $this->line('    ' . $bv);
            }
        } else {
            $this->ok('Nenhuma view usa @extends(\'components.layouts.app\')');
        }
    }

    private function checkPayloadValidator(): void
    {
        $this->section('AIContentPayloadValidator — array handling');

        $validator = app(\App\Services\AI\Response\AIContentPayloadValidatorService::class);

        // Test 1: sections as array (body must exceed 500-word minimum)
        try {
            $payload = [
                'title' => 'Teste Sections',
                'sections' => [
                    ['heading' => 'Introdução', 'body' => str_repeat('Parágrafo de introdução com conteúdo relevante. ', 80)],
                    ['heading' => 'Desenvolvimento', 'body' => str_repeat('Parágrafo de desenvolvimento aprofundado. ', 80)],
                    ['heading' => 'Conclusão', 'body' => str_repeat('Parágrafo de conclusão e chamada para ação. ', 80)],
                ],
            ];
            $result = $validator->validateBlogPostPayload($payload);
            if (!empty($result['content_markdown'])) {
                $this->ok('Sections como array: convertido corretamente');
            } else {
                $this->err('Sections como array: content_markdown vazio após conversão');
            }
        } catch (\Exception $e) {
            $this->err('Sections como array lançou exceção: ' . $e->getMessage());
        }

        // Test 2: paragraphs as array
        try {
            $payload = [
                'title' => 'Teste Paragraphs',
                'paragraphs' => array_fill(0, 20, str_repeat('Parágrafo de conteúdo do artigo. ', 5)),
            ];
            $result = $validator->validateBlogPostPayload($payload);
            if (!empty($result['content_markdown'])) {
                $this->ok('Paragraphs como array: convertido corretamente');
            } else {
                $this->err('Paragraphs como array: content_markdown vazio após conversão');
            }
        } catch (\Exception $e) {
            $this->err('Paragraphs como array lançou exceção: ' . $e->getMessage());
        }

        // Test 3: partial payload (only title + content_type) must fail controlled
        try {
            $payload = ['content_type' => 'blog_post', 'title' => 'Só o título'];
            $validator->validateBlogPostPayload($payload);
            $this->err('Payload parcial deveria lançar exceção mas não lançou');
        } catch (\App\Exceptions\AIInvalidJsonResponseException $e) {
            $this->ok('Payload parcial falha controlado: ' . substr($e->getMessage(), 0, 60));
        } catch (\Exception $e) {
            $this->err('Payload parcial lançou exceção inesperada: ' . get_class($e));
        }
    }

    private function checkAiEmployeeSkills(): void
    {
        $this->section('AI Employee Skills — FK integrity');

        try {
            $orphans = DB::table('ai_employee_skill as p')
                ->leftJoin('ai_employees as e', 'p.ai_employee_id', '=', 'e.id')
                ->leftJoin('ai_skills as s', 'p.ai_skill_id', '=', 's.id')
                ->where(function ($q) {
                    $q->whereNull('e.id')->orWhereNull('s.id');
                })
                ->count();

            if ($orphans === 0) {
                $this->ok('Nenhum pivot órfão em ai_employee_skill');
            } else {
                $this->err("{$orphans} pivot(s) órfão(s). Execute: php artisan ai:repair-employee-skills --fix");
            }

            $employees = DB::table('ai_employees')->count();
            $skills    = DB::table('ai_skills')->count();
            $pivots    = DB::table('ai_employee_skill')->count();
            $this->line("  ai_employees={$employees} | ai_skills={$skills} | pivots={$pivots}");
        } catch (\Exception $e) {
            $this->err('Erro ao verificar ai_employee_skill: ' . $e->getMessage());
        }
    }

    private function checkMainRoutes(): void
    {
        $this->section('Rotas Principais — sem 500');

        $routes = [
            'admin.social.threads.index'       => '/admin/social/threads',
            'admin.social.threads.posts.index'  => '/admin/social/threads/posts',
            'admin.social.threads.logs'         => '/admin/social/threads/logs',
            'admin.operation'                   => '/admin/operation',
            'admin.approvals.index'             => '/admin/approvals',
            'admin.blog.pipeline.index'         => '/admin/blog/pipeline',
            'admin.social.pipeline.index'       => '/admin/social/pipeline',
        ];

        foreach ($routes as $name => $uri) {
            try {
                if (\Illuminate\Support\Facades\Route::has($name)) {
                    $this->ok("Rota registrada: {$uri}");
                } else {
                    $this->err("Rota não encontrada: {$name} ({$uri})");
                }
            } catch (\Exception $e) {
                $this->err("Erro ao verificar rota {$name}: " . $e->getMessage());
            }
        }
    }

    private function checkBlogTokenConfig(): void
    {
        $this->section('Blog — Configuração de Tokens');

        $blogMax  = config('ai.blog_max_output_tokens', 0);
        $blogMin  = config('ai.blog_min_words', 0);
        $aiMax    = config('ai.max_tokens', 0);

        if ($blogMax >= 4000) {
            $this->ok("AI_BLOG_MAX_OUTPUT_TOKENS={$blogMax} (adequado para artigos completos)");
        } else {
            $this->warn_("AI_BLOG_MAX_OUTPUT_TOKENS={$blogMax} — recomendado ≥6000. Adicione ao .env: AI_BLOG_MAX_OUTPUT_TOKENS=6000");
        }

        if ($blogMin >= 400) {
            $this->ok("AI_BLOG_MIN_WORDS={$blogMin}");
        } else {
            $this->warn_("AI_BLOG_MIN_WORDS={$blogMin} — recomendado ≥500");
        }

        if ($aiMax < 2000) {
            $this->warn_("AI_MAX_TOKENS={$aiMax} — este é o limite genérico; o blog usa AI_BLOG_MAX_OUTPUT_TOKENS={$blogMax}");
        } else {
            $this->ok("AI_MAX_TOKENS={$aiMax}");
        }
    }

    private function checkEnvExample(): void
    {
        $this->section('.env.example — Variáveis obrigatórias');

        $required = [
            'THREADS_APP_ID',
            'THREADS_APP_SECRET',
            'THREADS_REDIRECT_URI',
            'THREADS_PUBLISHING_ENABLED',
            'AI_BLOG_MAX_OUTPUT_TOKENS',
            'AI_BLOG_MIN_WORDS',
            'GEMINI_BLOG_MAX_OUTPUT_TOKENS',
        ];

        $examplePath = base_path('.env.example');
        if (!file_exists($examplePath)) {
            $this->warn_('.env.example não encontrado');
            return;
        }

        $example = file_get_contents($examplePath);
        foreach ($required as $var) {
            if (str_contains($example, $var)) {
                $this->ok(".env.example contém {$var}");
            } else {
                $this->warn_(".env.example sem {$var}");
            }
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function section(string $title): void
    {
        $this->info('');
        $this->info("▶ {$title}");
    }

    private function ok(string $msg): void
    {
        $this->line("  <fg=green>✓</> {$msg}");
        $this->passed++;
    }

    private function err(string $msg): void
    {
        $this->line("  <fg=red>✗</> {$msg}");
        $this->errCount++;
    }

    private function warn_(string $msg): void
    {
        $this->line("  <fg=yellow>⚠</> {$msg}");
        $this->warned++;
    }

    private function globRecursive(string $directory, string $pattern): array
    {
        $files   = glob(rtrim($directory, '/') . '/' . $pattern) ?: [];
        $subdirs = glob(rtrim($directory, '/') . '/*', GLOB_ONLYDIR) ?: [];
        foreach ($subdirs as $subdir) {
            $files = array_merge($files, $this->globRecursive($subdir, $pattern));
        }
        return $files;
    }
}
