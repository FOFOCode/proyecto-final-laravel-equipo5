<?php

namespace App\Http\Requests;

use App\Models\Platillo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlatilloRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'disponible' => $this->boolean('disponible'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array // [VALIDACION]
    {
        $platillo = $this->route('platillo');
        $platilloId = $platillo instanceof Platillo ? $platillo->id : $platillo;

        return [
            // [REGLA-NEGOCIO] Nombre de platillo unico ignorando el registro actual
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('platillos', 'nombre')->ignore($platilloId),
            ],
            'descripcion' => ['required', 'string', 'max:1000'],
            // [REGLA-NEGOCIO] Precio mayor que 0
            'precio' => ['required', 'numeric', 'gt:0'],
            // [REGLA-NEGOCIO] Tiempo de preparacion entre 1 y 180 minutos
            'tiempo_preparacion' => ['required', 'integer', 'between:1,180'],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'disponible' => ['boolean'],
            // [VALIDACION] Imagen valida: formatos requeridos y maximo 2 MB
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * Custom validation messages in Spanish.
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del platillo es obligatorio.',
            'nombre.unique' => 'Ya existe otro platillo registrado con este nombre en el menú.',
            'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
            'descripcion.required' => 'La descripción del platillo es obligatoria.',
            'precio.required' => 'El precio del platillo es obligatorio.',
            'precio.numeric' => 'El precio debe ser un número válido.',
            'precio.gt' => 'El precio debe ser mayor que 0.00.',
            'tiempo_preparacion.required' => 'El tiempo de preparación es obligatorio.',
            'tiempo_preparacion.integer' => 'El tiempo de preparación debe ser un número entero.',
            'tiempo_preparacion.between' => 'El tiempo de preparación debe estar entre 1 y 180 minutos.',
            'categoria_id.required' => 'Debe seleccionar una categoría para el platillo.',
            'categoria_id.exists' => 'La categoría seleccionada no es válida.',
            'imagen.image' => 'El archivo adjunto debe ser una imagen.',
            'imagen.mimes' => 'La imagen debe ser de tipo JPG, JPEG, PNG o WEBP.',
            'imagen.max' => 'El tamaño de la imagen no puede superar los 2 MB (2048 KB).',
        ];
    }
}
