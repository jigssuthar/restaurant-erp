<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ScopesBranchData;
use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use ScopesBranchData;

    public function index(Request $request)
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);
        $branchIds = $this->branchQuery()->select('id');
        if (! empty($filters['branch_id'])) {
            $this->ensureBranchAccess(\App\Models\Branch::findOrFail($filters['branch_id']));
        }
        $orders = Order::whereIn('branch_id', $branchIds)
            ->when(! empty($filters['branch_id']), fn ($query) => $query->where('branch_id', $filters['branch_id']))
            ->when(! empty($filters['from']), fn ($query) => $query->whereDate('created_at', '>=', $filters['from']))
            ->when(! empty($filters['to']), fn ($query) => $query->whereDate('created_at', '<=', $filters['to']));
        $orderCount = (clone $orders)->count();
        $cancelledCount = (clone $orders)->where('status', 'cancelled')->count();
        $sales = (clone $orders)->joinPaymentTotals()
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('orders.currency_code, SUM('.Order::NET_PAYMENT_SQL.') as total')
            ->groupBy('orders.currency_code')
            ->havingRaw('SUM('.Order::NET_PAYMENT_SQL.') > 0')
            ->get();
        $statusCounts = (clone $orders)->selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status');
        $topItems = OrderItem::query()
            ->selectRaw('product_name, SUM(quantity) as quantity_sold')
            ->whereHas('order', fn ($query) => $query->whereIn('branch_id', $branchIds)
                ->where('status', '!=', 'cancelled')
                ->when(! empty($filters['branch_id']), fn ($query) => $query->where('branch_id', $filters['branch_id']))
                ->when(! empty($filters['from']), fn ($query) => $query->whereDate('created_at', '>=', $filters['from']))
                ->when(! empty($filters['to']), fn ($query) => $query->whereDate('created_at', '<=', $filters['to'])))
            ->groupBy('product_name')
            ->orderByDesc('quantity_sold')
            ->limit(10)
            ->get();
        $lowStock = $this->scopeToAccessibleBranches(InventoryItem::query())
            ->where('is_active', true)
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->with('branch')
            ->orderBy('quantity_on_hand')
            ->limit(20)
            ->get();
        $branches = $this->branchQuery()->orderBy('name')->get(['id', 'name']);

        return view('admin.reports.index', compact(
            'filters', 'orderCount', 'cancelledCount', 'sales', 'statusCounts', 'topItems', 'lowStock', 'branches'
        ));
    }
}
