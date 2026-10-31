@extends('layouts.admin')

@section('title', 'Subscription Plans')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Subscription Plans</h1>
        <p class="text-muted mb-0">Configure plans and connect each one to a Razorpay plan ID. Prices are not pre-filled.</p>
    </div>
    <a href="{{ route('admin.saas-plans.create') }}" class="btn btn-orange">Create plan</a>
</div>

@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

<div class="admin-table-card table-responsive">
    <table class="table align-middle">
        <thead><tr><th>Plan</th><th>Price</th><th>Limits</th><th>Status</th><th>Subscribers</th><th></th></tr></thead>
        <tbody>
        @forelse($plans as $plan)
            <tr>
                <td><strong>{{ $plan->name }}</strong><div class="small text-muted">{{ $plan->slug }}</div></td>
                <td>{{ $plan->price === null ? 'Not set' : $plan->currency.' '.number_format((float) $plan->price, 2).' / '.$plan->billing_interval }}</td>
                <td class="small">Branches: {{ $plan->max_branches ?? 'Unlimited' }} · Staff: {{ $plan->max_staff ?? 'Unlimited' }}<br>
                    Products: {{ $plan->max_products ?? 'Unlimited' }} · Tables: {{ $plan->max_tables ?? 'Unlimited' }}</td>
                <td><span class="badge {{ $plan->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $plan->is_active ? 'Published' : 'Draft' }}</span>
                    <div class="small text-muted">{{ $plan->razorpay_plan_id ?: 'No Razorpay plan ID' }}</div></td>
                <td>{{ $plan->subscriptions_count }}</td>
                <td class="text-end">
                    <a href="{{ route('admin.saas-plans.edit', $plan) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    @if($plan->subscriptions_count === 0)
                        <form class="d-inline" method="POST" action="{{ route('admin.saas-plans.destroy', $plan) }}"
                              onsubmit="return confirm('Delete this draft plan?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">No plans configured. Create draft plans now and publish when pricing and Razorpay IDs are ready.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $plans->links() }}
</div>
@endsection
