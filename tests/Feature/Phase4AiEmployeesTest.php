<?php

namespace Tests\Feature;

use App\Models\AiEmployee;
use App\Models\AiMemory;
use App\Models\AiTask;
use App\Models\AiWorkSchedule;
use App\Models\User;
use App\Services\Ai\AiTaskService;
use Database\Seeders\AiEmployeeSeeder;
use Database\Seeders\AiSkillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase4AiEmployeesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private AiEmployee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([AiSkillSeeder::class, AiEmployeeSeeder::class]);

        $this->admin = User::factory()->create(['email' => 'admin@lymity.local', 'role' => 'admin_geral', 'status' => 'active']);
        $this->employee = AiEmployee::where('slug', 'social-media-ia')->first();
    }

    // ─── T01: AiEmployee model ───────────────────────────────
    public function test_ai_employee_has_required_fields(): void
    {
        $this->assertNotNull($this->employee);
        $this->assertEquals('social-media-ia', $this->employee->slug);
        $this->assertEquals('social_media_ai', $this->employee->role_key);
        $this->assertEquals('active', $this->employee->status);
        $this->assertEquals('low', $this->employee->autonomy_level);
        $this->assertTrue($this->employee->requires_approval_default);
    }

    // ─── T02: AiEmployee skills relationship ─────────────────
    public function test_ai_employee_has_skills(): void
    {
        $this->employee->load('skills');
        $this->assertGreaterThan(0, $this->employee->skills->count());
    }

    // ─── T03: AiEmployee status labels ───────────────────────
    public function test_ai_employee_status_labels(): void
    {
        $this->assertEquals('Ativo', $this->employee->status_label);
        $this->employee->status = 'inactive';
        $this->assertEquals('Inativo', $this->employee->status_label);
        $this->employee->status = 'disabled';
        $this->assertEquals('Desativado', $this->employee->status_label);
    }

    // ─── T04: createManualTask ────────────────────────────────
    public function test_can_create_manual_task(): void
    {
        $this->actingAs($this->admin);

        $service = app(AiTaskService::class);
        $task = $service->createManualTask($this->employee, [
            'title'     => 'Test task',
            'task_type' => 'social_post',
        ]);

        $this->assertDatabaseHas('ai_tasks', ['id' => $task->id, 'status' => 'queued']);
        $this->assertEquals('social-media-ia', $task->employee->slug);
    }

    // ─── T05: runTask produces output ────────────────────────
    public function test_run_task_produces_output(): void
    {
        $this->actingAs($this->admin);

        $service = app(AiTaskService::class);
        $task = $service->createManualTask($this->employee, [
            'title'              => 'Generate social posts',
            'task_type'          => 'social_post',
            'requires_approval'  => true,
        ]);

        $result = $service->runTask($task);

        $this->assertEquals('waiting_approval', $result->status);
        $this->assertNotEmpty($result->output);
    }

    // ─── T06: runTask → no approval → completed ──────────────
    public function test_run_task_without_approval_completes(): void
    {
        $this->actingAs($this->admin);

        $service = app(AiTaskService::class);
        $task = $service->createManualTask($this->employee, [
            'title'             => 'Auto-complete task',
            'task_type'         => 'data_analysis',
            'requires_approval' => false,
        ]);

        $result = $service->runTask($task);

        $this->assertEquals('completed', $result->status);
    }

    // ─── T07: approveTask ────────────────────────────────────
    public function test_approve_task(): void
    {
        $this->actingAs($this->admin);

        $service = app(AiTaskService::class);
        $task = $service->createManualTask($this->employee, [
            'title'             => 'Task to approve',
            'task_type'         => 'copywriting',
            'requires_approval' => true,
        ]);
        $service->runTask($task);
        $task->refresh();

        $result = $service->approveTask($task, $this->admin);

        $this->assertEquals('completed', $result->status);
        $this->assertEquals($this->admin->id, $result->approved_by);
    }

    // ─── T08: rejectTask ─────────────────────────────────────
    public function test_reject_task(): void
    {
        $this->actingAs($this->admin);

        $service = app(AiTaskService::class);
        $task = $service->createManualTask($this->employee, [
            'title'             => 'Task to reject',
            'task_type'         => 'seo_plan',
            'requires_approval' => true,
        ]);
        $service->runTask($task);
        $task->refresh();

        $result = $service->rejectTask($task, 'Não atende ao padrão', $this->admin);

        $this->assertEquals('rejected', $result->status);
        $this->assertStringContainsString('Não atende', $result->error_message);
    }

    // ─── T09: cancelTask ─────────────────────────────────────
    public function test_cancel_queued_task(): void
    {
        $this->actingAs($this->admin);

        $service = app(AiTaskService::class);
        $task = $service->createManualTask($this->employee, [
            'title'     => 'Task to cancel',
            'task_type' => 'general',
        ]);

        $result = $service->cancelTask($task);

        $this->assertEquals('canceled', $result->status);
    }

    // ─── T10: task logs are created ──────────────────────────
    public function test_task_execution_creates_logs(): void
    {
        $this->actingAs($this->admin);

        $service = app(AiTaskService::class);
        $task = $service->createManualTask($this->employee, [
            'title'     => 'Logged task',
            'task_type' => 'general',
        ]);
        $service->runTask($task);

        $this->assertGreaterThan(0, $task->logs()->count());
    }

    // ─── T11: AiMemory model ─────────────────────────────────
    public function test_ai_memory_can_be_created(): void
    {
        $memory = AiMemory::create([
            'ai_employee_id' => $this->employee->id,
            'memory_type'    => 'rule',
            'title'          => 'Test rule',
            'content'        => 'Never skip approval.',
            'weight'         => 9,
        ]);

        $this->assertDatabaseHas('ai_memories', ['id' => $memory->id, 'memory_type' => 'rule']);
        $this->assertEquals('Regra', $memory->memory_type_label);
    }

    // ─── T12: AiWorkSchedule isDue ───────────────────────────
    public function test_work_schedule_is_due(): void
    {
        $schedule = AiWorkSchedule::create([
            'ai_employee_id'      => $this->employee->id,
            'frequency'           => 'daily',
            'active'              => true,
            'next_run_at'         => now()->subMinutes(10),
            'work_minutes_per_run'=> 15,
            'max_tasks_per_run'   => 3,
        ]);

        $this->assertTrue($schedule->isDue());
    }

    // ─── T13: inactive schedule is not due ───────────────────
    public function test_inactive_schedule_is_not_due(): void
    {
        $schedule = AiWorkSchedule::create([
            'ai_employee_id'      => $this->employee->id,
            'frequency'           => 'daily',
            'active'              => false,
            'work_minutes_per_run'=> 15,
            'max_tasks_per_run'   => 3,
        ]);

        $this->assertFalse($schedule->isDue());
    }

    // ─── T14: admin can view AI employees list ────────────────
    public function test_admin_can_view_ai_employees(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/ai-employees');
        $response->assertStatus(200);
        $response->assertSeeText('Funcionários IA');
    }

    // ─── T15: admin can view AI tasks list ───────────────────
    public function test_admin_can_view_ai_tasks(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/ai-tasks');
        $response->assertStatus(200);
    }

    // ─── T16: admin can view AI logs ─────────────────────────
    public function test_admin_can_view_ai_logs(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/ai-logs');
        $response->assertStatus(200);
    }

    // ─── T17: admin can view AI schedules ────────────────────
    public function test_admin_can_view_ai_schedules(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/ai-schedules');
        $response->assertStatus(200);
    }

    // ─── T18: admin can view AI memories ─────────────────────
    public function test_admin_can_view_ai_memories(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/ai-memories');
        $response->assertStatus(200);
    }

    // ─── T19: MockAiProvider never runs external actions ─────
    public function test_mock_provider_output_contains_draft_notice(): void
    {
        $this->actingAs($this->admin);

        $service = app(AiTaskService::class);
        $task = $service->createManualTask($this->employee, [
            'title'             => 'Social content draft',
            'task_type'         => 'social_post',
            'requires_approval' => false,
        ]);
        $result = $service->runTask($task);

        $this->assertNotEmpty($result->output);
        $this->assertStringNotContainsString('PUBLICADO', $result->output);
        $this->assertStringNotContainsString('ENVIADO', $result->output);
    }
}
