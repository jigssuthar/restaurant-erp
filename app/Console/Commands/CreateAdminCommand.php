<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    protected $signature = 'admin:create-admin {email : Email address for the administrator} {--name= : Administrator name}';

    protected $description = 'Create an administrator account with a securely prompted password';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));

        if (! Validator::make(['email' => $email], ['email' => ['required', 'email', 'max:255']])->passes()) {
            $this->error('Enter a valid email address.');

            return self::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->error('An account already exists with that email address.');

            return self::FAILURE;
        }

        $name = trim((string) $this->option('name'));
        if ($name === '') {
            $name = trim((string) $this->ask('Administrator name'));
        }

        if ($name === '' || mb_strlen($name) > 255) {
            $this->error('Administrator name is required and must be 255 characters or fewer.');

            return self::FAILURE;
        }

        $password = $this->secret('Password (minimum 12 characters)');
        $confirmation = $this->secret('Confirm password');

        if (! \is_string($password) || mb_strlen($password) < 12) {
            $this->error('Password must be at least 12 characters.');

            return self::FAILURE;
        }

        if (! \is_string($confirmation) || ! hash_equals($password, $confirmation)) {
            $this->error('Password confirmation does not match.');

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
        ]);

        $this->info("Administrator account created for {$email}.");

        return self::SUCCESS;
    }
}
