<?php

return [
    // === GESTÃO DE USUÁRIOS ===
    'user_management'       => true,
    'role_management'       => true,
    'permission_management' => true,

    // === GESTÃO DE CLIENTES ===
    'clients_management'  => true,
    'client_user_binding' => true,
    'client_brand_profile'=> true,

    // === MÓDULOS ATIVOS ===
    'blog_pipeline'       => true,
    'instagram_pipeline'  => true,
    'ai_social_media'     => true,
    'ai_copywriter'       => true,
    'ai_blog_writer'      => true,

    // === SOCIAL MEDIA ===
    'social_media_module'        => true,
    'social_post_creation'       => true,
    'social_approval_flow'       => true,
    'social_image_generation'    => true,
    'social_carousel_generation' => true,
    'social_manual_upload'       => true,
    'ai_image_generation'        => true,

    // === FASE 5 — ROTINAS OPERACIONAIS ===
    'agent_routines'       => true,
    'agency_brand_context' => true,

    // === BLOG AUTOMATION — REAL PHASE ===
    'blog_text_pipeline'  => true,
    'blog_image_pipeline' => false,
    'ai_blog_planner'     => true,
    'ai_cover_image'      => false,

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
