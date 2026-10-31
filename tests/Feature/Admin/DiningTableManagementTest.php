<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\DiningTable;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiningTableManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_a_dining_table(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $branch = $this->createBranch('MAIN');

        $this->post(route('admin.dining-tables.store'), [
            'branch_id' => $branch->id,
            'table_number' => 'T-01',
            'capacity' => 4,
            'status' => 'available',
        ])->assertRedirect(route('admin.dining-tables.index'));

        $table = DiningTable::firstOrFail();
        $this->get(route('admin.dining-tables.show', $table))
            ->assertOk()
            ->assertSee('T-01')
            ->assertSee('4 guests');

        $this->put(route('admin.dining-tables.update', $table), [
            'branch_id' => $branch->id,
            'table_number' => 'T-02',
            'capacity' => 6,
            'status' => 'reserved',
        ])->assertRedirect(route('admin.dining-tables.index'));

        $this->assertDatabaseHas('dining_tables', [
            'id' => $table->id,
            'table_number' => 'T-02',
            'capacity' => 6,
            'status' => 'reserved',
        ]);

        $this->delete(route('admin.dining-tables.destroy', $table))
            ->assertRedirect(route('admin.dining-tables.index'));
        $this->assertDatabaseMissing('dining_tables', ['id' => $table->id]);
    }

    public function test_duplicate_table_numbers_are_rejected_within_a_branch(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $branch = $this->createBranch('MAIN');
        $branch->diningTables()->create([
            'table_number' => 'T-01',
            'capacity' => 4,
            'status' => 'available',
        ]);

        $this->from(route('admin.dining-tables.create'))
            ->post(route('admin.dining-tables.store'), [
                'branch_id' => $branch->id,
                'table_number' => 'T-01',
                'capacity' => 4,
                'status' => 'available',
            ])
            ->assertSessionHasErrors('table_number');

        $this->assertDatabaseCount('dining_tables', 1);
    }

    public function test_table_actions_and_bootstrap_pagination_are_rendered(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $branch = $this->createBranch('MAIN');

        foreach (range(1, 11) as $number) {
            $branch->diningTables()->create([
                'table_number' => sprintf('T-%02d', $number),
                'capacity' => 4,
                'status' => 'available',
            ]);
        }

        $indexUrl = route('admin.dining-tables.index');
        $latestTable = DiningTable::orderByDesc('id')->firstOrFail();
        $this->get($indexUrl)
            ->assertOk()
            ->assertSee('data-bs-toggle="dropdown"', false)
            ->assertSee(route('admin.dining-tables.show', $latestTable), false)
            ->assertSee(route('admin.dining-tables.edit', $latestTable), false)
            ->assertSee('class="pagination"', false)
            ->assertSee($indexUrl.'?page=2', false);

        $this->get($indexUrl.'?page=2')
            ->assertOk()
            ->assertSee('T-01');
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
}
