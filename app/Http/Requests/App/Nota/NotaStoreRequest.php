<?php

namespace App\Http\Requests\App\Nota;

use App\Enums\TipoProdutoEnum;
use BenSampo\Enum\Rules\EnumKey;
use Illuminate\Foundation\Http\FormRequest;

class NotaStoreRequest extends FormRequest
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
            "numero_nota" => [
                "required", "string"
            ],
            "valor_total"  => [
                "required", "decimal:0,2", "min:0"
            ],
            "fornecedor_uuid" => [
                "required", "string", 
            ],
            "tipo_nota" => [
                "required", new EnumKey(TipoProdutoEnum::class)
            ]
        ];
    }

    /**
     * Mensagens de erro personalizadas (opcional)
     */
    public function messages(): array
    {
        return [
            
        ];
    }
}
