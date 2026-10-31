<?php

namespace App\Http\Controllers\Admin;

use App\Actions\TransitionRestaurantOrder;
use App\Http\Controllers\Admin\Concerns\ScopesBranchData;
use App\Http\Controllers\Controller;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\TableServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ServiceAppController extends Controller
{
    use ScopesBranchData;

    public function index(Request $request)
    {
        return view('admin.service-app.index', $this->boardData($request));
    }

    public function feed(Request $request)
    {
        return view('admin.service-app.partials.board', $this->boardData($request));
    }

    public function updateOrderStatus(
        Request $request,
        Order $order,
        TransitionRestaurantOrder $transition
    ) {
        $this->ensureBranchAccess($order->branch);
        $request->validate(['status' => ['required', 'in:completed']]);
        $transition->handle($order, 'completed');

        return response()->json([
            'message' => 'Order marked as served.',
            'refresh' => 'board',
        ]);
    }

    public function updateServiceRequest(Request $request, TableServiceRequest $serviceRequest)
    {
        $this->ensureBranchAccess($serviceRequest->branch);
        $data = $request->validate([
            'status' => ['required', 'in:acknowledged,completed'],
        ]);

        DB::transaction(function () use ($request, $data, $serviceRequest) {
            $lockedRequest = TableServiceRequest::query()
                ->whereKey($serviceRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedRequest->status === 'completed'
                || ($data['status'] === 'acknowledged' && $lockedRequest->status !== 'pending')
                || ($data['status'] === 'completed' && ! \in_array($lockedRequest->status, ['pending', 'acknowledged'], true))) {
                throw ValidationException::withMessages([
                    'status' => 'This table request has already been updated.',
                ]);
            }

            if ($data['status'] === 'acknowledged') {
                $lockedRequest->update([
                    'status' => 'acknowledged',
                    'acknowledged_by' => $request->user()->id,
                    'acknowledged_at' => now(),
                ]);
            } else {
                $lockedRequest->update([
                    'status' => 'completed',
                    'completed_by' => $request->user()->id,
                    'completed_at' => now(),
                    'acknowledged_by' => $lockedRequest->acknowledged_by ?? $request->user()->id,
                    'acknowledged_at' => $lockedRequest->acknowledged_at ?? now(),
                ]);
            }
        });

        return response()->json([
            'message' => $data['status'] === 'completed' ? 'Table request completed.' : 'Table request acknowledged.',
            'refresh' => 'board',
        ]);
    }

    private function boardData(Request $request): array
    {
        $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        $branches = $this->branchQuery()->orderBy('name')->get(['id', 'name']);
        $branch = $request->filled('branch_id')
            ? $this->branchQuery()->whereKey($request->integer('branch_id'))->firstOrFail()
            : $branches->first();

        $tables = $branch
            ? DiningTable::query()
                ->where('branch_id', $branch->id)
                ->with([
                    'orders' => fn ($query) => $query
                        ->where(function ($query) {
                            $query->whereIn('status', ['pending', 'preparing', 'ready'])
                                ->orWhere(function ($query) {
                                    $query->where('status', 'completed')
                                        ->whereIn('payment_status', ['pending', 'partial']);
                                });
                        })
                        ->with(['items', 'payments'])
                        ->oldest(),
                    'serviceRequests' => fn ($query) => $query
                        ->whereIn('status', ['pending', 'acknowledged'])
                        ->oldest(),
                ])
                ->orderBy('table_number')
                ->get()
            : collect();

        $allRequests = $tables->flatMap(fn ($table) => $table->serviceRequests);

        return [
            'branches' => $branches,
            'branch' => $branch,
            'tables' => $tables,
            'pendingRequestCount' => $allRequests->where('status', 'pending')->count(),
            'readyOrderCount' => $tables->sum(fn ($table) => $table->orders->where('status', 'ready')->count()),
            'lastUpdated' => now(),
        ];
    }
}
