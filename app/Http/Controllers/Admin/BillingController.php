<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillingTransaction;
use App\Models\OrganizationSubscription;
use App\Models\SaasPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $organization = $request->user()->organization;
        abort_unless($organization, 403, 'Billing is available to restaurant workspaces only.');

        $subscription = $organization->currentSubscription;
        $plans = SaasPlan::query()->where('is_active', true)->whereNotNull('price')
            ->whereNotNull('razorpay_plan_id')->orderBy('sort_order')->get();
        $transactions = $subscription?->transactions()->latest()->paginate(10) ?? collect();

        return view('admin.billing.index', compact('subscription', 'plans', 'transactions'));
    }

    public function subscribe(Request $request, SaasPlan $plan)
    {
        $organization = $request->user()->organization;
        abort_unless($organization, 403);
        abort_unless($plan->is_active && $plan->price !== null && $plan->razorpay_plan_id, 404);

        $subscription = $organization->currentSubscription;
        if (! $subscription) {
            abort(403, 'No trial subscription exists for this workspace.');
        }
        if ($subscription->status === 'active' && $subscription->grantsAccess()) {
            return back()->withErrors(['plan' => 'Your subscription is already active.']);
        }
        $pendingCheckout = $subscription->provider_subscription_id
            && $subscription->saas_plan_id === $plan->id
            && $subscription->current_period_start === null
            && $subscription->current_period_end === null
            && $subscription->trial_ends_at !== null
            && \in_array($subscription->status, ['trialing', 'expired'], true);
        if ($subscription->provider_subscription_id
            && $subscription->trial_ends_at !== null
            && $subscription->current_period_start === null
            && $subscription->current_period_end === null
            && ! $pendingCheckout) {
            return back()->withErrors(['plan' => 'A checkout is already pending for a different plan. Finish that checkout or contact support before switching plans.']);
        }

        $key = config('services.razorpay.key');
        $secret = config('services.razorpay.secret');
        if (! $key || ! $secret) {
            return back()->withErrors(['plan' => 'Online billing is not configured yet. Please contact the platform administrator.']);
        }

        $gatewayPlan = Http::withBasicAuth($key, $secret)
            ->acceptJson()
            ->get(rtrim(config('services.razorpay.url'), '/').'/v1/plans/'.$plan->razorpay_plan_id);
        $gatewayPlanData = $gatewayPlan->json();
        $gatewayAmount = data_get($gatewayPlanData, 'item.amount');
        $gatewayCurrency = data_get($gatewayPlanData, 'item.currency');
        if (! $gatewayPlan->successful()
            || $gatewayAmount === null
            || $gatewayCurrency === null
            || data_get($gatewayPlanData, 'period') === null
            || data_get($gatewayPlanData, 'interval') === null) {
            Log::error('Razorpay plan lookup failed.', ['status' => $gatewayPlan->status()]);

            return back()->withErrors(['plan' => 'The configured Razorpay plan could not be verified. Checkout is unavailable until it is corrected.']);
        }

        if ((int) $gatewayAmount !== (int) round((float) $plan->price * 100)
            || strtoupper((string) $gatewayCurrency) !== strtoupper($plan->currency)
            || data_get($gatewayPlanData, 'period') !== $plan->billing_interval
            || (int) data_get($gatewayPlanData, 'interval') !== 1) {
            Log::error('Razorpay plan does not match the published SaaS price or interval.', ['plan_id' => $plan->id]);

            return back()->withErrors(['plan' => 'The Razorpay plan amount or currency does not match this published price. Contact the platform administrator.']);
        }

        if ($pendingCheckout) {
            return $this->checkoutView($subscription, $plan, $key, $organization, $request);
        }

        $response = Http::withBasicAuth($key, $secret)
            ->acceptJson()
            ->post(rtrim(config('services.razorpay.url'), '/').'/v1/subscriptions', [
                'plan_id' => $plan->razorpay_plan_id,
                'total_count' => $plan->billing_interval === 'yearly' ? 10 : 120,
                'quantity' => 1,
                'customer_notify' => 1,
                'notes' => [
                    'organization_id' => (string) $organization->id,
                    'plan_slug' => $plan->slug,
                ],
            ]);

        if (! $response->successful() || ! $response->json('id')) {
            Log::error('Razorpay subscription creation failed.', ['status' => $response->status()]);

            return back()->withErrors(['plan' => 'Razorpay could not start checkout. Verify the configured plan ID and gateway credentials.']);
        }

        $subscription->update([
            'saas_plan_id' => $plan->id,
            'provider' => 'razorpay',
            'provider_subscription_id' => $response->json('id'),
            'status' => $subscription->status === 'trialing' && $subscription->trial_ends_at?->isFuture()
                ? 'trialing'
                : 'expired',
        ]);

        return $this->checkoutView($subscription->fresh(), $plan, $key, $organization, $request);
    }

    private function checkoutView(
        OrganizationSubscription $subscription,
        SaasPlan $plan,
        string $key,
        \App\Models\Organization $organization,
        Request $request
    ) {
        return view('admin.billing.checkout', [
            'plan' => $plan,
            'subscription' => $subscription,
            'razorpayKey' => $key,
            'organization' => $organization,
            'checkoutName' => config('app.name', 'RestaurantOS'),
            'owner' => $request->user(),
        ]);
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'razorpay_payment_id' => ['required', 'string', 'max:255'],
            'razorpay_subscription_id' => ['required', 'string', 'max:255'],
            'razorpay_signature' => ['required', 'string', 'size:64'],
        ]);

        $subscription = $request->user()->organization?->currentSubscription;
        abort_unless(
            $subscription
                && $subscription->provider_subscription_id === $data['razorpay_subscription_id'],
            403
        );

        $key = config('services.razorpay.key');
        $secret = config('services.razorpay.secret');
        abort_unless($key && $secret, 503, 'Payment verification is unavailable. Contact support before retrying.');
        $expected = hash_hmac(
            'sha256',
            $data['razorpay_subscription_id'].'|'.$data['razorpay_payment_id'],
            $secret
        );
        abort_unless(hash_equals($expected, $data['razorpay_signature']), 403, 'Payment signature verification failed.');

        $paymentResponse = Http::withBasicAuth($key, $secret)
            ->acceptJson()
            ->get(rtrim(config('services.razorpay.url'), '/').'/v1/payments/'.$data['razorpay_payment_id']);
        $payment = $paymentResponse->json();
        $paymentStatus = data_get($payment, 'status');
        $plan = $subscription->plan;
        if (! $paymentResponse->successful()
            || data_get($payment, 'id') !== $data['razorpay_payment_id']
            || data_get($payment, 'subscription_id') !== $data['razorpay_subscription_id']
            || ! \in_array($paymentStatus, ['authorized', 'captured'], true)
            || strtoupper((string) data_get($payment, 'currency')) !== strtoupper((string) $plan?->currency)) {
            Log::error('Razorpay payment could not be verified against its subscription.', [
                'status' => $paymentResponse->status(),
            ]);

            return response()->json([
                'message' => 'Payment verification is pending. Do not retry; contact support with your Razorpay payment reference.',
            ], 422);
        }

        DB::transaction(function () use ($subscription, $data, $payment, $paymentStatus): void {
            $subscription->refresh();
            $periodStart = $subscription->current_period_start ?? now();
            $periodEnd = $this->periodEnd($subscription, $periodStart);

            $subscription->update([
                'status' => 'active',
                'current_period_start' => $periodStart,
                'current_period_end' => $periodEnd,
                'trial_ends_at' => null,
                'ends_at' => null,
            ]);

            BillingTransaction::updateOrCreate(
                ['provider_payment_id' => $data['razorpay_payment_id']],
                [
                    'organization_subscription_id' => $subscription->id,
                    'amount' => ((int) data_get($payment, 'amount', 0)) / 100,
                    'currency' => data_get($payment, 'currency', config('saas.currency', 'INR')),
                    'status' => $paymentStatus,
                    'payment_method' => data_get($payment, 'method'),
                    'paid_at' => $paymentStatus === 'captured' && data_get($payment, 'created_at')
                        ? Carbon::createFromTimestamp((int) data_get($payment, 'created_at'))
                        : ($paymentStatus === 'captured' ? now() : null),
                    'details' => ['source' => 'checkout_signature_verified'],
                ]
            );
        });

        return redirect()->route('admin.billing.index')->with('success', 'Payment verified and subscription activated.');
    }

    public function cancel(Request $request)
    {
        $subscription = $request->user()->organization?->currentSubscription;
        if (! $subscription
            || $subscription->status !== 'active'
            || ! $subscription->provider_subscription_id
            || ! $subscription->grantsAccess()) {
            return back()->withErrors(['subscription' => 'There is no active Razorpay subscription to cancel.']);
        }

        $key = config('services.razorpay.key');
        $secret = config('services.razorpay.secret');
        if (! $key || ! $secret) {
            return back()->withErrors(['subscription' => 'Online billing is not configured. Please contact the platform administrator.']);
        }

        $response = Http::withBasicAuth($key, $secret)
            ->acceptJson()
            ->post(
                rtrim(config('services.razorpay.url'), '/').'/v1/subscriptions/'.$subscription->provider_subscription_id.'/cancel',
                ['cancel_at_cycle_end' => 1]
            );
        if (! $response->successful()) {
            Log::error('Razorpay subscription cancellation failed.', ['status' => $response->status()]);

            return back()->withErrors(['subscription' => 'Razorpay could not schedule cancellation. Please try again or contact support.']);
        }

        $subscription->update(['cancel_at_period_end' => true]);

        return back()->with('success', 'Cancellation is scheduled for the end of the current billing period.');
    }

    public function webhook(Request $request)
    {
        $secret = config('services.razorpay.webhook_secret');
        $signature = (string) $request->header('X-Razorpay-Signature');
        abort_unless($secret && $signature, 403);

        $expected = hash_hmac('sha256', $request->getContent(), $secret);
        abort_unless(hash_equals($expected, $signature), 403, 'Invalid webhook signature.');

        $event = $request->json()->all();
        $subscriptionEntity = data_get($event, 'payload.subscription.entity');
        $subscriptionId = data_get($subscriptionEntity, 'id')
            ?? data_get($event, 'payload.payment.entity.subscription_id');
        if (! $subscriptionId) {
            return response()->json(['status' => 'ignored']);
        }

        $subscription = OrganizationSubscription::where('provider_subscription_id', $subscriptionId)->first();
        if (! $subscription) {
            Log::warning('Razorpay webhook references an unknown subscription.', ['event' => $event['event'] ?? null]);

            return response()->json(['status' => 'unknown_subscription'], 404);
        }

        DB::transaction(function () use ($event, $subscription, $subscriptionEntity): void {
            $subscription = OrganizationSubscription::query()
                ->whereKey($subscription->id)
                ->lockForUpdate()
                ->firstOrFail();
            $eventName = (string) ($event['event'] ?? '');
            $eventCreatedAt = data_get($event, 'created_at')
                ? Carbon::createFromTimestamp((int) data_get($event, 'created_at'))
                : null;
            $isLatestEvent = ! $eventCreatedAt
                || ! $subscription->last_webhook_at
                || $eventCreatedAt->greaterThanOrEqualTo($subscription->last_webhook_at);
            $status = match ($eventName) {
                'subscription.authenticated', 'subscription.activated', 'subscription.charged' => 'active',
                'subscription.halted', 'payment.failed' => 'past_due',
                'subscription.cancelled' => 'cancelled',
                'subscription.completed' => 'expired',
                default => null,
            };

            $update = [];
            if ($status && $isLatestEvent) {
                $update['status'] = $status;
            }
            if ($status === 'active' && $isLatestEvent) {
                $update['trial_ends_at'] = null;
            }
            $start = data_get($subscriptionEntity, 'current_start');
            $end = data_get($subscriptionEntity, 'current_end');
            if ($start && $isLatestEvent) {
                $update['current_period_start'] = Carbon::createFromTimestamp((int) $start);
            }
            if ($end && $isLatestEvent) {
                $update['current_period_end'] = Carbon::createFromTimestamp((int) $end);
            }
            $cancelAt = data_get($subscriptionEntity, 'ended_at');
            if ($cancelAt && $isLatestEvent) {
                $update['ends_at'] = Carbon::createFromTimestamp((int) $cancelAt);
            } elseif ($eventName === 'subscription.cancelled' && $isLatestEvent) {
                $update['ends_at'] = data_get($subscriptionEntity, 'current_end')
                    ? Carbon::createFromTimestamp((int) data_get($subscriptionEntity, 'current_end'))
                    : now();
            }
            if ($status && $eventCreatedAt && $isLatestEvent) {
                $update['last_webhook_at'] = $eventCreatedAt;
            }
            if ($update) {
                $subscription->update($update);
            }

            $payment = data_get($event, 'payload.payment.entity');
            $paymentId = data_get($payment, 'id');
            if ($paymentId && \in_array($eventName, ['subscription.charged', 'payment.captured'], true)) {
                BillingTransaction::updateOrCreate(
                    ['provider_payment_id' => $paymentId],
                    [
                        'organization_subscription_id' => $subscription->id,
                        'provider_invoice_id' => data_get($event, 'payload.invoice.entity.id'),
                        'amount' => ((int) data_get($payment, 'amount', 0)) / 100,
                        'currency' => data_get($payment, 'currency', config('saas.currency', 'INR')),
                        'status' => 'captured',
                        'payment_method' => data_get($payment, 'method'),
                        'paid_at' => data_get($payment, 'created_at')
                            ? Carbon::createFromTimestamp((int) data_get($payment, 'created_at'))
                            : now(),
                        'details' => ['event' => $eventName],
                    ]
                );
            }
        });

        return response()->json(['status' => 'ok']);
    }

    private function periodEnd(OrganizationSubscription $subscription, Carbon $start): Carbon
    {
        return $subscription->plan?->billing_interval === 'yearly'
            ? $start->copy()->addYear()
            : $start->copy()->addMonth();
    }
}
