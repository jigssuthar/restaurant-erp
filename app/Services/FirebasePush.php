<?php

namespace App\Services;

use App\Jobs\SendFirebasePushNotification;
use App\Models\Branch;
use App\Models\User;
use App\Notifications\RestaurantNotification;

class FirebasePush
{
    public function notifyBranch(
        int $branchId,
        array $roles,
        string $title,
        string $body,
        string $url,
        ?int $excludeUserId = null,
    ): void {
        $branch = Branch::query()->findOrFail($branchId);
        User::query()
            ->where('organization_id', $branch->organization_id)
            ->whereIn('role', $roles)
            ->where(function ($query) use ($branch) {
                $query->whereNull('branch_id')
                    ->orWhere('branch_id', $branch->id);
            })
            ->when($excludeUserId, fn ($query) => $query->where('id', '!=', $excludeUserId))
            ->each(fn (User $user) => $user->notify(new RestaurantNotification($title, $body, $url)));

        SendFirebasePushNotification::dispatch(
            $branchId,
            $roles,
            $title,
            $body,
            $url,
            $excludeUserId,
        )->afterCommit();
    }
}
