<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\ContactEnquiry;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Organization;
use App\Models\OrganizationSetting;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_populates_all_modules_and_is_repeatable(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1, Organization::count());
        $this->assertSame(2, Branch::count());
        $this->assertSame(1, OrganizationSetting::count());
        $this->assertSame(8, Category::count());
        $this->assertSame(22, Product::count());
        $this->assertSame(12, DiningTable::count());
        $this->assertSame(8, Order::count());
        $this->assertGreaterThan(0, OrderItem::count());
        $this->assertSame(2, ContactEnquiry::count());
        $this->assertSame(4, User::count());
        $this->assertSame(['manager', 'staff'], User::query()->distinct()->orderBy('role')->pluck('role')->all());
        $this->assertSame(2, Order::where('order_type', 'delivery')->count());
        $this->assertSame(2, Order::where('order_type', 'takeaway')->count());
        $this->assertSame(4, Order::where('order_type', 'dine_in')->count());
        $this->assertSame(12, DiningTable::whereNotNull('qr_token')->count());
    }
}
