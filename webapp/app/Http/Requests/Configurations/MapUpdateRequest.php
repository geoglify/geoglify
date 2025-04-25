<?php

namespace App\Http\Requests\Configurations;

use Illuminate\Foundation\Http\FormRequest;

class MapUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'default_latitude' => 'required|numeric',
            'default_longitude' => 'required|numeric',
            'default_zoom' => 'required|integer|min:0|max:22',
            'default_bearing' => 'required|integer|min:0|max:360',
            'default_style' => 'required|string',
        ];
    }
}
