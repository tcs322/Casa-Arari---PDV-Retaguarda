<?php

namespace App\Http\Requests\App\Produto;

use App\Enums\TipoProdutoEnum;
use BenSampo\Enum\Rules\EnumKey;
use Illuminate\Foundation\Http\FormRequest;

class ProdutoStoreRequest extends FormRequest
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
            "nome" => ["required", "min:3", "max:255"],
            "descricao" => ["required", "min:3", "max:255"],
            "peso" => ["required"],
            "tipo" => ["required", new EnumKey(TipoProdutoEnum::class)]
        ];
    }
}
