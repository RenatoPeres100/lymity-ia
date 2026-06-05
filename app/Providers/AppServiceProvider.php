<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\AgencyBrandContext;
use App\Models\AgentRoutine;
use App\Models\AiTask;
use App\Models\ApprovalRequest;
use App\Models\BlogPost;
use App\Models\Client;
use App\Models\ContentBrief;
use App\Models\ExternalFile;
use App\Models\ProspectActivity;
use App\Models\ProspectAiInsight;
use App\Models\ProspectLead;
use App\Models\ProspectMessageSuggestion;
use App\Models\ProspectPipeline;
use App\Models\SocialPost;
use App\Models\User;
use App\Policies\ActivityLogPolicy;
use App\Policies\AgentRoutinePolicy;
use App\Policies\AiTaskPolicy;
use App\Policies\ApprovalRequestPolicy;
use App\Policies\BlogPostPolicy;
use App\Policies\BrandContextPolicy;
use App\Policies\ClientPolicy;
use App\Policies\ContentBriefPolicy;
use App\Policies\ExternalFilePolicy;
use App\Policies\ProspectActivityPolicy;
use App\Policies\ProspectAiPolicy;
use App\Policies\ProspectLeadPolicy;
use App\Policies\ProspectPipelinePolicy;
use App\Policies\SocialPostPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Research provider binding
        $this->app->bind(
            \App\Services\Research\ExternalResearchProviderInterface::class,
            \App\Services\Research\DisabledResearchProvider::class
        );
    }

    public function boot(): void
    {
        Gate::policy(User::class,              UserPolicy::class);
        Gate::policy(Client::class,            ClientPolicy::class);
        Gate::policy(BlogPost::class,          BlogPostPolicy::class);
        Gate::policy(ContentBrief::class,      ContentBriefPolicy::class);
        Gate::policy(SocialPost::class,        SocialPostPolicy::class);
        Gate::policy(ApprovalRequest::class,   ApprovalRequestPolicy::class);
        Gate::policy(ActivityLog::class,       ActivityLogPolicy::class);
        Gate::policy(AiTask::class,            AiTaskPolicy::class);
        Gate::policy(AgentRoutine::class,      AgentRoutinePolicy::class);
        Gate::policy(ExternalFile::class,      ExternalFilePolicy::class);
        Gate::policy(AgencyBrandContext::class, BrandContextPolicy::class);

        // Prospecting
        Gate::policy(ProspectLead::class,              ProspectLeadPolicy::class);
        Gate::policy(ProspectPipeline::class,          ProspectPipelinePolicy::class);
        Gate::policy(ProspectActivity::class,          ProspectActivityPolicy::class);
        Gate::policy(ProspectMessageSuggestion::class, ProspectAiPolicy::class);
    }
}
