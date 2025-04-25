<?php

namespace App\Http\Requests\Configurations;

use Illuminate\Foundation\Http\FormRequest;

class AisServerUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ais_host' => 'required|string|max:255',
            'ais_port' => 'required|integer|min:1|max:65535',
        ];
    }
}
