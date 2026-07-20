<?php

declare(strict_types=1);

namespace App\Http\Requests\Billing;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Billing\Subscription;
use App\Models\Billing\User as BillingUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating/updating an invoice.
 */
class InvoiceRequest extends FormRequest
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
            'user_id' => ['required', 'uuid', Rule::exists(BillingUser::class, 'id')],
            'subscription_id' => ['nullable', 'uuid', Rule::exists(Subscription::class, 'id')],
            'invoice_no' => ['nullable', 'string', 'max:64'],
            'method' => ['sometimes', 'string', 'max:32'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'promo_code' => ['nullable', 'string', 'max:64'],
            'promo_amount' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'invoice_status' => ['sometimes', Rule::in(array_column(InvoiceStatus::cases(), 'value'))],
            'status' => ['sometimes', Rule::in(array_column(PaymentStatus::cases(), 'value'))],
            'items' => ['sometimes', 'array'],
            'items.*.description' => ['required_with:items', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
        ];
    }

    /**
     * Normalised line items (description/quantity/unit_price) for the service.
     *
     * @return array<int, array{description:string,quantity:int,unit_price:float}>
     */
    public function items(): array
    {
        $raw = $this->input('items', []);
        $clean = [];
        foreach ($raw as $item) {
            if (empty($item['description']) || (! isset($item['unit_price']) || $item['unit_price'] === '')) {
                continue;
            }
            $clean[] = [
                'description' => $item['description'],
                'quantity' => (int) ($item['quantity'] ?? 1),
                'unit_price' => (float) $item['unit_price'],
            ];
        }

        return $clean;
    }
}
