<?php

declare(strict_types=1);

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\SubscriptionRequest;
use App\Models\Billing\Package;
use App\Models\Billing\Subscription;
use App\Models\Billing\User as BillingUser;
use App\Models\Radius\Router;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Subscription lifecycle management (activate / suspend / renew / expire / cancel).
 */
class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscription
    ) {}

    public function index(): View
    {
        return view('billing.subscriptions.index', [
            'subscriptions' => $this->subscription->list(),
        ]);
    }

    public function create(): View
    {
        return view('billing.subscriptions.create', [
            'subscription' => new Subscription,
            'customers' => BillingUser::orderBy('name')->get(),
            'packages' => Package::orderBy('name')->get(),
            'routers' => Router::orderBy('name')->get(),
        ]);
    }

    public function store(SubscriptionRequest $request): RedirectResponse
    {
        $subscription = $this->subscription->create($request->validated());

        return redirect()
            ->route('subscriptions.show', $subscription)
            ->with('status', 'Subscription created.');
    }

    public function show(Subscription $subscription): View
    {
        return view('billing.subscriptions.show', [
            'subscription' => $subscription,
            'history' => $this->subscription->history($subscription),
        ]);
    }

    public function edit(Subscription $subscription): View
    {
        return view('billing.subscriptions.edit', [
            'subscription' => $subscription,
            'customers' => BillingUser::orderBy('name')->get(),
            'packages' => Package::orderBy('name')->get(),
            'routers' => Router::orderBy('name')->get(),
        ]);
    }

    public function update(SubscriptionRequest $request, Subscription $subscription): RedirectResponse
    {
        $updated = $this->subscription->update($subscription->id, $request->validated());

        return redirect()
            ->route('subscriptions.show', $updated)
            ->with('status', 'Subscription updated.');
    }

    public function destroy(Subscription $subscription): RedirectResponse
    {
        Gate::authorize('super_admin');

        $this->subscription->delete($subscription->id);

        return redirect()
            ->route('subscriptions.index')
            ->with('status', 'Subscription deleted.');
    }

    public function activate(Subscription $subscription): RedirectResponse
    {
        $this->subscription->activate($subscription, [], (string) auth()->id());

        return back()->with('status', 'Subscription activated.');
    }

    public function suspend(Subscription $subscription): RedirectResponse
    {
        $this->subscription->suspend($subscription, (string) auth()->id());

        return back()->with('status', 'Subscription suspended.');
    }

    public function renew(Subscription $subscription): RedirectResponse
    {
        $this->subscription->renew($subscription, [], (string) auth()->id());

        return back()->with('status', 'Subscription renewed.');
    }

    public function expire(Subscription $subscription): RedirectResponse
    {
        $this->subscription->expire($subscription, (string) auth()->id());

        return back()->with('status', 'Subscription marked expired.');
    }
}
