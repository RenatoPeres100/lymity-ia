<?php

return [
    // === GESTÃO DE USUÁRIOS ===
    'user_management'       => true,
    'role_management'       => true,
    'permission_management' => true,

    // === MÓDULOS ATIVOS ===
    'blog_pipeline'       => true,
    'instagram_pipeline'  => true,
    'ai_social_media'     => true,
    'ai_copywriter'       => true,
    'ai_blog_writer'      => true,

    // === FASE 5 — ROTINAS OPERACIONAIS ===
    'agent_routines'       => true,
    'agency_brand_context' => true,

    // === MÓDULOS DESATIVADOS ===
    'ads_module'          => false,
    'proposals_module'    => false,
    'budgets_module'      => false,
    'contracts_module'    => false,
    'crm_module'          => false,
    'sdr_module'          => false,
    'reports_fake_module' => false,
    'cases_demo_module'   => false,
    'demo_mode'           => false,

    // === INSTAGRAM / META ===
    'instagram_connection'  => true,
    'instagram_publishing'  => true,

    // === PUBLISHING / COMMAND CENTER ===
    'content_command_center' => true,
    'publishing_queue'       => true,
];
