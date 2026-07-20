<?php

declare(strict_types=1);

namespace App\Http\Requests\Billing;

use App\Enums\SubscriptionStatus;
use App\Models\Billing\Package;
use App\Models\Billing\Subscription;
use App\Models\Billing\User as BillingUser;
use App\Models\Radius\Router;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating/updating a subscription.
 */
class SubscriptionRequest extends FormRequest
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
        $id = $this->route('subscription')?->id;

        return [
            'user_id' => ['required', 'uuid', Rule::exists(BillingUser::class, 'id')],
            'package_id' => ['required', 'uuid', Rule::exists(Package::class, 'id')],
            'router_id' => ['nullable', 'uuid', Rule::exists(Router::class, 'id')],
            'username' => [
                'nullable', 'string', 'max:255',
                Rule::unique(Subscription::class, 'username')->ignore($id)->whereNull('deleted_at'),
            ],
            'password' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in(array_column(SubscriptionStatus::cases(), 'value'))],
            'started_at' => ['nullable', 'date'],
            'expired_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
