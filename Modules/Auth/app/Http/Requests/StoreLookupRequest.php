<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLookupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->canAny(['manage_settings', 'manage_users']);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $lookupId = $this->route('lookup');
        $uniqueRule = $lookupId
            ? "unique:lookups,code,{$lookupId},id,type,{$this->type}"
            : 'unique:lookups,code,NULL,id,type,'.$this->type;

        return [
            'type' => ['required', 'string', 'max:50'],
            'code' => ['required', 'string', 'max:50', $uniqueRule],
            'label' => ['required', 'string', 'max:100'],
            'sort_order' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
