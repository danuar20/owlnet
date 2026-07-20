<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function superAdmin(): User
{
    return User::factory()->superAdmin()->create();
}

function admin(): User
{
    return User::factory()->admin()->create();
}

it('lists users for a super admin', function (): void {
    superAdmin();
    $this->actingAs(superAdmin());

    $this->get(route('users.index'))
        ->assertOk()
        ->assertSee('Users');
});

it('blocks non-super-admins from the users index', function (): void {
    $this->actingAs(admin());

    $this->get(route('users.index'))->assertForbidden();
});

it('creates a new user with a role', function (): void {
    $this->actingAs(superAdmin());

    $this->post(route('users.store'), [
        'name' => 'New Staff',
        'email' => 'newstaff@example.com',
        'role' => UserRole::ADMIN->value,
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ])->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', [
        'email' => 'newstaff@example.com',
        'role' => UserRole::ADMIN->value,
    ]);
    // password must be hashed, not the raw value
    expect(User::where('email', 'newstaff@example.com')->first()->password)->not->toBe('secret123');
});

it('validates unique email and matching password confirmation on create', function (): void {
    $this->actingAs(superAdmin());
    $existing = User::factory()->create(['email' => 'dup@example.com']);

    $this->post(route('users.store'), [
        'name' => 'Dup',
        'email' => 'dup@example.com',
        'role' => UserRole::OPERATOR->value,
        'password' => 'secret123',
        'password_confirmation' => 'different',
    ])->assertSessionHasErrors(['email', 'password']);

    expect($existing)->toBeInstanceOf(User::class);
});

it('edits a user and updates the role', function (): void {
    $this->actingAs(superAdmin());
    $target = User::factory()->operator()->create();

    $this->put(route('users.update', $target), [
        'name' => 'Renamed',
        'email' => $target->email,
        'role' => UserRole::SUPER_ADMIN->value,
    ])->assertRedirect(route('users.index'));

    expect($target->fresh()->role)->toBe(UserRole::SUPER_ADMIN)
        ->and($target->fresh()->name)->toBe('Renamed');
});

it('keeps the old password when the edit form leaves it blank', function (): void {
    $this->actingAs(superAdmin());
    $target = User::factory()->create(['password' => 'original123']);

    $this->put(route('users.update', $target), [
        'name' => $target->name,
        'email' => $target->email,
        'role' => UserRole::OPERATOR->value,
        'password' => '',
    ])->assertRedirect(route('users.index'));

    // password unchanged -> old value still valid
    expect(Hash::check('original123', $target->fresh()->password))->toBeTrue();
});

it('deletes a user and prevents self-deletion', function (): void {
    $me = superAdmin();
    $this->actingAs($me);
    $victim = User::factory()->create();

    $this->delete(route('users.destroy', $victim))->assertRedirect(route('users.index'));
    expect(User::find($victim->id))->toBeNull();

    // cannot delete yourself
    $this->delete(route('users.destroy', $me))->assertRedirect(route('users.index'));
    expect(User::find($me->id))->not->toBeNull();
});
