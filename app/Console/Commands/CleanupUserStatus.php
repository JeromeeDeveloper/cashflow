<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupUserStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:cleanup-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up user status by setting all users as inactive';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning up user status...');

        $updatedCount = DB::table('users')->update(['status' => 'inactive']);

        $this->info("Updated {$updatedCount} users to inactive status.");
        $this->info('User status cleanup completed successfully!');

        return Command::SUCCESS;
    }
}
