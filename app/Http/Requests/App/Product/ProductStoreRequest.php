<?php

namespace App\Http\Requests\App\Product;

use App\Enums\TipoProdutoEnum;
use BenSampo\Enum\Rules\EnumKey;
use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
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
            // Defina as regras de validação aqui
            "nome_titulo" => ["required", "min:3", "max:255"],
            "codigo" => ["required", "string", "unique:products,codigo"],
            "preco" => ["required", "decimal:0,2"],
            "estoque" => ["required", "integer"],
            "autor" => ["string"],
            "edicao" => ["integer"],
            "tipo" => ["required", new EnumKey(TipoProdutoEnum::class)],
            "numero_nota" => ["nullable", "string"],
            "fornecedor_uuid" => ["string"],
        ];
    }
}
