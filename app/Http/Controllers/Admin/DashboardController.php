<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Organization;
use App\Models\Order;
use App\Models\InventoryItem;
use App\Models\Reservation;
use App\Models\RestaurantEvent;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $user = auth()->user();
        $isPlatformAdmin = $user->isPlatformAdmin();
        $branchRoute = $user->hasAnyRole('admin', 'manager') ? 'admin.branches.index' : null;
        $staffRoute = $user->hasAnyRole('admin') ? 'admin.staff.index' : null;
        $organizationRoute = $isPlatformAdmin ? 'admin.organizations.index' : null;
        $branchQuery = Branch::query();

        if ($user->hasAnyRole('admin', 'manager') && ! $isPlatformAdmin) {
            $branchQuery->where('organization_id', $user->organization_id ?? 0);
        } elseif ($user->hasAnyRole('staff')) {
            $branchQuery->whereKey($user->branch_id ?? 0);
        }

        $branchIds = $branchQuery->select('id');
        $organizationId = $user->organization_id ?? $user->branch?->organization_id;
        $userQuery = User::query();

        if ($user->hasAnyRole('admin', 'manager') && ! $isPlatformAdmin) {
            $userQuery->where(function ($query) use ($user, $branchIds) {
                $query->where('organization_id', $user->organization_id ?? 0)
                    ->orWhereIn('branch_id', $branchIds);
            });
        } elseif ($user->hasAnyRole('staff')) {
            $userQuery->where('branch_id', $user->branch_id ?? 0);
        }

        $dateFilter = fn ($query) => $query
            ->when(! empty($filters['from']), fn ($query) => $query->whereDate('created_at', '>=', $filters['from']))
            ->when(! empty($filters['to']), fn ($query) => $query->whereDate('created_at', '<=', $filters['to']));
        $salesByCurrency = Order::query()
            ->joinPaymentTotals()
            ->whereIn('orders.branch_id', $branchIds)
            ->where($dateFilter)
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('orders.currency_code, SUM('.Order::NET_PAYMENT_SQL.') as sales_total')
            ->groupBy('orders.currency_code')
            ->havingRaw('SUM('.Order::NET_PAYMENT_SQL.') > 0')
            ->get();
        $defaultCurrency = $organizationId
            ? Organization::with('settings')->find($organizationId)?->settings?->currency_code ?? 'INR'
            : null;
        $salesValue = match ($salesByCurrency->count()) {
            0 => $defaultCurrency ? "{$defaultCurrency} 0.00" : '—',
            1 => $salesByCurrency->first()->currency_code.' '.number_format((float) $salesByCurrency->first()->sales_total, 2),
            default => 'Multiple currencies',
        };
        $orderCount = Order::whereIn('branch_id', $branchIds)->where($dateFilter)->count();
        $customerCount = Order::whereIn('branch_id', $branchIds)->where($dateFilter)
            ->whereNotNull('customer_phone')
            ->distinct()
            ->count('customer_phone');
        $orderStatuses = Order::whereIn('branch_id', $branchIds)
            ->where($dateFilter)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');
        $recentOrders = Order::whereIn('branch_id', $branchIds)
            ->where($dateFilter)
            ->with('branch')
            ->latest()
            ->take(5)
            ->get();

        $stats = [
            ['Sales', $salesValue, 'bi-currency-rupee', 'orange-bg', 'admin.orders.index'],
            ['Orders', number_format($orderCount), 'bi-receipt', 'blue-bg', 'admin.orders.index'],
            ['Customers', number_format($customerCount), 'bi-person-heart', 'green-bg', null],
            ['Organizations', number_format($isPlatformAdmin ? Organization::count() : Organization::whereKey($organizationId ?? 0)->count()), 'bi-building', 'purple-bg', $organizationRoute],
            ['Branches', number_format((clone $branchQuery)->count()), 'bi-shop-window', 'blue-bg', $branchRoute],
            ['Users', number_format($userQuery->count()), 'bi-people', 'green-bg', $staffRoute],
            ['Categories', number_format(Category::whereIn('branch_id', $branchIds)->count()), 'bi-grid-3x3', 'purple-bg', 'admin.categories.index'],
            ['Products', number_format(Product::whereIn('branch_id', $branchIds)->count()), 'bi-cup-hot', 'orange-bg', 'admin.products.index'],
            ['Dining Tables', number_format(DiningTable::whereIn('branch_id', $branchIds)->count()), 'bi-table', 'blue-bg', 'admin.dining-tables.index'],
            ['Upcoming Reservations', number_format(Reservation::whereIn('branch_id', $branchIds)
                ->whereIn('status', ['confirmed', 'seated'])
                ->whereBetween('starts_at', [now(), now()->addDays(7)])
                ->count()), 'bi-journal-check', 'green-bg', $branchRoute ? 'admin.reservations.index' : null],
            ['Upcoming Events', number_format(RestaurantEvent::whereIn('branch_id', $branchIds)
                ->where('status', 'scheduled')
                ->whereBetween('starts_at', [now(), now()->addDays(30)])
                ->count()), 'bi-calendar-event', 'purple-bg', $branchRoute ? 'admin.calendar.index' : null],
            ['Low-stock Items', number_format(InventoryItem::whereIn('branch_id', $branchIds)
                ->where('is_active', true)
                ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
                ->count()), 'bi-exclamation-triangle', 'orange-bg', $branchRoute ? 'admin.inventory.index' : null],
        ];

        return view('admin.dashboard', compact('stats', 'orderStatuses', 'recentOrders', 'filters'));
    }
}
