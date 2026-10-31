<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ScopesBranchData;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\DiningTable;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    use ScopesBranchData;

    public function index(Request $request)
    {
        $request->validate([
            'date' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in(Reservation::STATUSES)],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);
        $reservations = $this->scopeToAccessibleBranches(Reservation::query())
            ->with(['branch', 'diningTable'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(fn ($query) => $query->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('reservation_number', 'like', "%{$search}%"));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('date'), fn ($query) => $query->whereDate('starts_at', $request->input('date')))
            ->when($request->filled('branch_id'), fn ($query) => $query->where('branch_id', $request->integer('branch_id')))
            ->orderByDesc('starts_at')
            ->paginate(15)
            ->withQueryString();
        $branches = $this->branchQuery()->orderBy('name')->get(['id', 'name']);

        return view('admin.reservations.index', compact('reservations', 'branches'));
    }

    public function create()
    {
        $branches = $this->branchQuery()->with('diningTables')->orderBy('name')->get();

        return view('admin.reservations.form', ['reservation' => new Reservation, 'branches' => $branches]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $branch = Branch::findOrFail($data['branch_id']);
        $this->ensureBranchAccess($branch);
        $table = isset($data['dining_table_id']) ? DiningTable::findOrFail($data['dining_table_id']) : null;

        if ($table && $data['party_size'] > $table->capacity) {
            throw ValidationException::withMessages(['party_size' => 'Party size exceeds the selected table capacity.']);
        }
        if ($table && $table->status === 'maintenance') {
            throw ValidationException::withMessages(['dining_table_id' => 'A table under maintenance cannot be reserved.']);
        }

        $reservation = DB::transaction(function () use ($data, $request, $table) {
            if ($table) {
                DiningTable::whereKey($table->id)->lockForUpdate()->firstOrFail();
                $conflict = Reservation::where('dining_table_id', $table->id)
                    ->whereIn('status', ['confirmed', 'seated'])
                    ->where('starts_at', '<', $data['ends_at'])
                    ->where('ends_at', '>', $data['starts_at'])
                    ->exists();
                if ($conflict) {
                    throw ValidationException::withMessages(['dining_table_id' => 'This table already has a reservation during that time.']);
                }
            }

            return Reservation::create([
                ...$data,
                'created_by' => $request->user()->id,
                'reservation_number' => 'RSV-'.now()->format('ymd').'-'.Str::upper(Str::random(6)),
                'status' => 'confirmed',
            ]);
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Reservation created.', 'redirect' => route('admin.reservations.index')], 201);
        }

        return redirect()->route('admin.reservations.index')->with('success', "Reservation {$reservation->reservation_number} created.");
    }

    public function updateStatus(Request $request, Reservation $reservation)
    {
        $this->ensureBranchAccess($reservation->branch);
        $data = $request->validate(['status' => ['required', Rule::in(Reservation::STATUSES)]]);
        DB::transaction(function () use ($reservation, $data) {
            $locked = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();
            $allowed = [
                'confirmed' => ['seated', 'cancelled', 'no_show'],
                'seated' => ['completed'],
                'completed' => [],
                'cancelled' => [],
                'no_show' => [],
            ];
            if ($data['status'] === $locked->status) {
                return;
            }
            if (! in_array($data['status'], $allowed[$locked->status] ?? [], true)) {
                throw ValidationException::withMessages(['status' => 'That reservation status transition is not allowed.']);
            }
            $locked->update(['status' => $data['status']]);
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Reservation status updated.', 'refresh' => true]);
        }

        return back()->with('success', 'Reservation status updated.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'dining_table_id' => [
                'nullable',
                'integer',
                Rule::exists('dining_tables', 'id')->where('branch_id', $request->input('branch_id')),
            ],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'party_size' => ['required', 'integer', 'min:1', 'max:100'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $data['status'] = 'confirmed';

        return $data;
    }
}
