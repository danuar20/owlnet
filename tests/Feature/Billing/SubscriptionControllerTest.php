<?php

declare(strict_types=1);

use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Billing\Package;
use App\Models\Billing\Subscription;
use App\Models\Billing\User as BillingUser;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function subAdmin(): User
{
    return User::factory()->create(['role' => UserRole::ADMIN]);
}

it('lists subscriptions', function (): void {
    Subscription::factory()->active()->create();
    $this->actingAs(subAdmin())
        ->get(route('subscriptions.index'))
        ->assertOk()
        ->assertSee('Subscriptions');
});

it('shows the create form with customer/package selects', function (): void {
    BillingUser::factory()->create(['name' => 'Budi']);
    Package::factory()->create(['name' => 'Fiber-100']);
    $this->actingAs(subAdmin())
        ->get(route('subscriptions.create'))
        ->assertOk()
        ->assertSee('Budi')
        ->assertSee('Fiber-100');
});

it('creates a subscription', function (): void {
    $customer = BillingUser::factory()->create();
    $package = Package::factory()->create();

    $this->actingAs(subAdmin())
        ->post(route('subscriptions.store'), [
            'user_id' => $customer->id,
            'package_id' => $package->id,
            'username' => 'budi01',
            'status' => 'pending',
            'price' => 100000,
        ])
        ->assertRedirect(route('subscriptions.show', Subscription::where('username', 'budi01')->firstOrFail()));

    expect(Subscription::where('username', 'budi01')->exists())->toBeTrue();
});

it('activates a subscription via the action endpoint', function (): void {
    $sub = Subscription::factory()->pending()->create();

    $this->actingAs(subAdmin())
        ->post(route('subscriptions.activate', $sub), ['password' => 'x'])
        ->assertRedirect();

    expect($sub->fresh()->status)->toBe(SubscriptionStatus::ACTIVE);
});

it('suspends a subscription via the action endpoint', function (): void {
    $sub = Subscription::factory()->active()->create();

    $this->actingAs(subAdmin())
        ->post(route('subscriptions.suspend', $sub))
        ->assertRedirect();

    expect($sub->fresh()->status)->toBe(SubscriptionStatus::SUSPENDED);
});

it('renews a subscription via the action endpoint', function (): void {
    $sub = Subscription::factory()->active()->create(['expired_at' => now()->addDays(2)]);

    $this->actingAs(subAdmin())
        ->post(route('subscriptions.renew', $sub))
        ->assertRedirect();

    expect($sub->fresh()->status)->toBe(SubscriptionStatus::ACTIVE)
        ->and($sub->fresh()->expired_at->greaterThan(now()->addDays(2)))->toBeTrue();
});

it('marks a subscription expired via the action endpoint', function (): void {
    $sub = Subscription::factory()->active()->create();

    $this->actingAs(subAdmin())
        ->post(route('subscriptions.expire', $sub))
        ->assertRedirect();

    expect($sub->fresh()->status)->toBe(SubscriptionStatus::EXPIRED);
});

it('shows subscription history on the detail page', function (): void {
    $sub = Subscription::factory()->active()->create();
    app(SubscriptionService::class)->suspend($sub);

    $this->actingAs(subAdmin())
        ->get(route('subscriptions.show', $sub))
        ->assertOk()
        ->assertSee('activate')
        ->assertSee('suspend')
        ->assertSee('History');
});

it('blocks guests', function (): void {
    $this->get(route('subscriptions.index'))->assertRedirect(route('login'));
});
