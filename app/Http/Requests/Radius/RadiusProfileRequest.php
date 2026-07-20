<?php

declare(strict_types=1);

namespace App\Http\Requests\Radius;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for creating/updating a FreeRADIUS profile (radgroupreply set).
 */
class RadiusProfileRequest extends FormRequest
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
        return [
            'groupname' => ['required', 'string', 'max:255'],
            'attributes' => ['sometimes', 'array'],
            'attributes.*.attribute' => ['required_with:attributes', 'string', 'max:255'],
            'attributes.*.op' => ['nullable', 'string', 'max:2'],
            'attributes.*.value' => ['required_with:attributes', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $attrs = $this->input('attributes', []);
        // drop fully-empty rows submitted from the repeater
        $clean = array_values(array_filter($attrs, fn ($a) => ! empty($a['attribute']) && ! empty($a['value'])));
        $this->merge(['attributes' => $clean]);
    }
}
