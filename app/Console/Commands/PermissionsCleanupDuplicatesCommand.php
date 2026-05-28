<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PermissionsCleanupDuplicatesCommand extends Command
{
    protected $signature   = 'permissions:cleanup-duplicates';
    protected $description = 'Remove duplicate entries from user_permissions table';

    public function handle(): int
    {
        $this->line('Checking for duplicate user_permissions...');

        $dupes = DB::table('user_permissions')
            ->select('user_id', 'permission_id', DB::raw('COUNT(*) as cnt'), DB::raw('MIN(id) as keep_id'))
            ->groupBy('user_id', 'permission_id')
            ->having('cnt', '>', 1)
            ->get();

        if ($dupes->isEmpty()) {
            $this->info('[OK] No duplicate user_permissions found.');
            return 0;
        }

        $removed = 0;

        DB::transaction(function () use ($dupes, &$removed) {
            foreach ($dupes as $dupe) {
                $deleted = DB::table('user_permissions')
                    ->where('user_id', $dupe->user_id)
                    ->where('permission_id', $dupe->permission_id)
                    ->where('id', '!=', $dupe->keep_id)
                    ->delete();
                $removed += $deleted;
            }
        });

        $this->info("[OK] Removed {$removed} duplicate user_permission rows.");
        return 0;
    }
}
