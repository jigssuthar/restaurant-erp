<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Organization;
use App\Models\TableServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KitchenAndServiceAppTest extends TestCase
{
    use RefreshDatabase;

    public function test_kitchen_role_can_progress_orders_and_is_limited_to_its_branch(): void
    {
        [$organization, $branch, $table] = $this->restaurantWithTable('KITCHEN');
        [, $otherBranch, $otherTable] = $this->restaurantWithTable('OTHERKITCHEN');
        $order = $this->createOrder($branch, $table, 'KDS-MAIN');
        $otherOrder = $this->createOrder($otherBranch, $otherTable, 'KDS-OTHER');
        $chef = User::factory()->create([
            'role' => 'chef',
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);
        $this->actingAs($chef);

        $this->get(route('kitchen-display.index'))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertDontSee($otherOrder->order_number)
            ->assertDontSee('adminSidebar');
        $this->assertSame('/kitchen-display', route('kitchen-display.index', [], false));
        $this->get(route('admin.dashboard'))->assertForbidden();
        $this->get(route('service-app.index'))->assertForbidden();

        $this->patchJson(route('kitchen-display.orders.status', $order), ['status' => 'preparing'])
            ->assertOk()
            ->assertJsonPath('refresh', 'board');
        $this->patchJson(route('kitchen-display.orders.status', $order), ['status' => 'ready'])
            ->assertOk();
        $this->patchJson(route('kitchen-display.orders.status', $otherOrder), ['status' => 'preparing'])
            ->assertForbidden();

        $this->assertSame('ready', $order->fresh()->status);
    }

    public function test_service_role_can_resolve_qr_requests_and_serve_before_payment(): void
    {
        [$organization, $branch, $table] = $this->restaurantWithTable('SERVICE');
        $order = $this->createOrder($branch, $table, 'SERVICE-ORDER');
        $order->update(['status' => 'ready']);
        $serviceRequest = TableServiceRequest::create([
            'branch_id' => $branch->id,
            'dining_table_id' => $table->id,
            'request_type' => 'call_waiter',
            'status' => 'pending',
        ]);
        $waiter = User::factory()->create([
            'role' => 'waiter',
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);
        $this->actingAs($waiter);

        $this->get(route('service-app.index'))
            ->assertOk()
            ->assertSee('Table '.$table->table_number)
            ->assertSee('Call Waiter')
            ->assertSee($order->order_number)
            ->assertDontSee('adminSidebar');
        $this->assertSame('/service-app', route('service-app.index', [], false));

        $this->patchJson(route('service-app.requests.status', $serviceRequest), ['status' => 'acknowledged'])
            ->assertOk()
            ->assertJsonPath('refresh', 'board');
        $this->patchJson(route('service-app.requests.status', $serviceRequest), ['status' => 'completed'])
            ->assertOk();
        $this->patchJson(route('service-app.orders.status', $order), ['status' => 'completed'])
            ->assertOk();

        $this->assertSame('completed', $order->fresh()->status);
        $this->assertSame('pending', $order->fresh()->payment_status);
        $this->assertSame('occupied', $table->fresh()->status);
        $this->assertSame('completed', $serviceRequest->fresh()->status);

        $owner = User::factory()->create([
            'role' => 'admin',
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);
        $this->actingAs($owner)
            ->put(route('admin.dining-tables.update', $table), [
                'branch_id' => $branch->id,
                'table_number' => $table->table_number,
                'capacity' => $table->capacity,
                'status' => 'available',
            ])
            ->assertSessionHasErrors('status');

        $this->actingAs($waiter);
        $this->postJson(route('service-app.orders.payments.store', $order), [
            'payments' => [['payment_method' => 'cash', 'amount' => 100]],
        ])->assertOk();
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame('available', $table->fresh()->status);
    }

    public function test_guest_can_send_a_table_request_and_duplicate_open_requests_are_reused(): void
    {
        [, , $table] = $this->restaurantWithTable('QRREQUEST');
        $route = route('qr.service-requests.store', $table->qr_token);

        $this->post($route, ['request_type' => 'request_bill'])
            ->assertRedirect(route('qr.menu', $table->qr_token))
            ->assertSessionHas('qr_service_request_status');
        $this->post($route, ['request_type' => 'request_bill'])
            ->assertRedirect(route('qr.menu', $table->qr_token));

        $this->assertSame(1, TableServiceRequest::count());
        $this->assertDatabaseHas('table_service_requests', [
            'branch_id' => $table->branch_id,
            'dining_table_id' => $table->id,
            'request_type' => 'request_bill',
            'status' => 'pending',
        ]);
    }

    public function test_specialized_staff_login_lands_on_their_operational_screen(): void
    {
        [$organization, $branch] = $this->restaurantWithTable('LOGIN');
        $chef = User::factory()->create([
            'role' => 'chef',
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'email' => 'chef@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->post(route('login.store'), [
            'email' => $chef->email,
            'password' => 'password123',
        ])->assertRedirect(route('kitchen-display.index'));
    }

    public function test_operational_apps_are_not_listed_in_the_admin_dashboard_sidebar(): void
    {
        [$organization, $branch] = $this->restaurantWithTable('NAVIGATION');
        $admin = User::factory()->create([
            'role' => 'admin',
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Kitchen Display')
            ->assertDontSee('Service App');
    }

    private function restaurantWithTable(string $suffix): array
    {
        $organization = Organization::create([
            'name' => "Restaurant {$suffix}",
            'slug' => strtolower($suffix).'-'.uniqid(),
            'status' => 'active',
        ]);
        $branch = $organization->branches()->create([
            'name' => "{$suffix} Branch",
            'branch_code' => substr($suffix, 0, 10),
            'status' => 'active',
        ]);
        $table = DiningTable::create([
            'branch_id' => $branch->id,
            'table_number' => 'T1',
            'capacity' => 4,
            'status' => 'occupied',
        ]);

        return [$organization, $branch, $table];
    }

    private function createOrder(Branch $branch, DiningTable $table, string $number): Order
    {
        $order = Order::create([
            'branch_id' => $branch->id,
            'dining_table_id' => $table->id,
            'order_number' => $number,
            'order_type' => 'dine_in',
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 100,
            'total' => 100,
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_name' => 'Sample dish',
            'quantity' => 2,
            'unit_price' => 50,
            'line_total' => 100,
        ]);

        return $order;
    }
}
