<?php

namespace App\Http\Requests\App\Product;

use App\Enums\TipoProducaoProdutoEnum;
use App\Enums\TipoProdutoEnum;
use BenSampo\Enum\Rules\EnumKey;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            // Dados básicos do produto
            "nome_titulo" => ["required", "min:3", "max:255"],
            "codigo" => ["required", "string", "unique:products,codigo"],
            "preco" => ["required", "decimal:0,2", "min:0"],
            "estoque" => ["required", "integer", "min:0"],
            "autor" => ["nullable", "string", "max:255"],
            "edicao" => ["nullable", "integer", "min:1"],
            "tipo" => ["required", new EnumKey(TipoProdutoEnum::class)],
            "nota_uuid" => ["nullable", "string", "max:36"],
            "fornecedor_uuid" => ["nullable", "string", "max:36"],

            // Campos fiscais
            "ncm" => ["nullable", "string", "size:8", "regex:/^\d{8}$/"],
            "cest" => ["nullable", "string", "size:7", "regex:/^\d{7}$/"],
            "codigo_barras" => ["nullable", "string", "max:14", "regex:/^\d+$/"],
            "unidade_medida" => ["nullable", "string", "max:6"],
            "aliquota_icms" => ["nullable", "numeric", "min:0", "max:100"],
            "cst_icms" => ["nullable", "string", "max:3"],
            "cst_pis" => ["nullable", "string", "max:2"],
            "cst_cofins" => ["nullable", "string", "max:2"],
            "cfop" => ["nullable", "string", "size:4", "regex:/^\d{4}$/"],
            "origem" => ["nullable", "string", "size:1", "in:0,1,2,3,4,5,6,7,8"],
        ];

        // ✅ REGRA CONDICIONAL: tipo_producao só é required para CAFETERIA
        if ($this->input('tipo') === 'CAFETERIA') {
            $rules["tipo_producao"] = ["required", new EnumKey(TipoProducaoProdutoEnum::class)];
        } else {
            $rules["tipo_producao"] = ["nullable", new EnumKey(TipoProducaoProdutoEnum::class)];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'ncm.regex' => 'O NCM deve conter exatamente 8 dígitos numéricos.',
            'cest.regex' => 'O CEST deve conter exatamente 7 dígitos numéricos.',
            'codigo_barras.regex' => 'O código de barras deve conter apenas números.',
            'cfop.regex' => 'O CFOP deve conter exatamente 4 dígitos numéricos.',
            'origem.in' => 'A origem deve ser um valor entre 0 e 8.',
            'tipo_producao.required' => 'O tipo de produção é obrigatório para produtos da cafeteria.',
        ];
    }

    protected function prepareForValidation()
    {
        // Remove máscaras e formatações dos campos fiscais
        if ($this->has('ncm')) {
            $this->merge(['ncm' => preg_replace('/\D/', '', $this->ncm)]);
        }

        if ($this->has('cest')) {
            $this->merge(['cest' => preg_replace('/\D/', '', $this->cest)]);
        }

        if ($this->has('codigo_barras')) {
            $this->merge(['codigo_barras' => preg_replace('/\D/', '', $this->codigo_barras)]);
        }

        if ($this->has('cfop')) {
            $this->merge(['cfop' => preg_replace('/\D/', '', $this->cfop)]);
        }

        // Garante que campos numéricos sejam tratados corretamente
        if ($this->has('preco')) {
            $this->merge(['preco' => str_replace(['R$', '.', ','], ['', '', '.'], $this->preco)]);
        }

        if ($this->has('aliquota_icms')) {
            $this->merge(['aliquota_icms' => str_replace(',', '.', $this->aliquota_icms)]);
        }
    }

    /**
     * Validações pós-regras básicas
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $tipo = $this->input('tipo');
            $tipoProducao = $this->input('tipo_producao');

            // ✅ Validação específica para produtos de cafeteria
            if ($tipo === 'CAFETERIA' && empty($tipoProducao)) {
                $validator->errors()->add(
                    'tipo_producao', 
                    'Para produtos da cafeteria, o tipo de produção é obrigatório. Selecione "Industrial" ou "Artesanal".'
                );
            }

            // ✅ Para livros, garante que tipo_producao seja null
            if ($tipo === 'LIVRARIA' && !empty($tipoProducao)) {
                $validator->errors()->add(
                    'tipo_producao', 
                    'Produtos de livraria não utilizam tipo de produção. Deixe este campo em branco.'
                );
            }
        });
    }
}