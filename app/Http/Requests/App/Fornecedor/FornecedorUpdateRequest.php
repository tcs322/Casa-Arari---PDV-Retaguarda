<?php

namespace App\Http\Requests\App\Fornecedor;

use Illuminate\Foundation\Http\FormRequest;

class FornecedorUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            "uuid" => ["uuid", "exists:fornecedores,uuid"],
            "nome_fantasia" => [
                "required", "min:5", "max:254"
            ],
            "razao_social" => [
                "required", "min:5", "max:254"
            ],
            "endereco" => ["nullable", "string"],
            "cidade" => ["nullable", "string"],
            "uf" => ["nullable", "string", "size:2"],
            "numero" => ["nullable", "string"],
        ];
    }
}
