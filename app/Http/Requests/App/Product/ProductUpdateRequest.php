<?php

namespace App\Http\Requests\App\Product;

use App\Enums\TipoProdutoEnum;
use BenSampo\Enum\Rules\EnumKey;
use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
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
            "uuid" => ["uuid", "exists:products,uuid"],
            "nome_titulo" => ["required", "min:3", "max:255"],
            "codigo" => ["required", "string", "unique:products,codigo,$this->codigo,codigo"],
            "preco_compra" => ["nullable", "decimal:0,2", "min:0"],
            "preco_venda" => ["nullable", "decimal:0,2", "min:0"],
            "estoque" => ["required", "integer"],
            "autor" => ["nullable", "string"],
            "edicao" => ["nullable", "integer"],
            "tipo" => ["required", new EnumKey(TipoProdutoEnum::class)],
            "nota_uuid" => ["nullable", "string"],
            "fornecedor_uuid" => ["string"],
        ];
    }
}
