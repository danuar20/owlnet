<?php

declare(strict_types=1);

use App\Enums\SubscriptionStatus;
use App\Models\Billing\Subscription;
use App\Repositories\Billing\SubscriptionRepository;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->service = app(SubscriptionService::class);
});

it('activates a pending subscription and records history', function (): void {
    $sub = Subscription::factory()->pending()->create();

    $activated = $this->service->activate($sub, ['password' => 'secret123']);

    expect($activated->status)->toBe(SubscriptionStatus::ACTIVE)
        ->and($activated->started_at)->not->toBeNull()
        ->and($activated->expired_at)->not->toBeNull();
    $this->assertDatabaseHas('billing.subscription_histories', [
        'subscription_id' => $sub->id,
        'action' => 'activate',
        'to_status' => 'active',
    ]);
});

it('suspends an active subscription', function (): void {
    $sub = Subscription::factory()->active()->create();
    $suspended = $this->service->suspend($sub);

    expect($suspended->status)->toBe(SubscriptionStatus::SUSPENDED);
    $this->assertDatabaseHas('billing.subscription_histories', [
        'subscription_id' => $sub->id,
        'action' => 'suspend',
        'to_status' => 'suspended',
    ]);
});

it('renews and extends expiry', function (): void {
    $sub = Subscription::factory()->active()->create([
        'expired_at' => now()->addDays(5),
    ]);
    $old = $sub->expired_at->copy();
    $renewed = $this->service->renew($sub);

    expect($renewed->status)->toBe(SubscriptionStatus::ACTIVE)
        ->and($renewed->expired_at->greaterThan($old))->toBeTrue();
});

it('renews a suspended subscription back to active', function (): void {
    $sub = Subscription::factory()->suspended()->create();
    $renewed = $this->service->renew($sub);

    expect($renewed->status)->toBe(SubscriptionStatus::ACTIVE);
});

it('expires a subscription and writes history', function (): void {
    $sub = Subscription::factory()->active()->create();
    $expired = $this->service->expire($sub);

    expect($expired->status)->toBe(SubscriptionStatus::EXPIRED);
    $this->assertDatabaseHas('billing.subscription_histories', [
        'subscription_id' => $sub->id,
        'action' => 'expire',
    ]);
});

it('auto-expires only due subscriptions', function (): void {
    Subscription::factory()->active()->create(['expired_at' => now()->subDay()]);   // due
    Subscription::factory()->active()->create(['expired_at' => now()->addDays(10)]); // not due
    Subscription::factory()->suspended()->create();                                 // not active

    $count = $this->service->expireDue('scheduler');

    expect($count)->toBe(1);
    expect(Subscription::where('status', SubscriptionStatus::EXPIRED)->count())->toBe(1);
});

it('cancels a subscription', function (): void {
    $sub = Subscription::factory()->active()->create();
    $cancelled = $this->service->cancel($sub);

    expect($cancelled->status)->toBe(SubscriptionStatus::EXPIRED);
});

it('counts subscriptions by status', function (): void {
    Subscription::factory()->active()->create();
    Subscription::factory()->suspended()->create();
    Subscription::factory()->expired()->create();

    $repo = app(SubscriptionRepository::class);
    $counts = $repo->countByStatus();
    expect($counts)->toHaveKey('active')
        ->and($counts['active'])->toBeGreaterThanOrEqual(1);
});

it('provides a history collection', function (): void {
    $sub = Subscription::factory()->active()->create();
    $this->service->suspend($sub);

    expect($this->service->history($sub))->toHaveCount(1)
        ->and($this->service->history($sub)->first()->action)->toBe('suspend');
});
