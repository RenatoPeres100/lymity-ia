<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function hasIndex(string $table, string $index): bool
    {
        $rows = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]);
        return count($rows) > 0;
    }

    private function hasColumn(string $table, string $column): bool
    {
        return Schema::hasColumn($table, $column);
    }

    public function up(): void
    {
        // ── users ──────────────────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            if (!$this->hasIndex('users', 'users_role_index')) {
                $table->index('role', 'users_role_index');
            }
            if (!$this->hasIndex('users', 'users_status_index')) {
                $table->index('status', 'users_status_index');
            }
            if (!$this->hasIndex('users', 'users_user_type_index')) {
                $table->index('user_type', 'users_user_type_index');
            }
        });

        // ── clients ────────────────────────────────────────────────────────
        Schema::table('clients', function (Blueprint $table) {
            if (!$this->hasIndex('clients', 'clients_status_index')) {
                $table->index('status', 'clients_status_index');
            }
            if (!$this->hasIndex('clients', 'clients_name_index')) {
                $table->index('name', 'clients_name_index');
            }
        });

        // ── permissions ────────────────────────────────────────────────────
        Schema::table('permissions', function (Blueprint $table) {
            if (!$this->hasIndex('permissions', 'permissions_module_index')) {
                $table->index('module', 'permissions_module_index');
            }
        });

        // ── blog_posts ─────────────────────────────────────────────────────
        Schema::table('blog_posts', function (Blueprint $table) {
            if (!$this->hasIndex('blog_posts', 'blog_posts_status_index')) {
                $table->index('status', 'blog_posts_status_index');
            }
            if (!$this->hasIndex('blog_posts', 'blog_posts_scheduled_at_index') && $this->hasColumn('blog_posts', 'scheduled_at')) {
                $table->index('scheduled_at', 'blog_posts_scheduled_at_index');
            }
            if (!$this->hasIndex('blog_posts', 'blog_posts_published_at_index') && $this->hasColumn('blog_posts', 'published_at')) {
                $table->index('published_at', 'blog_posts_published_at_index');
            }
            if (!$this->hasIndex('blog_posts', 'blog_posts_type_index') && $this->hasColumn('blog_posts', 'type')) {
                $table->index('type', 'blog_posts_type_index');
            }
        });

        // ── social_posts ───────────────────────────────────────────────────
        Schema::table('social_posts', function (Blueprint $table) {
            if (!$this->hasIndex('social_posts', 'social_posts_status_index')) {
                $table->index('status', 'social_posts_status_index');
            }
            if (!$this->hasIndex('social_posts', 'social_posts_scheduled_at_index') && $this->hasColumn('social_posts', 'scheduled_at')) {
                $table->index('scheduled_at', 'social_posts_scheduled_at_index');
            }
            if (!$this->hasIndex('social_posts', 'social_posts_published_at_index') && $this->hasColumn('social_posts', 'published_at')) {
                $table->index('published_at', 'social_posts_published_at_index');
            }
        });

        // ── approval_requests ──────────────────────────────────────────────
        Schema::table('approval_requests', function (Blueprint $table) {
            if (!$this->hasIndex('approval_requests', 'approval_requests_status_index')) {
                $table->index('status', 'approval_requests_status_index');
            }
            if (!$this->hasIndex('approval_requests', 'approval_requests_approval_type_index')) {
                $table->index('approval_type', 'approval_requests_approval_type_index');
            }
            if (!$this->hasIndex('approval_requests', 'approval_requests_created_at_index')) {
                $table->index('created_at', 'approval_requests_created_at_index');
            }
        });

        // ── activity_logs ──────────────────────────────────────────────────
        Schema::table('activity_logs', function (Blueprint $table) {
            if (!$this->hasIndex('activity_logs', 'activity_logs_action_index')) {
                $table->index('action', 'activity_logs_action_index');
            }
            if (!$this->hasIndex('activity_logs', 'activity_logs_level_index')) {
                $table->index('level', 'activity_logs_level_index');
            }
            if (!$this->hasIndex('activity_logs', 'activity_logs_created_at_index')) {
                $table->index('created_at', 'activity_logs_created_at_index');
            }
        });

        // ── ai_task_logs ───────────────────────────────────────────────────
        Schema::table('ai_task_logs', function (Blueprint $table) {
            if (!$this->hasIndex('ai_task_logs', 'ai_task_logs_status_index') && $this->hasColumn('ai_task_logs', 'status')) {
                $table->index('status', 'ai_task_logs_status_index');
            }
            if (!$this->hasIndex('ai_task_logs', 'ai_task_logs_created_at_index')) {
                $table->index('created_at', 'ai_task_logs_created_at_index');
            }
        });

        // ── agent_routines ─────────────────────────────────────────────────
        if (Schema::hasTable('agent_routines')) {
            Schema::table('agent_routines', function (Blueprint $table) {
                if (!$this->hasIndex('agent_routines', 'agent_routines_is_active_index') && $this->hasColumn('agent_routines', 'is_active')) {
                    $table->index('is_active', 'agent_routines_is_active_index');
                }
                if (!$this->hasIndex('agent_routines', 'agent_routines_next_run_at_index') && $this->hasColumn('agent_routines', 'next_run_at')) {
                    $table->index('next_run_at', 'agent_routines_next_run_at_index');
                }
            });
        }

        // ── content_briefs ─────────────────────────────────────────────────
        if (Schema::hasTable('content_briefs')) {
            Schema::table('content_briefs', function (Blueprint $table) {
                if (!$this->hasIndex('content_briefs', 'content_briefs_status_index') && $this->hasColumn('content_briefs', 'status')) {
                    $table->index('status', 'content_briefs_status_index');
                }
            });
        }
    }

    public function down(): void
    {
        $drops = [
            'users'             => ['users_role_index', 'users_status_index', 'users_user_type_index'],
            'clients'           => ['clients_status_index', 'clients_name_index'],
            'permissions'       => ['permissions_module_index'],
            'blog_posts'        => ['blog_posts_status_index', 'blog_posts_scheduled_at_index', 'blog_posts_published_at_index', 'blog_posts_type_index'],
            'social_posts'      => ['social_posts_status_index', 'social_posts_scheduled_at_index', 'social_posts_published_at_index'],
            'approval_requests' => ['approval_requests_status_index', 'approval_requests_approval_type_index', 'approval_requests_created_at_index'],
            'activity_logs'     => ['activity_logs_action_index', 'activity_logs_level_index', 'activity_logs_created_at_index'],
            'ai_task_logs'      => ['ai_task_logs_status_index', 'ai_task_logs_created_at_index'],
        ];

        foreach ($drops as $table => $indexes) {
            Schema::table($table, function (Blueprint $t) use ($table, $indexes) {
                foreach ($indexes as $idx) {
                    if ($this->hasIndex($table, $idx)) {
                        $t->dropIndex($idx);
                    }
                }
            });
        }
    }
};
