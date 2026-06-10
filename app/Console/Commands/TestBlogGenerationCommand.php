<?php

namespace App\Console\Commands;

use App\Exceptions\AIInvalidJsonResponseException;
use App\Models\AgentTask;
use App\Services\AI\AIProviderManager;
use App\Services\AI\Execution\AgentTaskExecutionService;
use App\Services\AI\Prompt\StructuredPromptBuilderService;
use App\Services\AI\Response\AIContentPayloadValidatorService;
use App\Services\AI\Response\AIJsonResponseNormalizerService;
use Illuminate\Console\Command;

class TestBlogGenerationCommand extends Command
{
    protected $signature = 'ai:test-blog-generation
                            {task_id : ID da AgentTask de blog}
                            {--no-save : Não criar BlogPost nem ApprovalRequest (padrão)}
                            {--show-preview : Exibir os primeiros 500 chars do content_markdown}';

    protected $description = 'Test blog generation for a specific task without saving anything';

    public function handle(
        AIProviderManager $providerManager,
        StructuredPromptBuilderService $promptBuilder,
        AIJsonResponseNormalizerService $jsonNormalizer,
        AIContentPayloadValidatorService $validator,
        AgentTaskExecutionService $service,
    ): int {
        $taskId = (int) $this->argument('task_id');
        $task   = AgentTask::with('aiEmployee')->find($taskId);

        if (!$task) {
            $this->error("AgentTask ID {$taskId} não encontrada.");
            $this->line("Tasks disponíveis:");
            AgentTask::latest('id')->take(10)->get(['id', 'title', 'task_type', 'status'])
                ->each(fn($t) => $this->line("  [{$t->id}] {$t->title} — {$t->task_type} — {$t->status}"));
            return self::FAILURE;
        }

        $this->info("=== ai:test-blog-generation — Task #{$task->id}: {$task->title} ===");
        $this->newLine();
        $this->line("  task_type:   {$task->task_type}");
        $this->line("  employee:    " . ($task->aiEmployee?->name ?? 'N/A'));
        $this->line("  status:      {$task->status}");

        // Resolve max tokens
        $maxTokens = config('ai.blog_max_output_tokens', 6000);
        $this->line("  max_tokens:  {$maxTokens}");

        // Validate brand context
        $this->newLine();
        $this->line("[1/5] Verificando Brand Context...");
        $companyId = $task->company_id;
        $brand = \App\Models\AgencyBrandContext::where('company_id', $companyId)->where('active', true)->first()
              ?? \App\Models\AgencyBrandContext::where('active', true)->first();
        if ($brand) {
            $this->line("  Brand Context: #{$brand->id} '{$brand->brand_name}' — OK");
        } else {
            $this->warn("  Brand Context: não encontrado — usando fallback genérico.");
        }

        // Build prompt
        $this->line("[2/5] Montando prompt...");
        $employee = $task->aiEmployee;
        if (!$employee) {
            $this->error("  Funcionário IA não encontrado para a task.");
            return self::FAILURE;
        }

        // Create a lightweight context for testing
        $fakeContext = new \App\Models\AiExecutionContext([
            'agent_task_id'        => $task->id,
            'compact_brand_context'=> $brand?->getEffectiveCompactContext() ?? 'Lymity IA — agência digital especializada.',
            'compact_task_context' => "Tarefa: {$task->title}. Tipo: {$task->task_type}. Instruções: " . ($task->instructions ?? 'Gerar artigo de blog profissional.'),
            'selected_memories'    => [],
        ]);

        $prompt = $promptBuilder->buildForTaskExecution($task, $employee, $fakeContext);
        $promptLen = strlen($prompt);
        $this->line("  Prompt length: {$promptLen} chars");

        // Call Gemini
        $this->line("[3/5] Chamando Gemini ({$maxTokens} tokens)...");
        $provider = $providerManager->provider();
        $start    = microtime(true);

        $response = $provider->generateText([
            'system_prompt' => "Você é um assistente especialista em conteúdo. RESPONDA APENAS COM JSON VÁLIDO.",
            'user_message'  => $prompt,
            'json_mode'     => true,
            'task_type'     => $task->task_type,
        ]);

        $elapsed = round(microtime(true) - $start, 2);

        $this->line("  Tempo de resposta: {$elapsed}s");
        $this->line("  Model:          " . ($response->model ?? 'N/A'));
        $this->line("  finish_reason:  " . ($response->finish_reason ?? 'N/A'));
        $this->line("  input_tokens:   " . ($response->input_tokens ?? 'N/A'));
        $this->line("  output_tokens:  " . ($response->output_tokens ?? 'N/A'));

        if (!$response->success) {
            $this->error("  Geração FALHOU: " . $response->error_message);
            return self::FAILURE;
        }

        if ($response->finish_reason && $response->finish_reason !== 'STOP') {
            $this->warn("  ATENÇÃO: finish_reason={$response->finish_reason} — resposta pode estar truncada!");
            if (in_array($response->finish_reason, ['MAX_TOKENS', 'LENGTH'])) {
                $this->warn("  → Adicione AI_BLOG_MAX_OUTPUT_TOKENS=6000 no .env para evitar truncamentos.");
            }
        }

        // Parse JSON
        $this->line("[4/5] Parseando JSON...");
        try {
            $data = $jsonNormalizer->normalizeToArray($response->text, [], $task->task_type);
            $this->line("  JSON parseado: OK. Chaves recebidas: " . implode(', ', array_keys($data)));
        } catch (\Throwable $e) {
            $this->error("  JSON inválido: " . $e->getMessage());
            $this->line("  Preview: " . mb_substr($response->text ?? '', 0, 300));
            return self::FAILURE;
        }

        // Validate
        $this->line("[5/5] Validando payload...");
        try {
            $validated = $validator->validateBlogPostPayload($data);
            $wordCount = $validated['_word_count'] ?? str_word_count($validated['content_markdown'] ?? '');
            $this->info("  Validator: PASSOU. word_count={$wordCount}");
        } catch (AIInvalidJsonResponseException $e) {
            $this->error("  Validator FALHOU: " . $e->getMessage());
            $received = array_keys(array_filter($data, fn($v) => $v !== null && $v !== ''));
            $this->warn("  Campos recebidos: " . implode(', ', $received));
            return self::FAILURE;
        }

        if ($this->option('show-preview')) {
            $this->newLine();
            $this->line("=== content_markdown preview (500 chars) ===");
            $this->line(mb_substr($validated['content_markdown'] ?? '', 0, 500));
        }

        $this->newLine();
        $this->info("Geração validada com sucesso. Nenhum BlogPost ou ApprovalRequest foi criado (--no-save implícito).");

        return self::SUCCESS;
    }
}
