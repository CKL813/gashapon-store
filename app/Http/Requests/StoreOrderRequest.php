<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Guest + authenticated checkout both allowed
    }

    public function rules(): array
    {
        $rules = [
            // Cart items (validated against DB inside controller)
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.product_id'      => ['required', 'integer', 'exists:products,id'],
            'items.*.variant_id'      => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.quantity'        => ['required', 'integer', 'min:1', 'max:99'],

            // Contact
            'contact.name'            => ['required', 'string', 'max:255'],
            'contact.phone'           => ['nullable', 'string', 'max:50'],

            // Shipping address
            'shipping.address_line_1' => ['required', 'string', 'max:255'],
            'shipping.address_line_2' => ['nullable', 'string', 'max:255'],
            'shipping.district'       => ['required', 'string', 'max:100'],
            'shipping.city'           => ['required', 'string', 'max:100'],

            // Billing
            'billing_same'            => ['boolean'],
            'billing.address_line_1'  => ['nullable', 'string', 'max:255'],
            'billing.district'        => ['nullable', 'string', 'max:100'],
            'billing.city'            => ['nullable', 'string', 'max:100'],

            // Optional
            'coupon_code'             => ['nullable', 'string', 'max:50'],
            'notes'                   => ['nullable', 'string', 'max:1000'],
        ];

        // Email only required for guest checkout
        if (! $this->user()) {
            $rules['contact.email'] = ['required', 'email', 'max:255'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'contact.name'            => 'full name',
            'contact.email'           => 'email address',
            'contact.phone'           => 'phone number',
            'shipping.address_line_1' => 'address',
            'shipping.district'       => 'district',
            'shipping.city'           => 'city',
        ];
    }
}
