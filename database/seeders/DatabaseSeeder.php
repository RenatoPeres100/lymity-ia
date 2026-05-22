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
    }
}
