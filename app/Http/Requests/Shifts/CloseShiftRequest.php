<?php

namespace App\Http\Requests\Shifts;

use Illuminate\Foundation\Http\FormRequest;

class CloseShiftRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Permitir que cualquier usuario autenticado pueda cerrar turno
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // El conteo de efectivo final es OBLIGATORIO, debe ser un número y debe ser mayor o igual a 0
            // Usamos min:0 (no min:0.01) porque puede haber casos donde no hay efectivo
            'closing_cash_count' => 'required|numeric|min:0',
            // Las notas son opcionales
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'closing_cash_count.required' => 'Debes ingresar el conteo total de efectivo en caja.',
            'closing_cash_count.numeric' => 'El conteo de efectivo debe ser un número.',
            'closing_cash_count.min' => 'El conteo de efectivo no puede ser negativo.',
            'notes.max' => 'Las notas no pueden tener más de 500 caracteres.',
        ];
    }
}