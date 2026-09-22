<?php

namespace Modules\Storefront\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GuestCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public — no auth required
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'address' => ['required', 'string', 'max:1000'],
        ];
    }
}
