<?php

namespace App\Console\Commands;

use App\Models\Batch;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Console\Command;

class BackfillUserProfiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backfill:user-profiles {--force : Overwrite existing profiles}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create missing user_profile rows for users and optionally overwrite existing ones.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting backfill of user profiles...');

        $defaultBatch = Batch::first();

        $users = User::all();
        $created = 0;
        foreach ($users as $user) {
            if (! $user->profile) {
                UserProfile::create([
                    'user_id' => $user->id,
                    'batch_id' => $defaultBatch?->id,
                ]);
                $created++;
            } elseif ($this->option('force')) {
                // ensure required columns exist
                $user->profile()->updateOrCreate(['user_id' => $user->id], [
                    'batch_id' => $user->profile->batch_id ?? $defaultBatch?->id,
                ]);
            }
        }

        $this->info("Done. Created {$created} profiles.");
        return 0;
    }
}
