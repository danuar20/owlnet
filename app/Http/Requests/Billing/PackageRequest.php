<?php

declare(strict_types=1);

namespace App\Http\Requests\Billing;

use App\Models\Billing\Package;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating/updating an Internet package.
 */
class PackageRequest extends FormRequest
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
        $packageId = $this->route('package')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable', 'string', 'max:64',
                Rule::unique(Package::class, 'code')->ignore($packageId)->whereNull('deleted_at'),
            ],
            'duration_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'speed_download' => ['nullable', 'string', 'max:32'],
            'speed_upload' => ['nullable', 'string', 'max:32'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'radius_profile' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'duration_days' => 'duration (days)',
            'speed_download' => 'download speed',
            'speed_upload' => 'upload speed',
            'radius_profile' => 'radius profile',
        ];
    }
}
