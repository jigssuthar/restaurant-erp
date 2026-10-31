<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Organization;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_view_and_delete_a_product(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $branch = $this->createBranch('MAIN');
        $category = $this->createCategory($branch, 'Meals');

        $this->post(route('admin.products.store'), [
            'branch_id' => $branch->id,
            'category_id' => $category->id,
            'name' => 'Lunch Special',
            'sku' => 'LUNCH-01',
            'description' => 'Daily lunch menu',
            'price' => '125.00',
            'cost_price' => '60.00',
            'type' => 'food',
            'is_available' => '1',
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::firstOrFail();
        $this->get(route('admin.products.show', $product))
            ->assertOk()
            ->assertSee('Lunch Special')
            ->assertSee('Daily lunch menu');

        $this->put(route('admin.products.update', $product), [
            'branch_id' => $branch->id,
            'category_id' => $category->id,
            'name' => 'Dinner Special',
            'sku' => 'LUNCH-01',
            'price' => '150',
            'cost_price' => '75',
            'type' => 'food',
            'is_available' => '0',
        ])->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Dinner Special',
            'price' => 150,
            'is_available' => false,
        ]);

        $this->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_product_category_must_belong_to_the_selected_branch(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $branch = $this->createBranch('MAIN');
        $otherBranch = $this->createBranch('OTHER');
        $category = $this->createCategory($otherBranch, 'Other category');

        $this->from(route('admin.products.create'))
            ->post(route('admin.products.store'), [
                'branch_id' => $branch->id,
                'category_id' => $category->id,
                'name' => 'Invalid product',
                'price' => '10',
                'cost_price' => '5',
                'type' => 'food',
                'is_available' => '1',
            ])
            ->assertSessionHasErrors('category_id');

        $this->assertDatabaseCount('products', 0);
    }

    private function createBranch(string $code): Branch
    {
        $organization = Organization::create([
            'name' => 'Test Restaurant',
            'slug' => uniqid('test-restaurant-'),
            'status' => 'active',
        ]);

        return $organization->branches()->create([
            'name' => $code.' Branch',
            'branch_code' => $code,
            'status' => 'active',
        ]);
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
