<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePedidoRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        // Las cantidades de casillas sin marcar no constituyen lineas del pedido.
        if (is_array($this->input('platillos'))) {
            $this->merge(['platillos' => array_filter(
                $this->input('platillos'),
                fn ($linea) => ! is_array($linea) || array_key_exists('platillo_id', $linea),
            )]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array // [VALIDACION]
    {
        return [
            'cliente' => ['required', 'string', 'max:100'],
            'mesa' => ['required', 'integer', 'between:1,50'],
            'notas' => ['nullable', 'string', 'max:500'],
            'platillos' => ['required', 'array', 'min:1'],
            'platillos.*' => ['array'],
            'platillos.*.platillo_id' => ['required', 'integer', 'distinct', 'exists:platillos,id'],
            'platillos.*.cantidad' => ['required', 'integer', 'between:1,20'],
        ];
    }

    public function messages(): array
    {
        return [
            'cliente.required' => 'El nombre del cliente es obligatorio.',
            'cliente.string' => 'El nombre del cliente debe ser texto.',
            'cliente.max' => 'El nombre del cliente no puede superar los 100 caracteres.',
            'mesa.required' => 'El número de mesa es obligatorio.',
            'mesa.integer' => 'El número de mesa debe ser un número entero.',
            'mesa.between' => 'El número de mesa debe estar entre 1 y 50.',
            'notas.max' => 'Las notas no pueden superar los 500 caracteres.',
            'notas.string' => 'Las notas deben ser texto.',
            'platillos.required' => 'Debe agregar al menos un platillo al pedido.',
            'platillos.array' => 'La lista de platillos no es válida.',
            'platillos.min' => 'Debe agregar al menos un platillo al pedido.',
            'platillos.*.array' => 'La línea del pedido no es válida.',
            'platillos.*.platillo_id.required' => 'Seleccione un platillo válido.',
            'platillos.*.platillo_id.integer' => 'El platillo seleccionado no es válido.',
            'platillos.*.platillo_id.distinct' => 'No repita el mismo platillo; ajuste su cantidad.',
            'platillos.*.platillo_id.exists' => 'Uno de los platillos seleccionados ya no existe.',
            'platillos.*.cantidad.required' => 'La cantidad de cada platillo es obligatoria.',
            'platillos.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'platillos.*.cantidad.between' => 'La cantidad debe estar entre 1 y 20.',
        ];
    }
}
