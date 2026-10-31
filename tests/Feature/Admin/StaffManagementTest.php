<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_staff_with_role_and_branch_assignment(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        [$organization, $branch] = $this->createOrganizationWithBranch('MAIN');

        $this->post(route('admin.staff.store'), [
            'name' => 'Restaurant Manager',
            'email' => 'manager@example.com',
            'role' => 'manager',
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'password' => 'password12345',
            'password_confirmation' => 'password12345',
        ])->assertRedirect(route('admin.staff.index'));

        $user = User::where('email', 'manager@example.com')->firstOrFail();
        $this->assertSame('manager', $user->role);
        $this->assertSame($branch->id, $user->branch_id);
        $this->assertTrue(Hash::check('password12345', $user->password));
    }

    public function test_staff_are_limited_to_read_only_access_for_their_assigned_branch(): void
    {
        [$organization, $branch] = $this->createOrganizationWithBranch('MAIN');
        [, $otherBranch] = $this->createOrganizationWithBranch('OTHER');
        $assignedCategory = $this->createCategory($branch, 'Assigned menu');
        $otherCategory = $this->createCategory($otherBranch, 'Private menu');
        $staff = User::factory()->create([
            'role' => 'staff',
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);
        $this->actingAs($staff);

        $this->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('Assigned menu')
            ->assertDontSee('Private menu');

        $this->get(route('admin.categories.show', $otherCategory))->assertForbidden();
        $this->get(route('admin.organizations.index'))->assertForbidden();
        $this->post(route('admin.categories.store'), [
            'branch_id' => $branch->id,
            'name' => 'Unauthorized',
            'is_active' => '1',
        ])->assertForbidden();
    }

    public function test_manager_can_only_access_data_in_their_organization(): void
    {
        [$organization, $branch] = $this->createOrganizationWithBranch('MAIN');
        [, $otherBranch] = $this->createOrganizationWithBranch('OTHER');
        $this->createCategory($branch, 'Manager menu');
        $otherCategory = $this->createCategory($otherBranch, 'Other organization menu');
        $manager = User::factory()->create([
            'role' => 'manager',
            'organization_id' => $organization->id,
        ]);
        $this->actingAs($manager);

        $this->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('Manager menu')
            ->assertDontSee('Other organization menu');

        $this->get(route('admin.categories.show', $otherCategory))->assertForbidden();
        $this->get(route('admin.organizations.index'))->assertForbidden();
    }

    public function test_last_admin_cannot_be_demoted_or_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $organization = $this->createOrganizationWithBranch('MAIN')[0];
        $this->actingAs($admin);

        $this->from(route('admin.staff.edit', $admin))
            ->put(route('admin.staff.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'manager',
                'organization_id' => $organization->id,
                'branch_id' => '',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertSessionHasErrors('role');

        $this->delete(route('admin.staff.destroy', $admin))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
    }

    public function test_admin_bootstrap_command_promotes_a_named_account(): void
    {
        $user = User::factory()->create([
            'email' => 'bootstrap@example.com',
            'role' => 'staff',
        ]);

        $this->assertSame(0, Artisan::call('admin:make-admin', ['email' => $user->email]));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'admin']);
    }

    public function test_platform_promotion_removes_tenant_scope(): void
    {
        $organization = $this->createOrganizationWithBranch('TENANT')[0];
        $user = User::factory()->create([
            'email' => 'tenant-owner@example.com',
            'role' => 'manager',
            'organization_id' => $organization->id,
        ]);

        $this->assertSame(0, Artisan::call('admin:make-admin', ['email' => $user->email]));

        $user->refresh();
        $this->assertTrue($user->isPlatformAdmin());
        $this->assertNull($user->organization_id);
        $this->assertNull($user->branch_id);
    }

    private function createOrganizationWithBranch(string $code): array
    {
        $organization = Organization::create([
            'name' => "Restaurant {$code}",
            'slug' => strtolower("restaurant-{$code}"),
            'status' => 'active',
        ]);
        $branch = $organization->branches()->create([
            'name' => "{$code} Branch",
            'branch_code' => $code,
            'status' => 'active',
        ]);

        return [$organization, $branch];
    }

    private function createCategory(Branch $branch, string $name): Category
    {
        return Category::create([
            'branch_id' => $branch->id,
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)),
            'is_active' => true,
        ]);
    }
}
