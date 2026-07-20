<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating/updating an application (staff) user.
 */
class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'role' => ['required', Rule::in(UserRole::values())],
            // Required on create (store), optional on update (may be left blank to keep current password).
            'password' => [$this->isStore() ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Password confirmation field name.
     */
    public function attributes(): array
    {
        return [
            'role' => 'role',
            'password' => 'password',
            'password_confirmation' => 'password confirmation',
        ];
    }

    private function isStore(): bool
    {
        return $this->route()?->getName() === 'users.store';
    }
}
