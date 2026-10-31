<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_admin_can_create_update_and_view_a_branch(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $organization = $this->createOrganization();

        $this->post(route('admin.branches.store'), [
            'organization_id' => $organization->id,
            'name' => 'Downtown',
            'branch_code' => 'dt-01',
            'email' => 'downtown@example.com',
            'phone' => '1234567890',
            'city' => 'Ahmedabad',
            'state' => 'Gujarat',
            'pincode' => '380001',
            'status' => 'active',
        ])->assertRedirect(route('admin.branches.index'));

        $branch = Branch::firstOrFail();
        $this->assertSame('DT-01', $branch->branch_code);

        $this->get(route('admin.branches.show', $branch))
            ->assertOk()
            ->assertSee('Downtown')
            ->assertSee('Ahmedabad');

        $this->put(route('admin.branches.update', $branch), [
            'organization_id' => $organization->id,
            'name' => 'Downtown Updated',
            'branch_code' => 'dt-01',
            'status' => 'inactive',
        ])->assertRedirect(route('admin.branches.index'));

        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'name' => 'Downtown Updated',
            'branch_code' => 'DT-01',
            'status' => 'inactive',
        ]);
    }

    public function test_branch_codes_must_be_unique_within_their_organization(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $organization = $this->createOrganization();
        $organization->branches()->create([
            'name' => 'Existing',
            'branch_code' => 'main',
            'status' => 'active',
        ]);

        $this->from(route('admin.branches.create'))
            ->post(route('admin.branches.store'), [
                'organization_id' => $organization->id,
                'name' => 'Duplicate',
                'branch_code' => 'MAIN',
                'status' => 'active',
            ])
            ->assertSessionHasErrors('branch_code');

        $this->assertDatabaseCount('branches', 1);
    }

    public function test_branch_listing_can_be_filtered_and_dependent_branches_cannot_be_deleted(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $organization = $this->createOrganization();
        $branch = $organization->branches()->create([
            'name' => 'Central',
            'branch_code' => 'CENTRAL',
            'status' => 'active',
        ]);
        Category::create([
            'branch_id' => $branch->id,
            'name' => 'Drinks',
            'slug' => 'drinks',
            'is_active' => true,
        ]);

        $this->get(route('admin.branches.index', [
            'search' => 'Central',
            'organization_id' => $organization->id,
            'status' => 'active',
        ]))
            ->assertOk()
            ->assertSee('Central')
            ->assertSee($organization->name);

        $this->delete(route('admin.branches.destroy', $branch))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('branches', ['id' => $branch->id]);
    }

    public function test_empty_branch_can_be_deleted(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $branch = $this->createOrganization()->branches()->create([
            'name' => 'Temporary',
            'branch_code' => 'TEMP',
            'status' => 'inactive',
        ]);

        $this->delete(route('admin.branches.destroy', $branch))
            ->assertRedirect(route('admin.branches.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('branches', ['id' => $branch->id]);
    }

    private function createOrganization(): Organization
    {
        return Organization::create([
            'name' => 'Test Restaurant',
            'slug' => uniqid('test-restaurant-'),
            'status' => 'active',
        ]);
    }
}
