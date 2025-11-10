<?php

namespace App\Http\Requests\Shifts;

use Illuminate\Foundation\Http\FormRequest;

class CloseShiftRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'closing_cash_count' => ['required','numeric','min:0'],
            'notes' => ['nullable','string','max:1000'],
        ];
    }
}
