<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Category;
use App\Models\CashRegisterSession;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\Organization;
use App\Models\OrganizationSetting;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_process_a_dine_in_order(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        [$branch, $product, $table] = $this->createMenuAndTable('MAIN');

        $this->post(route('admin.orders.store'), [
            'branch_id' => $branch->id,
            'order_type' => 'dine_in',
            'dining_table_id' => $table->id,
            'customer_name' => 'Alex',
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'payment_status' => 'pending',
        ])->assertRedirect();

        $order = Order::with('items')->firstOrFail();
        $this->assertSame('pending', $order->status);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('150.00', $order->total);
        $this->assertSame('Special meal', $order->items->first()->product_name);
        $this->assertSame('75.00', $order->items->first()->unit_price);
        $this->assertDatabaseHas('dining_tables', ['id' => $table->id, 'status' => 'occupied']);

        $this->patch(route('admin.orders.status', $order), ['status' => 'completed'])
            ->assertSessionHasErrors('status');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);

        $this->patch(route('admin.orders.payment', $order), ['payment_method' => 'cash'])
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->patch(route('admin.orders.status', $order), ['status' => 'cancelled'])
            ->assertSessionHasErrors('status');
        $this->patch(route('admin.orders.status', $order), ['status' => 'preparing'])->assertRedirect();
        $this->patch(route('admin.orders.status', $order), ['status' => 'ready'])->assertRedirect();
        $this->patch(route('admin.orders.status', $order), ['status' => 'completed'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
        ]);
        $this->assertDatabaseHas('dining_tables', ['id' => $table->id, 'status' => 'available']);
    }

    public function test_order_totals_use_server_prices_and_delivery_requires_contact_details(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        [$branch, $product] = $this->createMenuAndTable('MAIN');

        $this->from(route('admin.orders.create'))
            ->post(route('admin.orders.store'), [
                'branch_id' => $branch->id,
                'order_type' => 'delivery',
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
                'payment_status' => 'pending',
            ])
            ->assertSessionHasErrors(['customer_name', 'delivery_address']);
        $this->assertDatabaseCount('orders', 0);

        $this->post(route('admin.orders.store'), [
            'branch_id' => $branch->id,
            'order_type' => 'takeaway',
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
            'payment_status' => 'paid',
            'payment_method' => 'upi',
        ])->assertRedirect();

        $product->update(['price' => 999]);
        $order = Order::with('items')->firstOrFail();
        $this->assertSame('225.00', $order->total);
        $this->assertSame('75.00', $order->items->first()->unit_price);
        $this->assertNotNull($order->invoice_number);
        $this->assertDatabaseHas('order_payments', [
            'order_id' => $order->id,
            'transaction_type' => 'payment',
            'payment_method' => 'upi',
            'amount' => '225.00',
        ]);
    }

    public function test_pos_applies_discount_before_tax_and_records_invoice(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        [$branch, $product] = $this->createMenuAndTable('POSDISC');
        OrganizationSetting::create([
            'organization_id' => $branch->organization_id,
            'tax_label' => 'GST',
            'tax_rate' => 5,
            'service_charge_rate' => 10,
        ]);

        $this->post(route('admin.orders.store'), [
            'branch_id' => $branch->id,
            'order_type' => 'takeaway',
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'payment_status' => 'pending',
            'discount_type' => 'percentage',
            'discount_value' => 10,
        ])->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame('150.00', $order->subtotal);
        $this->assertSame('15.00', $order->discount_amount);
        $this->assertSame('6.75', $order->tax_amount);
        $this->assertSame('13.50', $order->service_charge);
        $this->assertSame('155.25', $order->total);
        $this->assertNull($order->invoice_number);
    }

    public function test_pos_supports_split_partial_payment_and_refund_limits(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        [$branch, $product] = $this->createMenuAndTable('POSSPLIT');
        $this->post(route('admin.orders.store'), [
            'branch_id' => $branch->id,
            'order_type' => 'takeaway',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payment_status' => 'pending',
        ])->assertRedirect();
        $order = Order::firstOrFail();

        $this->postJson(route('admin.orders.payments.store', $order), [
            'payments' => [['payment_method' => 'cash', 'amount' => 75.01]],
        ])->assertUnprocessable()->assertJsonValidationErrors('payments');
        $this->assertDatabaseCount('order_payments', 0);

        $this->postJson(route('admin.orders.payments.store', $order), [
            'payments' => [['payment_method' => 'cash', 'amount' => 25]],
        ])->assertOk()->assertJsonPath('refresh', true);
        $this->assertSame('partial', $order->fresh()->payment_status);
        $this->assertNotNull($order->fresh()->invoice_number);

        $this->postJson(route('admin.orders.payments.store', $order), [
            'payments' => [
                ['payment_method' => 'cash', 'amount' => 10],
                ['payment_method' => 'upi', 'amount' => 40],
            ],
        ])->assertOk();
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame('multiple', $order->fresh()->payment_method);
        $paidAt = $order->fresh()->paid_at;
        $this->assertNotNull($paidAt);
        $this->assertSame(3, OrderPayment::where('order_id', $order->id)->where('transaction_type', 'payment')->count());

        $this->postJson(route('admin.orders.refund', $order), [
            'payment_method' => 'upi',
            'amount' => 20,
            'notes' => 'Guest returned one item',
        ])->assertOk()->assertJsonPath('refresh', true);
        $this->assertSame('partially_refunded', $order->fresh()->payment_status);
        $this->assertEquals($paidAt, $order->fresh()->paid_at);
        $this->assertSame(0.0, (float) $order->fresh()->total - (float) OrderPayment::where('order_id', $order->id)
            ->where('transaction_type', 'payment')->sum('amount'));

        $this->postJson(route('admin.orders.refund', $order), [
            'payment_method' => 'upi',
            'amount' => 56,
            'notes' => 'Over-refund attempt',
        ])->assertUnprocessable()->assertJsonValidationErrors('amount');

        $this->assertSame(1, OrderPayment::where('order_id', $order->id)->where('transaction_type', 'refund')->count());
        $this->get(route('admin.reports.index'))
            ->assertOk()
            ->assertViewHas('sales', fn ($sales) => $sales->contains(fn ($sale) => (float) $sale->total === 55.0));
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('INR 55.00');
    }

    public function test_pos_orders_can_be_held_and_resumed(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        [$branch, $product] = $this->createMenuAndTable('POSHOLD');

        $this->post(route('admin.orders.store'), [
            'branch_id' => $branch->id,
            'order_type' => 'takeaway',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payment_status' => 'pending',
            'hold_order' => 1,
        ])->assertRedirect();
        $order = Order::firstOrFail();
        $this->assertSame('held', $order->status);

        $this->patch(route('admin.orders.status', $order), ['status' => 'pending'])->assertRedirect();
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_cash_register_closing_reconciles_opening_float_and_cash_tenders(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        [$branch, $product] = $this->createMenuAndTable('POSCASH');
        $this->post(route('admin.cash-register.open'), [
            'branch_id' => $branch->id,
            'opening_float' => 100,
        ])->assertRedirect();
        $session = CashRegisterSession::firstOrFail();
        $this->from(route('admin.cash-register.index', ['branch_id' => $branch->id]))
            ->post(route('admin.cash-register.open'), [
                'branch_id' => $branch->id,
                'opening_float' => 100,
            ])
            ->assertSessionHasErrors('branch_id');
        $this->assertDatabaseCount('cash_register_sessions', 1);

        $this->post(route('admin.orders.store'), [
            'branch_id' => $branch->id,
            'order_type' => 'takeaway',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payment_status' => 'paid',
            'payment_method' => 'cash',
        ])->assertRedirect();

        $payment = OrderPayment::where('transaction_type', 'payment')->firstOrFail();
        $this->assertSame($session->id, $payment->cash_register_session_id);

        $this->patchJson(route('admin.cash-register.close', $session), [
            'counted_cash' => 173,
            'closing_notes' => 'Till counted',
        ])->assertOk()->assertJsonPath('expected_cash', 175)->assertJsonPath('difference', -2);
        $this->assertNotNull($session->fresh()->closed_at);
        $this->assertSame('175.00', $session->fresh()->expected_cash);
        $this->assertSame('-2.00', $session->fresh()->cash_difference);
    }

    public function test_order_snapshots_organization_tax_fees_and_currency(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        [$branch, $product] = $this->createMenuAndTable('MAIN');
        OrganizationSetting::create([
            'organization_id' => $branch->organization_id,
            'currency_code' => 'USD',
            'tax_label' => 'VAT',
            'tax_rate' => 5,
            'service_charge_rate' => 10,
            'delivery_fee' => 30,
        ]);

        $this->post(route('admin.orders.store'), [
            'branch_id' => $branch->id,
            'order_type' => 'delivery',
            'customer_name' => 'Sam',
            'delivery_address' => '1 Main Street',
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'payment_status' => 'pending',
        ])->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame('USD', $order->currency_code);
        $this->assertSame('150.00', $order->subtotal);
        $this->assertSame('7.50', $order->tax_amount);
        $this->assertSame('15.00', $order->service_charge);
        $this->assertSame('30.00', $order->delivery_fee);
        $this->assertSame('202.50', $order->total);

        OrganizationSetting::where('organization_id', $branch->organization_id)
            ->update(['currency_code' => 'EUR', 'tax_rate' => 20]);

        $order->refresh();
        $this->assertSame('USD', $order->currency_code);
        $this->assertSame('7.50', $order->tax_amount);
        $this->assertSame('202.50', $order->total);
    }

    public function test_staff_can_create_orders_only_for_their_assigned_branch(): void
    {
        [$branch, $product] = $this->createMenuAndTable('MAIN');
        [$otherBranch, $otherProduct] = $this->createMenuAndTable('OTHER');
        $staff = User::factory()->create([
            'role' => 'staff',
            'organization_id' => $branch->organization_id,
            'branch_id' => $branch->id,
        ]);
        $this->actingAs($staff);

        $this->post(route('admin.orders.store'), [
            'branch_id' => $branch->id,
            'order_type' => 'takeaway',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payment_status' => 'pending',
        ])->assertRedirect();
        $this->assertDatabaseCount('orders', 1);

        $this->post(route('admin.orders.store'), [
            'branch_id' => $otherBranch->id,
            'order_type' => 'takeaway',
            'items' => [['product_id' => $otherProduct->id, 'quantity' => 1]],
            'payment_status' => 'pending',
        ])->assertForbidden();
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_guest_can_order_from_the_qr_code_for_the_correct_table(): void
    {
        [$branch, $product, $table] = $this->createMenuAndTable('QRMAIN');
        OrganizationSetting::create([
            'organization_id' => $branch->organization_id,
            'currency_code' => 'INR',
            'tax_label' => 'GST',
            'tax_rate' => 5,
            'service_charge_rate' => 10,
        ]);

        $this->get(route('qr.menu', $table->qr_token))
            ->assertOk()
            ->assertSee('Table '.$table->table_number)
            ->assertSee($product->name)
            ->assertSee('selected');

        $this->post(route('qr.orders.store', $table->qr_token), [
            'customer_name' => 'QR Guest',
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ])->assertRedirect(route('qr.menu', $table->qr_token))
            ->assertSessionHas('qr_order_confirmation.order_number');

        $order = Order::with('items')->firstOrFail();
        $this->assertNull($order->user_id);
        $this->assertSame($branch->id, $order->branch_id);
        $this->assertSame($table->id, $order->dining_table_id);
        $this->assertSame('dine_in', $order->order_type);
        $this->assertSame('pending', $order->payment_status);
        $this->assertNull($order->payment_method);
        $this->assertSame('7.50', $order->tax_amount);
        $this->assertSame('15.00', $order->service_charge);
        $this->assertSame('172.50', $order->total);
        $this->assertSame('occupied', $table->fresh()->status);

        $this->get(route('qr.menu', $table->qr_token))
            ->assertOk()
            ->assertSee('Your order is confirmed')
            ->assertSee($order->order_number)
            ->assertSee('Special meal')
            ->assertSee('QR Guest')
            ->assertSee('GST')
            ->assertSee('172.50')
            ->assertSee('Payment due at restaurant')
            ->assertSee('2')
            ->assertDontSee('items[0][product_id]');
    }

    public function test_qr_orders_can_be_added_to_occupied_tables_and_one_cancellation_does_not_free_it(): void
    {
        [$branch, $product, $table] = $this->createMenuAndTable('QRMULTI');

        foreach (range(1, 2) as $orderNumber) {
            $this->post(route('qr.orders.store', $table->qr_token), [
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
            ])->assertRedirect();
        }

        $orders = Order::orderBy('id')->get();
        $this->assertCount(2, $orders);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->patch(route('admin.orders.status', $orders->first()), ['status' => 'cancelled'])
            ->assertRedirect();
        $this->assertSame('occupied', $table->fresh()->status);

        $this->patch(route('admin.orders.status', $orders->last()), ['status' => 'cancelled'])
            ->assertRedirect();
        $this->assertSame('available', $table->fresh()->status);
    }

    public function test_qr_order_rejects_unavailable_products_and_invalid_or_rotated_tokens(): void
    {
        [$branch, $product, $table] = $this->createMenuAndTable('QRINVALID');

        $this->get(route('qr.menu', ['token' => str_repeat('x', 64)]))->assertNotFound();

        $product->update(['is_available' => false]);
        $this->from(route('qr.menu', $table->qr_token))
            ->post(route('qr.orders.store', $table->qr_token), [
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
            ])
            ->assertSessionHasErrors('items.0.product_id');
        $this->assertDatabaseCount('orders', 0);

        $product->update(['is_available' => true]);
        $product->category->update(['is_active' => false]);
        $this->post(route('qr.orders.store', $table->qr_token), [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertSessionHasErrors('items');
        $this->assertDatabaseCount('orders', 0);

        $product->category->update(['is_active' => true]);
        $this->actingAs(User::factory()->create([
            'role' => 'staff',
            'organization_id' => $branch->organization_id,
            'branch_id' => $branch->id,
        ]))
            ->post(route('admin.dining-tables.qr.rotate', $table))
            ->assertForbidden();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('admin.dining-tables.qr.rotate', $table))
            ->assertRedirect();

        $this->get(route('qr.menu', $table->qr_token))->assertNotFound();
        $this->get(route('qr.menu', $table->fresh()->qr_token))->assertOk();
    }

    public function test_qr_orders_are_blocked_when_the_table_is_reserved_or_under_maintenance(): void
    {
        [, , $table] = $this->createMenuAndTable('QRSTATUS');

        $table->update(['status' => 'reserved']);
        $this->get(route('qr.menu', $table->qr_token))->assertNotFound();

        $table->update(['status' => 'maintenance']);
        $this->post(route('qr.orders.store', $table->qr_token), [
            'items' => [['product_id' => 1, 'quantity' => 1]],
        ])->assertNotFound();
        $this->assertDatabaseCount('orders', 0);
    }

    private function createMenuAndTable(string $code): array
    {
        $organization = Organization::create([
            'name' => "Restaurant {$code}",
            'slug' => strtolower('restaurant-'.$code.'-'.uniqid()),
            'status' => 'active',
        ]);
        $branch = $organization->branches()->create([
            'name' => "{$code} Branch",
            'branch_code' => $code,
            'status' => 'active',
        ]);
        $category = Category::create([
            'branch_id' => $branch->id,
            'name' => 'Meals',
            'slug' => 'meals',
            'is_active' => true,
        ]);
        $product = Product::create([
            'branch_id' => $branch->id,
            'category_id' => $category->id,
            'name' => 'Special meal',
            'price' => 75,
            'cost_price' => 35,
            'type' => 'food',
            'is_available' => true,
        ]);
        $table = DiningTable::create([
            'branch_id' => $branch->id,
            'table_number' => 'T-1',
            'capacity' => 4,
            'status' => 'available',
        ]);

        return [$branch, $product, $table];
    }
}
