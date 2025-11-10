<?php

namespace App\Http\Requests\Shifts;

use Illuminate\Foundation\Http\FormRequest;

class OpenShiftRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Permitir que cualquier usuario autenticado pueda abrir turno
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // El fondo inicial es OBLIGATORIO, debe ser un número y debe ser mayor a 0
            'opening_float' => 'required|numeric|min:0.01',
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
            'opening_float.required' => 'Debes ingresar el monto inicial de la caja.',
            'opening_float.numeric' => 'El monto inicial debe ser un número.',
            'opening_float.min' => 'El monto inicial debe ser mayor a 0.',
            'notes.max' => 'Las notas no pueden tener más de 500 caracteres.',
        ];
    }
}