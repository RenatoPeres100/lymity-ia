<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\ApprovalRequest;
use App\Models\BlogPost;
use App\Models\Client;
use App\Models\Company;
use App\Models\ContentBrief;
use App\Models\ExternalFile;
use App\Models\SocialPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AccessScopeTestSeeder extends Seeder
{
    public function run(): void
    {
        // ── Companies ────────────────────────────────────────────────────────
        $companyA = Company::firstOrCreate(
            ['name' => 'Agência Lymity Teste A'],
            ['email' => 'agencia-a@lymity.local', 'status' => 'active']
        );

        $companyB = Company::firstOrCreate(
            ['name' => 'Agência Lymity Teste B'],
            ['email' => 'agencia-b@lymity.local', 'status' => 'active']
        );

        // ── Clients ──────────────────────────────────────────────────────────
        $clientA1 = Client::firstOrCreate(
            ['name' => 'Cliente A1', 'company_id' => $companyA->id],
            ['email' => 'clientea1@lymity.local', 'status' => 'active']
        );

        $clientA2 = Client::firstOrCreate(
            ['name' => 'Cliente A2', 'company_id' => $companyA->id],
            ['email' => 'clientea2@lymity.local', 'status' => 'active']
        );

        $clientB1 = Client::firstOrCreate(
            ['name' => 'Cliente B1', 'company_id' => $companyB->id],
            ['email' => 'clienteb1@lymity.local', 'status' => 'active']
        );

        // ── Users ────────────────────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'admin_global_test@lymity.local'],
            [
                'name'      => 'Admin Global Teste',
                'password'  => Hash::make('password123'),
                'role'      => 'admin_geral',
                'user_type' => 'internal',
                'status'    => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'agencia_admin_a@lymity.local'],
            [
                'name'       => 'Admin Geral A (migrado)',
                'password'   => Hash::make('password123'),
                'role'       => 'admin_geral',
                'user_type'  => 'internal',
                'status'     => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'agencia_admin_b@lymity.local'],
            [
                'name'      => 'Admin Geral B (migrado)',
                'password'  => Hash::make('password123'),
                'role'      => 'admin_geral',
                'user_type' => 'internal',
                'status'    => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'cliente_admin_a1@lymity.local'],
            [
                'name'      => 'Cliente A1',
                'password'  => Hash::make('password123'),
                'role'      => 'cliente',
                'user_type' => 'client',
                'client_id' => $clientA1->id,
                'status'    => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'cliente_admin_b1@lymity.local'],
            [
                'name'      => 'Cliente B1',
                'password'  => Hash::make('password123'),
                'role'      => 'cliente',
                'user_type' => 'client',
                'client_id' => $clientB1->id,
                'status'    => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'colaborador_a1@lymity.local'],
            [
                'name'      => 'Colaborador A1',
                'password'  => Hash::make('password123'),
                'role'      => 'colaborador',
                'user_type' => 'client',
                'client_id' => $clientA1->id,
                'status'    => 'active',
            ]
        );

        // ── Blog Posts ───────────────────────────────────────────────────────
        BlogPost::firstOrCreate(
            ['title' => '[TESTE] Post Blog Company A', 'company_id' => $companyA->id],
            [
                'slug'       => 'test-post-blog-company-a',
                'content'    => 'Conteúdo de teste para Company A.',
                'type'       => 'agency',
                'status'     => 'draft',
            ]
        );

        BlogPost::firstOrCreate(
            ['title' => '[TESTE] Post Blog Company B', 'company_id' => $companyB->id],
            [
                'slug'       => 'test-post-blog-company-b',
                'content'    => 'Conteúdo de teste para Company B.',
                'type'       => 'agency',
                'status'     => 'draft',
            ]
        );

        // ── Content Briefs ───────────────────────────────────────────────────
        ContentBrief::firstOrCreate(
            ['title' => '[TESTE] Brief Company A', 'company_id' => $companyA->id],
            ['topic' => 'Teste A', 'status' => 'draft']
        );

        ContentBrief::firstOrCreate(
            ['title' => '[TESTE] Brief Company B', 'company_id' => $companyB->id],
            ['topic' => 'Teste B', 'status' => 'draft']
        );

        // ── Social Posts ─────────────────────────────────────────────────────
        SocialPost::firstOrCreate(
            ['title' => '[TESTE] Social Post Company A', 'company_id' => $companyA->id],
            [
                'objective'    => 'awareness',
                'content_type' => 'feed',
                'status'       => 'draft',
                'main_caption' => 'Post de teste Company A',
            ]
        );

        SocialPost::firstOrCreate(
            ['title' => '[TESTE] Social Post Company B', 'company_id' => $companyB->id],
            [
                'objective'    => 'awareness',
                'content_type' => 'feed',
                'status'       => 'draft',
                'main_caption' => 'Post de teste Company B',
            ]
        );

        // ── Approval Requests ────────────────────────────────────────────────
        ApprovalRequest::firstOrCreate(
            ['title' => '[TESTE] Aprovação Cliente A1', 'client_id' => $clientA1->id],
            [
                'approval_type'  => 'other',
                'status'         => 'pending',
                'sensitive_level'=> 'low',
                'description'    => 'Aprovação de teste para Cliente A1',
            ]
        );

        ApprovalRequest::firstOrCreate(
            ['title' => '[TESTE] Aprovação Cliente B1', 'client_id' => $clientB1->id],
            [
                'approval_type'  => 'other',
                'status'         => 'pending',
                'sensitive_level'=> 'low',
                'description'    => 'Aprovação de teste para Cliente B1',
            ]
        );

        // ── Activity Logs ────────────────────────────────────────────────────
        ActivityLog::firstOrCreate(
            ['action' => 'test.scope.companyA', 'client_id' => $clientA1->id],
            [
                'module'      => 'test',
                'level'       => 'info',
                'description' => 'Log de teste para Company A / Cliente A1',
            ]
        );

        ActivityLog::firstOrCreate(
            ['action' => 'test.scope.companyB', 'client_id' => $clientB1->id],
            [
                'module'      => 'test',
                'level'       => 'info',
                'description' => 'Log de teste para Company B / Cliente B1',
            ]
        );

        // ── External Files ───────────────────────────────────────────────────
        ExternalFile::firstOrCreate(
            ['name' => '[TESTE] Arquivo Cliente A1', 'client_id' => $clientA1->id],
            [
                'file_type' => 'document',
                'source'    => 'upload',
                'company_id'=> $companyA->id,
            ]
        );

        ExternalFile::firstOrCreate(
            ['name' => '[TESTE] Arquivo Cliente B1', 'client_id' => $clientB1->id],
            [
                'file_type' => 'document',
                'source'    => 'upload',
                'company_id'=> $companyB->id,
            ]
        );

        $this->command->info('AccessScopeTestSeeder: dados de teste criados com sucesso.');
        $this->command->info('Usuários (senha: password123):');
        $this->command->info('  admin_global_test@lymity.local  → Admin Geral');
        $this->command->info('  cliente_admin_a1@lymity.local   → Cliente (Cliente A1)');
        $this->command->info('  cliente_admin_b1@lymity.local   → Cliente (Cliente B1)');
        $this->command->info('  colaborador_a1@lymity.local     → Colaborador (Cliente A1)');
    }
}
