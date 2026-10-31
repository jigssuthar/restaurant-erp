<?php

namespace App\Http\Controllers;

use App\Models\DiningTable;
use App\Models\TableServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerTableServiceRequestController extends Controller
{
    public function store(Request $request, string $token)
    {
        $data = $request->validate([
            'request_type' => ['required', 'in:'.implode(',', TableServiceRequest::TYPES)],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $serviceRequest = DB::transaction(function () use ($data, $token) {
            $table = DiningTable::query()
                ->where('qr_token', $token)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(\in_array($table->status, ['available', 'occupied'], true), 404);

            $existingRequest = TableServiceRequest::query()
                ->where('dining_table_id', $table->id)
                ->where('request_type', $data['request_type'])
                ->whereIn('status', ['pending', 'acknowledged'])
                ->latest()
                ->first();

            if ($existingRequest) {
                return $existingRequest;
            }

            return TableServiceRequest::create([
                'branch_id' => $table->branch_id,
                'dining_table_id' => $table->id,
                'request_type' => $data['request_type'],
                'status' => 'pending',
                'note' => $data['note'] ?? null,
            ]);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Your request has been sent to the restaurant team.',
                'request_id' => $serviceRequest->id,
                'status' => $serviceRequest->status,
            ], 201);
        }

        return redirect()
            ->route('qr.menu', ['token' => $token])
            ->with('qr_service_request_status', 'Your request has been sent to the restaurant team.');
    }
}
