<?php

namespace App\Http\Requests\Shifts;

use Illuminate\Foundation\Http\FormRequest;

class OpenShiftRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'opening_float' => ['required','numeric','min:0'],
            'notes' => ['nullable','string','max:1000'],
        ];
    }
}
