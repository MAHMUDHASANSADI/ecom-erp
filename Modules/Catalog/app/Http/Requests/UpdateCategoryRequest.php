<?php

namespace Modules\Catalog\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_products');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $categoryId = $this->route('category');

        return [
            'name' => ['required', 'string', 'max:100'],
            'parent_category_id' => [
                'nullable',
                'exists:categories,id',
                // Prevent setting itself as its own parent
                function (string $attribute, mixed $value, \Closure $fail) use ($categoryId): void {
                    if ((int) $value === (int) $categoryId) {
                        $fail('A category cannot be its own parent.');
                    }
                },
            ],
            'is_active' => ['boolean'],
        ];
    }
}
