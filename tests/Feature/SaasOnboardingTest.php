<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\DiningTable;
use App\Models\Organization;
use App\Models\SaasPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SaasOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_a_scoped_restaurant_and_30_day_trial(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Restaurant Owner',
            'email' => 'owner@example.test',
            'organization_name' => 'Owner Restaurant',
            'branch_name' => 'Downtown',
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
        ])->assertRedirect(route('admin.dashboard'));

        $owner = User::where('email', 'owner@example.test')->firstOrFail();
        $this->assertSame('admin', $owner->role);
        $this->assertNotNull($owner->organization_id);
        $this->assertSame('Downtown', $owner->branch->name);
        $this->assertSame($owner->organization_id, $owner->branch->organization_id);
        $this->assertDatabaseHas('organization_settings', ['organization_id' => $owner->organization_id]);

        $subscription = $owner->organization->currentSubscription;
        $this->assertSame('trialing', $subscription->status);
        $this->assertTrue($subscription->trial_ends_at->isFuture());
        $this->assertEqualsWithDelta(30, now()->diffInDays($subscription->trial_ends_at), 1);
        $this->assertFalse($owner->isPlatformAdmin());
    }

    public function test_tenant_admin_cannot_access_platform_organizations_or_other_tenant_staff(): void
    {
        $organization = $this->createOrganization('Tenant One');
        $otherOrganization = $this->createOrganization('Tenant Two');
        $owner = User::factory()->create([
            'role' => 'admin',
            'organization_id' => $organization->id,
        ]);
        User::factory()->create([
            'name' => 'Private Tenant User',
            'role' => 'staff',
            'organization_id' => $otherOrganization->id,
        ]);
        User::factory()->create([
            'name' => 'Tenant One Staff',
            'role' => 'staff',
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($owner)
            ->get(route('admin.organizations.index'))
            ->assertForbidden();

        $this->get(route('admin.staff.index'))
            ->assertOk()
            ->assertSee('Tenant One Staff')
            ->assertDontSee('Private Tenant User');
    }

    public function test_expired_trial_locks_workspace_but_keeps_billing_access(): void
    {
        $organization = $this->createOrganization('Expired Tenant');
        $owner = User::factory()->create([
            'role' => 'admin',
            'organization_id' => $organization->id,
        ]);
        $organization->currentSubscription->update(['trial_ends_at' => now()->subMinute()]);

        $this->actingAs($owner)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.billing.index'));

        $this->get(route('admin.billing.index'))
            ->assertOk()
            ->assertSee('Your trial remains available');
    }

    public function test_plan_branch_limit_is_enforced_on_creation(): void
    {
        $organization = $this->createOrganization('Limited Tenant');
        $owner = User::factory()->create([
            'role' => 'admin',
            'organization_id' => $organization->id,
        ]);
        $plan = SaasPlan::create([
            'name' => 'One Branch',
            'slug' => 'one-branch',
            'price' => null,
            'currency' => 'INR',
            'billing_interval' => 'monthly',
            'max_branches' => 1,
        ]);
        $subscription = $organization->currentSubscription;
        $subscription->update(['saas_plan_id' => $plan->id]);
        Branch::create([
            'organization_id' => $organization->id,
            'name' => 'Existing',
            'branch_code' => 'EXISTING',
            'status' => 'active',
        ]);

        $this->actingAs($owner)
            ->from(route('admin.branches.create'))
            ->post(route('admin.branches.store'), [
                'organization_id' => $organization->id,
                'name' => 'Extra',
                'branch_code' => 'EXTRA',
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.branches.create'))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('branches', ['branch_code' => 'EXTRA']);
    }

    public function test_expired_subscription_blocks_public_table_qr_menu(): void
    {
        $organization = $this->createOrganization('QR Tenant');
        $branch = Branch::create([
            'organization_id' => $organization->id,
            'name' => 'Main',
            'branch_code' => 'MAIN',
            'status' => 'active',
        ]);
        $table = DiningTable::create([
            'branch_id' => $branch->id,
            'table_number' => '1',
            'capacity' => 4,
            'status' => 'available',
        ]);
        $organization->currentSubscription->update(['trial_ends_at' => now()->subMinute()]);

        $this->get(route('qr.menu', $table->qr_token))->assertForbidden();
    }

    public function test_platform_admin_can_save_an_unpriced_draft_plan(): void
    {
        $platformAdmin = User::factory()->create([
            'role' => 'admin',
            'organization_id' => null,
            'branch_id' => null,
        ]);

        $this->actingAs($platformAdmin)
            ->post(route('admin.saas-plans.store'), [
                'name' => 'Draft plan',
                'currency' => 'INR',
                'billing_interval' => 'monthly',
                'features_text' => "QR ordering\nBasic reports",
                'sort_order' => 0,
            ])
            ->assertRedirect(route('admin.saas-plans.index'));

        $this->assertDatabaseHas('saas_plans', [
            'slug' => 'draft-plan',
            'price' => null,
            'razorpay_plan_id' => null,
            'is_active' => false,
        ]);
    }

    public function test_verified_razorpay_checkout_signature_activates_and_records_subscription(): void
    {
        config([
            'services.razorpay.key' => 'local-test-key',
            'services.razorpay.secret' => 'local-test-secret',
        ]);
        Http::fake([
            'https://api.razorpay.com/v1/payments/pay_test123' => Http::response([
                'id' => 'pay_test123',
                'subscription_id' => 'sub_test123',
                'amount' => 100,
                'currency' => 'INR',
                'status' => 'authorized',
                'method' => 'upi',
                'created_at' => now()->timestamp,
            ]),
        ]);
        $organization = $this->createOrganization('Billing Tenant');
        $owner = User::factory()->create([
            'role' => 'admin',
            'organization_id' => $organization->id,
        ]);
        $plan = SaasPlan::create([
            'name' => 'Monthly',
            'slug' => 'monthly',
            'price' => 499,
            'currency' => 'INR',
            'billing_interval' => 'monthly',
            'razorpay_plan_id' => 'plan_test123',
        ]);
        $subscription = $organization->currentSubscription;
        $subscription->update([
            'saas_plan_id' => $plan->id,
            'provider_subscription_id' => 'sub_test123',
        ]);
        $paymentId = 'pay_test123';
        $signature = hash_hmac('sha256', 'sub_test123|'.$paymentId, 'local-test-secret');

        $this->actingAs($owner)
            ->postJson(route('admin.billing.verify'), [
                'razorpay_subscription_id' => 'sub_test123',
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
            ])
            ->assertRedirect(route('admin.billing.index'));

        $this->assertSame('active', $subscription->fresh()->status);
        $this->assertDatabaseHas('billing_transactions', [
            'organization_subscription_id' => $subscription->id,
            'provider_payment_id' => $paymentId,
            'amount' => 1,
            'status' => 'authorized',
        ]);
    }

    public function test_checkout_refuses_a_razorpay_plan_with_a_different_amount(): void
    {
        config([
            'services.razorpay.key' => 'test-key',
            'services.razorpay.secret' => 'test-secret',
        ]);
        Http::fake([
            'https://api.razorpay.com/v1/plans/plan_test123' => Http::response([
                'period' => 'monthly',
                'interval' => 1,
                'item' => ['amount' => 99900, 'currency' => 'INR'],
            ]),
        ]);

        $organization = $this->createOrganization('Price Mismatch Tenant');
        $owner = User::factory()->create([
            'role' => 'admin',
            'organization_id' => $organization->id,
        ]);
        $plan = SaasPlan::create([
            'name' => 'Monthly',
            'slug' => 'monthly-mismatch',
            'price' => 499,
            'currency' => 'INR',
            'billing_interval' => 'monthly',
            'razorpay_plan_id' => 'plan_test123',
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->from(route('admin.billing.index'))
            ->post(route('admin.billing.subscribe', $plan))
            ->assertRedirect(route('admin.billing.index'))
            ->assertSessionHasErrors('plan');

        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/v1/subscriptions'));
    }

    public function test_checkout_creates_and_reuses_the_existing_pending_razorpay_subscription(): void
    {
        config([
            'services.razorpay.key' => 'test-key',
            'services.razorpay.secret' => 'test-secret',
        ]);
        Http::fake([
            'https://api.razorpay.com/v1/plans/plan_checkout123' => Http::response([
                'period' => 'monthly',
                'interval' => 1,
                'item' => ['amount' => 49900, 'currency' => 'INR'],
            ]),
            'https://api.razorpay.com/v1/subscriptions' => Http::response(['id' => 'sub_checkout123']),
        ]);

        $organization = $this->createOrganization('Checkout Tenant');
        $owner = User::factory()->create([
            'role' => 'admin',
            'organization_id' => $organization->id,
        ]);
        $plan = SaasPlan::create([
            'name' => 'Monthly',
            'slug' => 'checkout-monthly',
            'price' => 499,
            'currency' => 'INR',
            'billing_interval' => 'monthly',
            'razorpay_plan_id' => 'plan_checkout123',
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->post(route('admin.billing.subscribe', $plan))
            ->assertOk()
            ->assertSee('sub_checkout123');
        $this->post(route('admin.billing.subscribe', $plan))
            ->assertOk()
            ->assertSee('sub_checkout123');

        $this->assertSame(
            'sub_checkout123',
            $organization->currentSubscription->fresh()->provider_subscription_id
        );
        Http::assertSentCount(3);
        Http::assertSent(fn ($request) => str_contains($request->url(), '/v1/subscriptions'));
    }

    public function test_cancellation_is_scheduled_with_razorpay_without_immediate_access_revocation(): void
    {
        config([
            'services.razorpay.key' => 'test-key',
            'services.razorpay.secret' => 'test-secret',
        ]);
        Http::fake([
            'https://api.razorpay.com/v1/subscriptions/sub_cancel123/cancel' => Http::response([
                'id' => 'sub_cancel123',
                'status' => 'active',
            ]),
        ]);

        $organization = $this->createOrganization('Cancellation Tenant');
        $owner = User::factory()->create([
            'role' => 'admin',
            'organization_id' => $organization->id,
        ]);
        $subscription = $organization->currentSubscription;
        $subscription->update([
            'status' => 'active',
            'provider_subscription_id' => 'sub_cancel123',
            'current_period_end' => now()->addMonth(),
        ]);

        $this->actingAs($owner)
            ->post(route('admin.billing.cancel'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue($subscription->fresh()->cancel_at_period_end);
        $this->assertTrue($subscription->fresh()->grantsAccess());
    }

    public function test_razorpay_webhook_requires_and_accepts_a_valid_signature_without_csrf(): void
    {
        config(['services.razorpay.webhook_secret' => 'webhook-test-secret']);
        $organization = $this->createOrganization('Webhook Tenant');
        $subscription = $organization->currentSubscription;
        $subscription->update(['provider_subscription_id' => 'sub_webhook123']);
        $payload = [
            'event' => 'subscription.activated',
            'payload' => [
                'subscription' => [
                    'entity' => [
                        'id' => 'sub_webhook123',
                        'current_start' => now()->timestamp,
                        'current_end' => now()->addMonth()->timestamp,
                    ],
                ],
            ],
        ];
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha256', $body, 'webhook-test-secret');

        $this->call('POST', route('razorpay.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_RAZORPAY_SIGNATURE' => $signature,
        ], $body)->assertOk();

        $this->assertSame('active', $subscription->fresh()->status);
    }

    public function test_razorpay_webhook_rejects_an_invalid_signature(): void
    {
        config(['services.razorpay.webhook_secret' => 'webhook-test-secret']);

        $this->call('POST', route('razorpay.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_RAZORPAY_SIGNATURE' => 'invalid',
        ], '{"event":"subscription.activated"}')->assertForbidden();
    }

    public function test_older_razorpay_lifecycle_webhook_does_not_revert_subscription_state(): void
    {
        config(['services.razorpay.webhook_secret' => 'webhook-test-secret']);
        $organization = $this->createOrganization('Ordered Webhook Tenant');
        $subscription = $organization->currentSubscription;
        $subscription->update([
            'provider_subscription_id' => 'sub_ordered123',
            'status' => 'active',
            'last_webhook_at' => now(),
        ]);
        $body = json_encode([
            'event' => 'subscription.halted',
            'created_at' => now()->subDay()->timestamp,
            'payload' => ['subscription' => ['entity' => ['id' => 'sub_ordered123']]],
        ], JSON_THROW_ON_ERROR);

        $this->call('POST', route('razorpay.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_RAZORPAY_SIGNATURE' => hash_hmac('sha256', $body, 'webhook-test-secret'),
        ], $body)->assertOk();

        $this->assertSame('active', $subscription->fresh()->status);
    }

    private function createOrganization(string $name): Organization
    {
        return Organization::create([
            'name' => $name,
            'slug' => str($name)->slug().'-'.uniqid(),
            'status' => 'active',
        ]);
    }
}
