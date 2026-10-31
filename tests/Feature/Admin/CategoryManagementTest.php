<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Organization;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_view_categories(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $branch = $this->createBranch();

        $this->post(route('admin.categories.store'), [
            'branch_id' => $branch->id,
            'name' => 'Hot Drinks',
            'description' => 'Freshly prepared drinks',
            'is_active' => '1',
        ])->assertRedirect(route('admin.categories.index'));

        $category = Category::firstOrFail();
        $this->assertSame('hot-drinks', $category->slug);

        $this->get(route('admin.categories.show', $category))
            ->assertOk()
            ->assertSee('Hot Drinks')
            ->assertSee('Freshly prepared drinks');

        $this->put(route('admin.categories.update', $category), [
            'branch_id' => $branch->id,
            'name' => 'Cold Drinks',
            'is_active' => '0',
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Cold Drinks',
            'slug' => 'cold-drinks',
            'is_active' => false,
        ]);
    }

    public function test_duplicate_category_names_receive_unique_branch_scoped_slugs(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $branch = $this->createBranch();
        $payload = ['branch_id' => $branch->id, 'name' => 'Snacks', 'is_active' => '1'];

        $this->post(route('admin.categories.store'), $payload)->assertRedirect(route('admin.categories.index'));
        $this->post(route('admin.categories.store'), $payload)->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', ['branch_id' => $branch->id, 'slug' => 'snacks']);
        $this->assertDatabaseHas('categories', ['branch_id' => $branch->id, 'slug' => 'snacks-2']);
    }

    public function test_category_cannot_be_deleted_while_products_are_assigned(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $branch = $this->createBranch();
        $category = Category::create([
            'branch_id' => $branch->id,
            'name' => 'Meals',
            'slug' => 'meals',
            'is_active' => true,
        ]);
        Product::create([
            'branch_id' => $branch->id,
            'category_id' => $category->id,
            'name' => 'Lunch',
            'price' => 100,
            'cost_price' => 50,
            'type' => 'food',
            'is_available' => true,
        ]);

        $this->delete(route('admin.categories.destroy', $category))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    private function createBranch(): Branch
    {
        $organization = Organization::create([
            'name' => 'Test Restaurant',
            'slug' => uniqid('test-restaurant-'),
            'status' => 'active',
        ]);

        return $organization->branches()->create([
            'name' => 'Main Branch',
            'branch_code' => 'MAIN',
            'status' => 'active',
        ]);
    }
}
