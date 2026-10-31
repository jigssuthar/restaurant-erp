<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\DiningTable;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Organization;
use App\Models\Order;
use App\Models\PurchaseOrder;
use App\Models\Reservation;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationsModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_events_are_created_through_ajax_and_tenant_scoped(): void
    {
        [$user, $branch] = $this->restaurant();
        $otherBranch = $this->restaurant()[1];

        $this->actingAs($user)
            ->postJson(route('admin.calendar.store'), [
                'branch_id' => $branch->id,
                'title' => 'Private dining evening',
                'event_type' => 'private_event',
                'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
                'ends_at' => now()->addDay()->addHours(3)->format('Y-m-d H:i:s'),
                'status' => 'scheduled',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Event created.');

        $this->assertDatabaseHas('restaurant_events', [
            'branch_id' => $branch->id,
            'title' => 'Private dining evening',
            'all_day' => false,
        ]);

        $this->actingAs($user)
            ->postJson(route('admin.calendar.store'), [
                'branch_id' => $otherBranch->id,
                'title' => 'Other tenant event',
                'event_type' => 'general',
                'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
                'ends_at' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
                'status' => 'scheduled',
            ])
            ->assertForbidden();
    }

    public function test_reservations_reject_overlapping_table_bookings(): void
    {
        [$user, $branch] = $this->restaurant();
        $table = DiningTable::create([
            'branch_id' => $branch->id,
            'table_number' => 'A1',
            'capacity' => 4,
            'status' => 'available',
        ]);
        $start = now()->addDay()->startOfHour();
        $attributes = [
            'branch_id' => $branch->id,
            'dining_table_id' => $table->id,
            'customer_name' => 'Guest One',
            'customer_phone' => '1234567890',
            'party_size' => 3,
            'starts_at' => $start->format('Y-m-d H:i:s'),
            'ends_at' => $start->copy()->addHours(2)->format('Y-m-d H:i:s'),
        ];

        $this->actingAs($user)
            ->postJson(route('admin.reservations.store'), $attributes)
            ->assertCreated();

        $this->actingAs($user)
            ->postJson(route('admin.reservations.store'), [
                ...$attributes,
                'customer_name' => 'Guest Two',
                'starts_at' => $start->copy()->addHour()->format('Y-m-d H:i:s'),
                'ends_at' => $start->copy()->addHours(3)->format('Y-m-d H:i:s'),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('dining_table_id');

        $this->assertSame(1, Reservation::count());
    }

    public function test_receiving_a_purchase_order_updates_stock_once_and_records_movement(): void
    {
        [$user, $branch, $organization] = $this->restaurant();
        $supplier = Supplier::create([
            'organization_id' => $organization->id,
            'name' => 'Produce supplier',
            'is_active' => true,
        ]);
        $item = InventoryItem::create([
            'branch_id' => $branch->id,
            'name' => 'Tomatoes',
            'unit' => 'kg',
            'quantity_on_hand' => 5,
            'reorder_level' => 2,
            'unit_cost' => 30,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->postJson(route('admin.purchase-orders.store'), [
                'branch_id' => $branch->id,
                'supplier_id' => $supplier->id,
                'items' => [[
                    'inventory_item_id' => $item->id,
                    'quantity' => 3,
                    'unit_cost' => 35,
                ]],
            ])
            ->assertCreated();
        $purchaseOrder = PurchaseOrder::firstOrFail();
        $this->get(route('admin.purchase-orders.show', $purchaseOrder))
            ->assertOk()
            ->assertSee('Tomatoes');

        $this->patchJson(route('admin.purchase-orders.status', $purchaseOrder), ['status' => 'ordered'])
            ->assertOk();
        $this->patchJson(route('admin.purchase-orders.status', $purchaseOrder), ['status' => 'received'])
            ->assertOk();
        $this->patchJson(route('admin.purchase-orders.status', $purchaseOrder), ['status' => 'received'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');

        $this->assertSame(8.0, (float) $item->fresh()->quantity_on_hand);
        $this->assertSame(1, InventoryMovement::where('reference_id', $purchaseOrder->id)->count());
    }

    public function test_inventory_adjustments_cannot_make_stock_negative(): void
    {
        [$user, $branch] = $this->restaurant();
        $item = InventoryItem::create([
            'branch_id' => $branch->id,
            'name' => 'Milk',
            'unit' => 'litre',
            'quantity_on_hand' => 2,
            'reorder_level' => 1,
            'unit_cost' => 25,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->patchJson(route('admin.inventory.adjust', $item), [
                'movement_type' => 'out',
                'quantity' => 3,
                'reason' => 'Waste',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('quantity');

        $this->assertSame(2.0, (float) $item->fresh()->quantity_on_hand);
        $this->assertDatabaseMissing('inventory_movements', ['inventory_item_id' => $item->id]);
    }

    public function test_order_status_and_payment_actions_return_ajax_responses(): void
    {
        [$user, $branch] = $this->restaurant();
        $order = Order::create([
            'branch_id' => $branch->id,
            'order_number' => 'AJAX-ORDER-1',
            'order_type' => 'dine_in',
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 100,
            'total' => 100,
        ]);
        $this->actingAs($user);

        $this->patchJson(route('admin.orders.status', $order), ['status' => 'preparing'])
            ->assertOk()
            ->assertJsonPath('refresh', true);
        $this->patchJson(route('admin.orders.payment', $order), ['payment_method' => 'cash'])
            ->assertOk()
            ->assertJsonPath('message', 'Payment recorded.');
        $this->patchJson(route('admin.orders.status', $order), ['status' => 'cancelled'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'preparing',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
        ]);
    }

    public function test_new_module_screens_render_for_the_restaurant_admin(): void
    {
        [$user, $branch, $organization] = $this->restaurant();
        $item = InventoryItem::create([
            'branch_id' => $branch->id,
            'name' => 'Cooking oil',
            'unit' => 'litre',
            'quantity_on_hand' => 1,
            'reorder_level' => 2,
            'unit_cost' => 100,
            'is_active' => true,
        ]);
        $supplier = Supplier::create([
            'organization_id' => $organization->id,
            'name' => 'Kitchen supplier',
            'is_active' => true,
        ]);

        $this->actingAs($user);
        $this->get(route('admin.calendar.index'))->assertOk()->assertSee('Calendar & Events');
        $this->get(route('admin.calendar.create'))->assertOk()->assertSee('Add calendar event');
        $this->get(route('admin.reservations.index'))->assertOk()->assertSee('Reservations');
        $this->get(route('admin.reservations.create'))->assertOk()->assertSee('New reservation');
        $this->get(route('admin.inventory.index'))->assertOk()->assertSee('Stock history');
        $this->get(route('admin.inventory.create'))->assertOk()->assertSee('Opening quantity');
        $this->get(route('admin.inventory.edit', $item))->assertOk()->assertSee('Edit inventory item');
        $this->get(route('admin.inventory.movements', $item))->assertOk()->assertSee('Stock movement history');
        $this->get(route('admin.suppliers.index'))->assertOk()->assertSee('Kitchen supplier');
        $this->get(route('admin.suppliers.create'))->assertOk()->assertSee('Add supplier');
        $this->get(route('admin.suppliers.edit', $supplier))->assertOk()->assertSee('Edit supplier');
        $this->get(route('admin.purchase-orders.index'))->assertOk()->assertSee('New purchase order');
        $this->get(route('admin.purchase-orders.create'))->assertOk()->assertSee('New purchase order');
        $this->get(route('admin.reports.index'))->assertOk()->assertSee('Advanced Reports');
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Upcoming Reservations')->assertSee('Low-stock Items');
    }

    private function restaurant(): array
    {
        $organization = Organization::create([
            'name' => 'Operations Restaurant '.uniqid(),
            'slug' => 'operations-'.uniqid(),
            'status' => 'active',
        ]);
        $branch = $organization->branches()->create([
            'name' => 'Main Branch',
            'branch_code' => 'MAIN',
            'status' => 'active',
        ]);
        $user = User::factory()->create([
            'role' => 'admin',
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);

        return [$user, $branch, $organization];
    }
}
