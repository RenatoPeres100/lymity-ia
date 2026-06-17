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

    // === REAL AI EMPLOYEE TASK ENGINE ===
    'ai_task_engine_real'          => true,
    'ai_operational_memory'        => true,
    'ai_context_snapshots'         => true,
    'ai_task_recurring_execution'  => true,
    'ai_cost_guard'                => true,
    'ai_content_package_approval'  => true,

    'blog_text_generation'    => true,
    'blog_image_generation'   => false,
    'social_text_generation'  => true,
    'social_image_generation_tasks' => false,

    'auto_publish_without_approval' => false,
    'mock_content_generation'       => false,

    // === MENU VISIBILITY ===
    'agent_tasks_menu'          => true,
    'ai_memory_menu'            => true,
    'ai_usage_menu'             => true,
    'ai_execution_logs_menu'    => true,   // Logs IA no menu Funcionários IA
    'legacy_ai_tasks_menu'      => false,  // AiTasks antigas — ocultas do menu principal
    'legacy_ai_schedules_menu'  => false,  // AiWorkSchedules — ocultas do menu principal
    'legacy_agent_routines_menu'=> false,  // Rotinas legadas — substituídas por AgentTasks

    // === BLOG AUTOMATION — REAL PHASE ===
    'blog_text_pipeline'  => true,
    'blog_image_pipeline' => false,
    'ai_blog_planner'     => true,
    'ai_cover_image'      => false,

    // === AI IMAGE STUDIO ===
    'ai_images_module'         => true,
    'gemini_image_generation'  => true,
    'ai_image_carousel'        => true,
    'ai_image_library'         => true,
    'ai_image_logs'            => true,
    'ai_image_usage'           => true,

    // === PROSPECÇÃO / CRM / SDR IA ===
    'prospecting_module'       => true,
    'crm_pipeline'             => true,
    'sdr_ai_assistant'         => true,
    'crm_auto_outreach'        => false,
    'crm_whatsapp_integration' => false,
    'crm_email_integration'    => false,
    'crm_scraping'             => false,

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

    // === THREADS ===
    'threads_connection'           => true,
    'threads_text_publishing'      => true,
    'threads_publishing_scheduler' => false, // Habilitar apenas após canal conectado e THREADS_PUBLISHING_ENABLED=true
    'threads_image_publishing'     => false,
    'threads_carousel_publishing'  => false,
    'threads_video_publishing'     => false,
    'threads_auto_reply'           => false,
    'threads_insights'             => false,

    // === PUBLISHING / COMMAND CENTER ===
    'content_command_center' => true,
    'publishing_queue'       => true,
];
