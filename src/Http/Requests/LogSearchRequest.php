<?php

namespace KieranFYI\Logging\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LogSearchRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'page' => ['nullable', 'integer'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'user' => ['nullable', 'string'],
        ];
    }
}
