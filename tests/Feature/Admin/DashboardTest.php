<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Organization;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_live_counts_for_existing_modules(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $organization = Organization::create([
            'name' => 'Dashboard Restaurant',
            'slug' => 'dashboard-restaurant',
            'status' => 'active',
        ]);
        $branch = $organization->branches()->create([
            'name' => 'Main Branch',
            'branch_code' => 'MAIN',
            'status' => 'active',
        ]);
        $category = Category::create([
            'branch_id' => $branch->id,
            'name' => 'Meals',
            'slug' => 'meals',
            'is_active' => true,
        ]);
        Product::create([
            'branch_id' => $branch->id,
            'category_id' => $category->id,
            'name' => 'Special',
            'price' => 100,
            'cost_price' => 50,
            'type' => 'food',
            'is_available' => true,
        ]);
        $branch->diningTables()->create([
            'table_number' => 'T-1',
            'capacity' => 4,
            'status' => 'available',
        ]);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Organizations')
            ->assertSee('Branches')
            ->assertSee('Categories')
            ->assertSee('Products')
            ->assertSee('Dining Tables')
            ->assertSee('Sales')
            ->assertSee('Recent orders')
            ->assertSee('No orders yet.');
    }
}
