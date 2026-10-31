<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ScopesBranchData;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\RestaurantEvent;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CalendarController extends Controller
{
    use ScopesBranchData;

    public function index(Request $request)
    {
        $filters = $request->validate(['month' => ['nullable', 'date_format:Y-m']]);
        $month = isset($filters['month'])
            ? Carbon::createFromFormat('Y-m', $filters['month'])->startOfMonth()
            : now()->startOfMonth();
        $events = $this->scopeToAccessibleBranches(RestaurantEvent::query())
            ->with('branch')
            ->where('starts_at', '<', $month->copy()->addMonth()->startOfMonth())
            ->where('ends_at', '>=', $month->copy()->startOfMonth())
            ->when($request->filled('branch_id'), fn ($query) => $query->where('branch_id', $request->integer('branch_id')))
            ->orderBy('starts_at')
            ->paginate(20)
            ->withQueryString();
        $branches = $this->branchQuery()->orderBy('name')->get(['id', 'name']);

        return view('admin.calendar.index', compact('events', 'branches', 'month'));
    }

    public function create()
    {
        $branches = $this->branchQuery()->orderBy('name')->get(['id', 'name']);

        return view('admin.calendar.form', ['event' => new RestaurantEvent, 'branches' => $branches]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $branch = Branch::findOrFail($data['branch_id']);
        $this->ensureBranchAccess($branch);
        $data['created_by'] = $request->user()->id;
        $event = RestaurantEvent::create($data);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Event created.', 'redirect' => route('admin.calendar.index')], 201);
        }

        return redirect()->route('admin.calendar.index')->with('success', 'Event created successfully.');
    }

    public function edit(RestaurantEvent $calendar)
    {
        $this->ensureBranchAccess($calendar->branch);
        $branches = $this->branchQuery()->orderBy('name')->get(['id', 'name']);

        return view('admin.calendar.form', ['event' => $calendar, 'branches' => $branches]);
    }

    public function update(Request $request, RestaurantEvent $calendar)
    {
        $this->ensureBranchAccess($calendar->branch);
        $data = $this->validatedData($request);
        $this->ensureBranchAccess(Branch::findOrFail($data['branch_id']));
        $calendar->update($data);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Event updated.', 'redirect' => route('admin.calendar.index')]);
        }

        return redirect()->route('admin.calendar.index')->with('success', 'Event updated successfully.');
    }

    public function destroy(Request $request, RestaurantEvent $calendar)
    {
        $this->ensureBranchAccess($calendar->branch);
        $calendar->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Event deleted.', 'refresh' => true]);
        }

        return redirect()->route('admin.calendar.index')->with('success', 'Event deleted successfully.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'title' => ['required', 'string', 'max:180'],
            'event_type' => ['required', Rule::in(RestaurantEvent::TYPES)],
            'description' => ['nullable', 'string', 'max:4000'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'all_day' => ['sometimes', 'boolean'],
            'status' => ['required', Rule::in(['scheduled', 'completed', 'cancelled'])],
        ]);
        $data['all_day'] = $request->boolean('all_day');

        return $data;
    }
}
