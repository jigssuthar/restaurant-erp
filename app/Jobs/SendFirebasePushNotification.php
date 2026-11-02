<?php

namespace App\Jobs;

use App\Models\Branch;
use App\Models\FirebaseDevice;
use App\Services\FirebaseMessaging;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendFirebasePushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $branchId,
        public array $roles,
        public string $title,
        public string $body,
        public string $url,
        public ?int $excludeUserId = null,
    ) {
        $this->afterCommit = true;
    }

    public function backoff(): array
    {
        return [30, 120];
    }

    public function handle(FirebaseMessaging $messaging): void
    {
        $branch = Branch::query()->findOrFail($this->branchId);
        $devices = FirebaseDevice::query()
            ->whereHas('user', function ($query) use ($branch) {
                $query->where('organization_id', $branch->organization_id)
                    ->whereIn('role', $this->roles)
                    ->where(function ($query) use ($branch) {
                        $query->whereNull('branch_id')
                            ->orWhere('branch_id', $branch->id);
                    })
                    ->when($this->excludeUserId, fn ($query) => $query->where('id', '!=', $this->excludeUserId));
            })
            ->get();

        if ($devices->isEmpty()) {
            Log::warning('Firebase push has no registered recipients for the requested branch and roles.', [
                'branch_id' => $this->branchId,
                'roles' => $this->roles,
            ]);

            return;
        }

        foreach ($devices as $device) {
            $messaging->send($device, [
                'title' => $this->title,
                'body' => $this->body,
                'url' => $this->url,
            ]);
        }
    }
}
