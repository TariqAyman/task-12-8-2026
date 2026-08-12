<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\BillingCycle;
use App\Enums\ServiceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['sometimes', 'required', 'integer', Rule::exists('customers', 'id')->whereNull('deleted_at')],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0', 'max:99999999.99'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'billing_cycle' => ['sometimes', Rule::enum(BillingCycle::class)],
            'status' => ['sometimes', Rule::enum(ServiceStatus::class)],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => $this->filled('starts_at')
                ? ['nullable', 'date', 'after_or_equal:starts_at']
                : ['nullable', 'date'],
        ];
    }

    /**
     * Normalize the currency code before validation runs.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('currency')) {
            $this->merge([
                'currency' => strtoupper((string) $this->string('currency')),
            ]);
        }
    }
}
