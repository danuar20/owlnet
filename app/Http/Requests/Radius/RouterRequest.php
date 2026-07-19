<?php

declare(strict_types=1);

namespace App\Http\Requests\Radius;

use App\Models\Radius\Router;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating/updating a router.
 */
class RouterRequest extends FormRequest
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
        $routerId = $this->route('router')?->id;

        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique(Router::class, 'name')->ignore($routerId)->whereNull('deleted_at'),
            ],
            'ip_address' => [
                'required', 'ip',
                Rule::unique(Router::class, 'ip_address')->ignore($routerId)->whereNull('deleted_at'),
            ],
            'radius_secret' => ['nullable', 'string', 'max:255'],
            'nas_identifier' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'api_port' => ['nullable', 'integer', 'between:1,65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'api_type' => ['nullable', 'string', 'in:mikrotik,freeradius'],
            'is_active' => ['sometimes', 'boolean'],
            'status' => ['nullable', 'string', Rule::in(Router::STATUSES)],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
