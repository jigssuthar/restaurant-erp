<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeAdminCommand extends Command
{
    protected $signature = 'admin:make-admin {email : Email address of the account to promote}';

    protected $description = 'Promote an existing account to a platform administrator';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No account was found with that email address.');

            return self::FAILURE;
        }

        if ($user->isPlatformAdmin()) {
            $this->info('This account is already a platform administrator.');

            return self::SUCCESS;
        }

        $user->update([
            'role' => 'admin',
            'organization_id' => null,
            'branch_id' => null,
        ]);

        $this->info("{$user->email} is now a platform administrator.");

        return self::SUCCESS;
    }
}
