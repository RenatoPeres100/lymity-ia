<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Phase 1
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            AdminUserSeeder::class,
            DemoClientSeeder::class,
        ]);

        // Phase 2
        $this->call([
            AiSkillSeeder::class,
            AiEmployeeSeeder::class,
            BlogCategorySeeder::class,
            BlogPostSeeder::class,
            CaseStudySeeder::class,
            LeadSeeder::class,
        ]);

        // Phase 3
        $this->call([
            ClientBrandProfileSeeder::class,
            ClientWebsiteSeeder::class,
            ClientWebsitePageSeeder::class,
            ClientAssetSeeder::class,
            ClientKnowledgeBaseSeeder::class,
            ClientBlogPostSeeder::class,
        ]);

        // Phase 4
        $this->call([
            AiWorkScheduleSeeder::class,
            AiMemorySeeder::class,
        ]);

        // Phase 5
        $this->call([
            ApprovalRequestSeeder::class,
        ]);

        // Phase 6
        $this->call([
            SocialChannelSeeder::class,
            SocialContentBriefSeeder::class,
            SocialCalendarSeeder::class,
            SocialPostSeeder::class,
        ]);
    }
}
