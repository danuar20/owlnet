<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Radius\RadiusGroupReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function profileAdmin(): User
{
    return User::factory()->create(['role' => UserRole::ADMIN]);
}

it('lists profiles on the index page', function (): void {
    RadiusGroupReply::create(['groupname' => 'voucher', 'attribute' => 'Mikrotik-Rate-Limit', 'value' => '2M/4M']);

    $this->actingAs(profileAdmin())
        ->get(route('radius-profiles.index'))
        ->assertOk()
        ->assertSee('voucher');
});

it('shows the create form', function (): void {
    $this->actingAs(profileAdmin())
        ->get(route('radius-profiles.create'))
        ->assertOk()
        ->assertSee('Mikrotik-Rate-Limit');
});

it('creates a profile via the controller', function (): void {
    $this->actingAs(profileAdmin())
        ->post(route('radius-profiles.store'), [
            'groupname' => 'profile-20m',
            'attributes' => [
                ['attribute' => 'Mikrotik-Rate-Limit', 'op' => ':=', 'value' => '20M/20M'],
                ['attribute' => 'Session-Timeout', 'op' => ':=', 'value' => '28800'],
            ],
        ])
        ->assertRedirect(route('radius-profiles.show', 'profile-20m'));

    expect(RadiusGroupReply::forGroup('profile-20m')->count())->toBe(2);
});

it('validates groupname required', function (): void {
    $this->actingAs(profileAdmin())
        ->post(route('radius-profiles.store'), ['groupname' => ''])
        ->assertSessionHasErrors('groupname');
});

it('shows a profile', function (): void {
    RadiusGroupReply::create(['groupname' => 'show-me', 'attribute' => 'A', 'value' => '1']);

    $this->actingAs(profileAdmin())
        ->get(route('radius-profiles.show', 'show-me'))
        ->assertOk()
        ->assertSee('show-me');
});

it('updates a profile via the controller', function (): void {
    RadiusGroupReply::create(['groupname' => 'old', 'attribute' => 'A', 'value' => '1']);

    $this->actingAs(profileAdmin())
        ->put(route('radius-profiles.update', 'old'), [
            'groupname' => 'renamed',
            'attributes' => [['attribute' => 'B', 'op' => ':=', 'value' => '2']],
        ])
        ->assertRedirect(route('radius-profiles.show', 'renamed'));

    expect(RadiusGroupReply::forGroup('old')->count())->toBe(0)
        ->and(RadiusGroupReply::forGroup('renamed')->count())->toBe(1);
});

it('deletes a profile via the controller', function (): void {
    RadiusGroupReply::create(['groupname' => 'del', 'attribute' => 'A', 'value' => '1']);

    $this->actingAs(profileAdmin())
        ->delete(route('radius-profiles.destroy', 'del'))
        ->assertRedirect(route('radius-profiles.index'));

    expect(RadiusGroupReply::forGroup('del')->count())->toBe(0);
});

it('blocks guests', function (): void {
    $this->get(route('radius-profiles.index'))->assertRedirect(route('login'));
});
