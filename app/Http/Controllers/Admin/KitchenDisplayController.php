<?php

namespace App\Http\Controllers\Admin;

use App\Actions\TransitionRestaurantOrder;
use App\Http\Controllers\Admin\Concerns\ScopesBranchData;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class KitchenDisplayController extends Controller
{
    use ScopesBranchData;

    public function index(Request $request)
    {
        $board = $this->boardData($request);

        return view('admin.kitchen-display.index', $board);
    }

    public function feed(Request $request)
    {
        return view('admin.kitchen-display.partials.board', $this->boardData($request));
    }

    public function updateStatus(Request $request, Order $order, TransitionRestaurantOrder $transition)
    {
        $this->ensureBranchAccess($order->branch);
        $data = $request->validate([
            'status' => ['required', 'in:preparing,ready'],
        ]);

        if (! \in_array($data['status'], ['preparing', 'ready'], true)) {
            throw ValidationException::withMessages([
                'status' => 'The kitchen can only start preparation or mark an order ready.',
            ]);
        }

        $transition->handle($order, $data['status']);

        return response()->json([
            'message' => 'Kitchen order updated.',
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

        $orders = $branch
            ? Order::query()
                ->where('branch_id', $branch->id)
                ->where(function ($query) {
                    $query->whereIn('status', ['pending', 'preparing', 'ready'])
                        ->orWhere(function ($query) {
                            $query->where('status', 'completed')
                                ->whereDate('updated_at', today());
                        });
                })
                ->with(['items', 'diningTable'])
                ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'preparing' THEN 1 WHEN 'ready' THEN 2 ELSE 3 END")
                ->oldest('created_at')
                ->limit(100)
                ->get()
            : collect();

        $counts = $branch
            ? Order::query()
                ->where('branch_id', $branch->id)
                ->where(function ($query) {
                    $query->whereIn('status', ['pending', 'preparing', 'ready'])
                        ->orWhere(function ($query) {
                            $query->where('status', 'completed')
                                ->whereDate('updated_at', today());
                        });
                })
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
            : collect();

        return [
            'branches' => $branches,
            'branch' => $branch,
            'ordersByStatus' => $orders->groupBy('status'),
            'statusCounts' => $counts,
            'lastUpdated' => now(),
        ];
    }
}
