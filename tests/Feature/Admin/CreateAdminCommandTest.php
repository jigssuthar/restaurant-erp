<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_an_admin_with_a_securely_prompted_password(): void
    {
        $this->artisan('admin:create-admin admin@example.com --name=Administrator')
            ->expectsQuestion('Password (minimum 12 characters)', 'StrongPassword123!')
            ->expectsQuestion('Confirm password', 'StrongPassword123!')
            ->expectsOutput('Administrator account created for admin@example.com.')
            ->assertExitCode(0);

        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->assertSame('Administrator', $admin->name);
        $this->assertSame('admin', $admin->role);
        $this->assertTrue(Hash::check('StrongPassword123!', $admin->password));
    }

    public function test_command_rejects_duplicate_email_addresses(): void
    {
        User::factory()->create(['email' => 'admin@example.com']);

        $this->artisan('admin:create-admin admin@example.com --name=Administrator')
            ->expectsOutput('An account already exists with that email address.')
            ->assertExitCode(1);
    }

    public function test_command_rejects_a_short_password(): void
    {
        $this->artisan('admin:create-admin admin@example.com --name=Administrator')
            ->expectsQuestion('Password (minimum 12 characters)', 'short')
            ->expectsQuestion('Confirm password', 'short')
            ->expectsOutput('Password must be at least 12 characters.')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['email' => 'admin@example.com']);
    }
}
