<?php

namespace Tests\Feature;

use App\Jobs\SendFirebasePushNotification;
use App\Models\FirebaseDevice;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\RestaurantNotification;
use App\Services\FirebaseMessaging;
use App\Services\FirebasePush;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class FirebasePushTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_register_and_remove_their_browser_token(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        $this->actingAs($firstUser)
            ->postJson(route('notifications.firebase.device.store'), ['token' => 'browser-token'])
            ->assertOk();

        $device = FirebaseDevice::query()->where('token', 'browser-token')->firstOrFail();
        $this->assertSame($firstUser->id, $device->user_id);

        $this->actingAs($secondUser)
            ->postJson(route('notifications.firebase.device.store'), ['token' => 'browser-token'])
            ->assertOk();
        $this->assertSame($secondUser->id, $device->fresh()->user_id);

        $this->actingAs($firstUser)
            ->deleteJson(route('notifications.firebase.device.destroy'), ['token' => 'browser-token'])
            ->assertOk();
        $this->assertDatabaseHas('firebase_devices', [
            'id' => $device->id,
            'user_id' => $secondUser->id,
        ]);

        $this->actingAs($secondUser)
            ->deleteJson(route('notifications.firebase.device.destroy'), ['token' => 'browser-token'])
            ->assertOk();
        $this->assertDatabaseMissing('firebase_devices', ['token' => 'browser-token']);
    }

    public function test_firebase_service_worker_is_served_with_a_root_scope(): void
    {
        config(['services.firebase.web' => [
            'apiKey' => 'public-api-key',
            'projectId' => 'restaurant-project',
        ]]);

        $this->get(route('firebase.messaging-sw'))
            ->assertOk()
            ->assertHeader('Service-Worker-Allowed', '/')
            ->assertHeader('Content-Type', 'application/javascript; charset=UTF-8')
            ->assertSee('firebase.messaging()')
            ->assertSee('restaurant-project');
    }

    public function test_guests_cannot_register_browser_tokens(): void
    {
        $this->postJson(route('notifications.firebase.device.store'), ['token' => 'guest-token'])
            ->assertUnauthorized();

        $this->assertDatabaseMissing('firebase_devices', ['token' => 'guest-token']);
    }

    public function test_logout_removes_the_current_users_browser_token(): void
    {
        $user = User::factory()->create();
        $user->firebaseDevices()->create(['token' => 'logout-token']);

        $this->actingAs($user)
            ->post(route('admin.logout'), ['firebase_device_token' => 'logout-token'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseMissing('firebase_devices', ['token' => 'logout-token']);
    }

    public function test_push_jobs_only_send_to_matching_branch_roles_and_skip_the_actor(): void
    {
        $organization = Organization::create([
            'name' => 'Push Restaurant',
            'slug' => 'push-restaurant',
            'status' => 'active',
        ]);
        $branch = $organization->branches()->create([
            'name' => 'Main Branch',
            'branch_code' => 'PUSH',
            'status' => 'active',
        ]);

        $owner = User::factory()->create([
            'role' => 'admin',
            'organization_id' => $organization->id,
            'branch_id' => null,
        ]);
        $waiter = User::factory()->create([
            'role' => 'waiter',
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);
        $actor = User::factory()->create([
            'role' => 'manager',
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);
        $otherBranchUser = User::factory()->create([
            'role' => 'waiter',
            'organization_id' => $organization->id,
            'branch_id' => $organization->branches()->create([
                'name' => 'Other Branch',
                'branch_code' => 'PUSH2',
                'status' => 'active',
            ])->id,
        ]);
        $otherRoleUser = User::factory()->create([
            'role' => 'chef',
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);

        foreach ([
            [$owner, 'owner-token'],
            [$waiter, 'waiter-token'],
            [$actor, 'actor-token'],
            [$otherBranchUser, 'other-branch-token'],
            [$otherRoleUser, 'other-role-token'],
        ] as [$user, $token]) {
            $user->firebaseDevices()->create(['token' => $token]);
        }

        $sentTokens = [];
        $messaging = Mockery::mock(FirebaseMessaging::class);
        $messaging->shouldReceive('send')
            ->twice()
            ->andReturnUsing(function (FirebaseDevice $device) use (&$sentTokens) {
                $sentTokens[] = $device->token;
            });

        (new SendFirebasePushNotification(
            $branch->id,
            ['admin', 'manager', 'waiter'],
            'New order',
            'Order received',
            '/service-app',
            $actor->id,
        ))->handle($messaging);

        $this->assertEqualsCanonicalizing(['owner-token', 'waiter-token'], $sentTokens);
    }

    public function test_push_job_logs_when_no_matching_browser_is_registered(): void
    {
        $organization = Organization::create([
            'name' => 'No Devices Restaurant',
            'slug' => 'no-devices-restaurant',
            'status' => 'active',
        ]);
        $branch = $organization->branches()->create([
            'name' => 'Main Branch',
            'branch_code' => 'NODEVICE',
            'status' => 'active',
        ]);
        Log::shouldReceive('warning')
            ->once()
            ->with(
                'Firebase push has no registered recipients for the requested branch and roles.',
                ['branch_id' => $branch->id, 'roles' => ['admin', 'manager']],
            );
        $messaging = Mockery::mock(FirebaseMessaging::class);
        $messaging->shouldNotReceive('send');

        (new SendFirebasePushNotification(
            $branch->id,
            ['admin', 'manager'],
            'Test',
            'Test',
            '/admin',
        ))->handle($messaging);
    }

    public function test_firebase_push_fails_explicitly_when_server_credentials_are_unset(): void
    {
        config(['services.firebase.credentials' => null]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('FIREBASE_CREDENTIALS is not configured');

        app(FirebaseMessaging::class)->send(new FirebaseDevice(['token' => 'test-token']), [
            'title' => 'Test',
            'body' => 'Test',
            'url' => '/admin',
        ]);
    }

    public function test_ordering_workflows_queue_branch_notifications(): void
    {
        Queue::fake();
        $organization = Organization::create([
            'name' => 'Queue Restaurant',
            'slug' => 'queue-restaurant',
            'status' => 'active',
        ]);
        $branch = $organization->branches()->create([
            'name' => 'Main Branch',
            'branch_code' => 'QUEUE',
            'status' => 'active',
        ]);
        $recipient = User::factory()->create([
            'role' => 'manager',
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);
        $actor = User::factory()->create([
            'role' => 'admin',
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);

        app(FirebasePush::class)->notifyBranch(
            $branch->id,
            ['admin', 'manager'],
            'Low inventory',
            'Milk is low.',
            '/admin/inventory',
            $actor->id,
        );

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $recipient->id,
            'notifiable_type' => User::class,
        ]);
        $this->assertSame('Low inventory', $recipient->notifications()->firstOrFail()->data['title']);
        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $actor->id,
            'notifiable_type' => User::class,
        ]);

        Queue::assertPushed(SendFirebasePushNotification::class, function ($job) use ($branch) {
            return $job->branchId === $branch->id
                && $job->title === 'Low inventory'
                && $job->url === '/admin/inventory';
        });
    }

    public function test_bell_lists_only_the_current_users_notifications_and_can_mark_them_read(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $notification = new RestaurantNotification(
            'New order',
            'Order ORD-1 is waiting.',
            '/kitchen-display',
        );
        $user->notify($notification);
        $otherUser->notify($notification);
        $userNotification = $user->notifications()->firstOrFail();

        $this->actingAs($user)
            ->getJson(route('notifications.index'))
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('notifications.0.title', 'New order')
            ->assertJsonPath('notifications.0.url', '/kitchen-display')
            ->assertJsonMissing(['title' => 'Other user notification']);

        $this->patchJson(route('notifications.read', $userNotification->id))
            ->assertOk();
        $this->assertNotNull($userNotification->fresh()->read_at);

        $otherNotification = $otherUser->notifications()->firstOrFail();
        $this->patchJson(route('notifications.read', $otherNotification->id))
            ->assertNotFound();

        $this->actingAs($user)
            ->patchJson(route('notifications.read-all'))
            ->assertOk()
            ->assertJsonPath('message', 'All notifications marked as read.');
    }
}
