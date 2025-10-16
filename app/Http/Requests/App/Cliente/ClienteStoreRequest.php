<?php

namespace App\Http\Requests\App\Cliente;

use Illuminate\Foundation\Http\FormRequest;

class ClienteStoreRequest extends FormRequest
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
            "nome" => [
                "required", "string", "min:2", "max:254"
            ],
            "cpf" => [
                "required",
                "string",
                "size:11",
                "regex:/^\d+$/",
                function ($attribute, $value, $fail) {
                    if (!$this->validarCPF($value)) {
                        $fail("O CPF informado é inválido.");
                    }
                },
            ],
            "data_nascimento"  => [
                "required", "date"
            ]
        ];
    }

    private function validarCPF($cpf): bool
    {
        // Remove caracteres não numéricos
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        // Verifica se tem 11 dígitos
        if (strlen($cpf) != 11) {
            return false;
        }
        
        // Verifica se não é uma sequência de dígitos repetidos
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        
        // Cálculo para validar o primeiro dígito verificador
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Mensagens de erro personalizadas (opcional)
     */
    public function messages(): array
    {
        return [
            'cpf.required' => 'O campo CPF é obrigatório.',
            'cpf.string' => 'O CPF deve ser uma string.',
            'cpf.size' => 'O CPF deve ter exatamente 11 dígitos.',
            'cpf.regex' => 'O CPF deve conter apenas números.',
        ];
    }
}
