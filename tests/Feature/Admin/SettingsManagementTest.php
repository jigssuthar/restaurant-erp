<?php

namespace Tests\Feature\Admin;

use App\Models\Organization;
use App\Models\OrganizationSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_organization_settings(): void
    {
        $organization = $this->createOrganization('Settings Restaurant');
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('Organization-wide pricing');

        $this->put(route('admin.settings.update'), [
            'organization_id' => $organization->id,
            'currency_code' => 'usd',
            'tax_label' => 'VAT',
            'tax_rate' => '5.00',
            'service_charge_rate' => '10.00',
            'delivery_fee' => '25.00',
            'receipt_header' => 'Welcome',
            'receipt_footer' => 'Thank you',
        ])->assertRedirect(route('admin.settings.index', ['organization_id' => $organization->id]));

        $this->assertDatabaseHas('organization_settings', [
            'organization_id' => $organization->id,
            'currency_code' => 'USD',
            'tax_label' => 'VAT',
            'tax_rate' => 5,
            'service_charge_rate' => 10,
            'delivery_fee' => 25,
            'receipt_header' => 'Welcome',
            'receipt_footer' => 'Thank you',
        ]);
    }

    public function test_manager_can_change_only_their_organization_settings(): void
    {
        $organization = $this->createOrganization('Manager Restaurant');
        $otherOrganization = $this->createOrganization('Other Restaurant');
        $manager = User::factory()->create([
            'role' => 'manager',
            'organization_id' => $organization->id,
        ]);
        $this->actingAs($manager);

        $this->get(route('admin.settings.index', ['organization_id' => $otherOrganization->id]))
            ->assertOk()
            ->assertSee('Manager Restaurant')
            ->assertDontSee('Other Restaurant');

        $this->put(route('admin.settings.update'), [
            'organization_id' => $otherOrganization->id,
            'currency_code' => 'INR',
            'tax_rate' => 0,
            'service_charge_rate' => 0,
            'delivery_fee' => 0,
            'receipt_header' => '',
            'receipt_footer' => '',
        ])->assertSessionHasErrors('organization_id');

        $this->assertDatabaseMissing('organization_settings', [
            'organization_id' => $otherOrganization->id,
        ]);
    }

    public function test_staff_cannot_access_settings(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'staff']));

        $this->get(route('admin.settings.index'))->assertForbidden();
    }

    private function createOrganization(string $name): Organization
    {
        return Organization::create([
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)).'-'.uniqid(),
            'status' => 'active',
        ]);
    }
}
