<?php

namespace App\Services\Agents;

use App\Models\AgentRoutine;
use App\Services\Agents\RoutineHandlers\BlogRoutineHandler;
use App\Services\Agents\RoutineHandlers\CopywriterRoutineHandler;
use App\Services\Agents\RoutineHandlers\SocialRoutineHandler;

class RoutineHandlerResolver
{
    private const BLOG_TYPES = [
        'blog_post_creation', 'generate_blog_post', 'blog_post_generation', 'create_blog_post',
    ];

    private const SOCIAL_TYPES = [
        'social_post_creation', 'generate_social_post', 'social_post_generation', 'create_social_post',
    ];

    private const COPY_TYPES = [
        'copy_improvement', 'copy_review', 'review_pending_content', 'content_review',
    ];

    public function resolve(AgentRoutine $routine): BlogRoutineHandler|SocialRoutineHandler|CopywriterRoutineHandler
    {
        $type = $routine->routine_type ?? '';

        if (in_array($type, self::BLOG_TYPES)) {
            return app(BlogRoutineHandler::class);
        }

        if (in_array($type, self::SOCIAL_TYPES)) {
            return app(SocialRoutineHandler::class);
        }

        if (in_array($type, self::COPY_TYPES)) {
            return app(CopywriterRoutineHandler::class);
        }

        throw new \RuntimeException(
            "Tipo de rotina não reconhecido: '{$type}'. "
            . "Tipos suportados: " . implode(', ', array_merge(self::BLOG_TYPES, self::SOCIAL_TYPES, self::COPY_TYPES))
        );
    }

    public function typeLabel(string $routineType): string
    {
        if (in_array($routineType, self::BLOG_TYPES))   return 'Blog';
        if (in_array($routineType, self::SOCIAL_TYPES)) return 'Social';
        if (in_array($routineType, self::COPY_TYPES))   return 'Copy Review';
        return $routineType;
    }
}
