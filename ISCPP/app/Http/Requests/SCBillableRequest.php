<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SCBillableRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'year' => ['integer', 'max:2100', 'min:2020'], 
            'month' => ['integer', 'max:12', 'min:1'], 
        ];
    }
}
