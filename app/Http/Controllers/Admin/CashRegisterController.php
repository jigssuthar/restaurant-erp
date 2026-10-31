<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ScopesBranchData;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\CashRegisterSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashRegisterController extends Controller
{
    use ScopesBranchData;

    public function index(Request $request)
    {
        $request->validate(['branch_id' => ['nullable', 'integer', 'exists:branches,id']]);
        $branches = $this->branchQuery()->orderBy('name')->get(['id', 'name']);
        $branch = $request->filled('branch_id')
            ? $this->branchQuery()->whereKey($request->integer('branch_id'))->firstOrFail()
            : $branches->first();
        $activeSession = $branch
            ? CashRegisterSession::where('branch_id', $branch->id)
                ->where('opened_by', $request->user()->id)
                ->whereNull('closed_at')
                ->with('opener')
                ->latest('opened_at')
                ->first()
            : null;
        $sessions = $branch
            ? CashRegisterSession::where('branch_id', $branch->id)
                ->with(['opener', 'closer'])
                ->latest('opened_at')
                ->paginate(15)
                ->withQueryString()
            : CashRegisterSession::query()->whereRaw('1 = 0')->paginate(15);

        return view('admin.cash-register.index', compact('branches', 'branch', 'activeSession', 'sessions'));
    }

    public function open(Request $request)
    {
        $data = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'opening_float' => ['required', 'numeric', 'min:0', 'decimal:0,2', 'max:100000000'],
        ]);
        $branch = Branch::findOrFail($data['branch_id']);
        $this->ensureBranchAccess($branch);
        $session = DB::transaction(function () use ($request, $data, $branch) {
            Branch::whereKey($branch->id)->lockForUpdate()->firstOrFail();
            $alreadyOpen = CashRegisterSession::where('branch_id', $branch->id)
                ->where('opened_by', $request->user()->id)
                ->whereNull('closed_at')
                ->exists();
            if ($alreadyOpen) {
                throw ValidationException::withMessages([
                    'branch_id' => 'You already have an open cash register for this branch.',
                ]);
            }

            return CashRegisterSession::create([
                'branch_id' => $branch->id,
                'opened_by' => $request->user()->id,
                'opening_float' => $data['opening_float'],
                'opened_at' => now(),
            ]);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Cash register opened.',
                'redirect' => route('admin.cash-register.index', ['branch_id' => $session->branch_id]),
            ], 201);
        }

        return redirect()->route('admin.cash-register.index', ['branch_id' => $session->branch_id])
            ->with('success', 'Cash register opened.');
    }

    public function close(Request $request, CashRegisterSession $session)
    {
        $this->ensureBranchAccess($session->branch);
        abort_unless($session->opened_by === $request->user()->id, 403);
        $data = $request->validate([
            'counted_cash' => ['required', 'numeric', 'min:0', 'decimal:0,2', 'max:100000000'],
            'closing_notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $expectedCash = DB::transaction(function () use ($request, $session, $data) {
            $locked = CashRegisterSession::whereKey($session->id)->lockForUpdate()->firstOrFail();
            if ($locked->closed_at) {
                throw ValidationException::withMessages([
                    'session' => 'This register session has already been closed.',
                ]);
            }
            $cashReceived = $locked->payments()
                ->where('payment_method', 'cash')
                ->where('transaction_type', 'payment')
                ->sum('amount');
            $cashRefunded = $locked->payments()
                ->where('payment_method', 'cash')
                ->where('transaction_type', 'refund')
                ->sum('amount');
            $expected = round(
                (float) $locked->opening_float + (float) $cashReceived - (float) $cashRefunded,
                2
            );
            $counted = round((float) $data['counted_cash'], 2);

            $locked->update([
                'closed_by' => $request->user()->id,
                'expected_cash' => $expected,
                'counted_cash' => $counted,
                'cash_difference' => round($counted - $expected, 2),
                'closing_notes' => $data['closing_notes'] ?? null,
                'closed_at' => now(),
            ]);

            return $expected;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Cash register closed.',
                'expected_cash' => $expectedCash,
                'difference' => round((float) $data['counted_cash'] - $expectedCash, 2),
                'refresh' => true,
            ]);
        }

        return back()->with('success', 'Cash register closed. Expected cash: '.number_format($expectedCash, 2));
    }
}
